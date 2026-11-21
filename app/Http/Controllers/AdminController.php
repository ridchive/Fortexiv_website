<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOrderFulfillment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use App\Services\CarbonImpactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $carbonSummary = app(CarbonImpactService::class)->adminDashboardSummary();

        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'pendingPayments' => Payment::where('gateway', Payment::GATEWAY_MANUAL)->where('status', 'pending')->count(),
            'buyersCount' => User::where('role', 'buyer')->count(),
            'productsSold' => OrderItem::whereHas('order', fn ($query) => $query->whereIn('status', ['paid', 'partially_picked_up', 'picked_up']))->sum('quantity'),
            'carbonSummary' => $carbonSummary,
            'payments' => Payment::with('order.buyer')
                ->where('gateway', Payment::GATEWAY_MANUAL)
                ->where('status', 'pending')
                ->latest()
                ->limit(6)
                ->get(),
            'orders' => Order::query()->with(['buyer', 'payment', 'items.seller.project'])->latest()->limit(12)->get(),
            'classes' => Project::query()->with(['seller.user', 'products'])->withCount('products')->orderBy('grade')->orderBy('class_name')->get(),
        ]);
    }

    public function payments(): View
    {
        $payments = Payment::with(['order.buyer', 'order.items.fulfillment'])->latest()->paginate(20);

        return view('admin.payments', compact('payments'));
    }

    public function paymentProof(Payment $payment)
    {
        abort_unless($payment->gateway === Payment::GATEWAY_MANUAL && filled($payment->proof_image), 404);
        abort_unless(Storage::disk('local')->exists($payment->proof_image), 404);

        return Storage::disk('local')->download($payment->proof_image);
    }

    public function verify(Request $request, Payment $payment, CarbonImpactService $carbonImpact): RedirectResponse
    {
        $data = $request->validate([
            'received_amount' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        DB::transaction(function () use ($payment, $carbonImpact, $data) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            abort_unless(
                $lockedPayment->gateway === Payment::GATEWAY_MANUAL
                    && $lockedPayment->status === 'pending'
                    && $order->status === 'waiting_verification',
                422,
                'Pembayaran ini sudah diproses atau bukan pembayaran manual.',
            );

            $lockedPayment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'received_amount' => $data['received_amount'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $order->update(['status' => 'paid']);
            $order->ticket()->create([
                'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                'issued_at' => now(),
            ]);
            $carbonImpact->recordEligiblePurchase($order);
        });

        return back()->with('status', 'Pembayaran diverifikasi dan ticket pickup diterbitkan.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($payment, $data) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            abort_unless(
                $lockedPayment->gateway === Payment::GATEWAY_MANUAL
                    && $lockedPayment->status === 'pending'
                    && $order->status === 'waiting_verification',
                422,
                'Pembayaran ini sudah diproses atau bukan pembayaran manual.',
            );

            $lockedPayment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $data['notes'],
            ]);
            $order->update(['status' => 'pending_payment']);
        });

        return back()->with('status', 'Pembayaran ditolak. Pembeli dapat mengunggah bukti pembayaran baru.');
    }

    public function adjustCarbonImpact(
        Request $request,
        OrderItem $orderItem,
        CarbonImpactService $carbonImpact,
    ): RedirectResponse {
        $data = $request->validate([
            'corrected_contribution' => ['nullable', 'numeric', 'between:-999999999999.999999,999999999999.999999'],
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $orderItem->loadMissing(['order.payment']);
        abort_unless(
            $orderItem->order->payment?->status === 'verified',
            422,
            'Kontribusi hanya dapat dikoreksi untuk pesanan dengan pembayaran terverifikasi.',
        );
        abort_unless(
            $orderItem->carbon_eligible_at !== null && $orderItem->carbon_contribution !== null,
            422,
            'Item belum memiliki perhitungan emisi dihindari yang lengkap.',
        );

        $carbonImpact->adjustOrderItem(
            $orderItem,
            isset($data['corrected_contribution']) ? (float) $data['corrected_contribution'] : null,
            $data['reason'],
            $request->user(),
        );

        return back()->with('status', 'Kontribusi jejak karbon item diperbarui dan koreksi dicatat.');
    }

    public function users(Request $request): View
    {
        $role = $request->query('role');
        $query = User::whereIn('role', ['buyer', 'seller'])->with(['seller.project'])->withCount('orders');

        if ($role === 'seller') {
            // Hanya menampilkan akun seller yang terhubung aktif ke kelas di Kelola Kelas
            $query->where('role', 'seller')->whereHas('seller.project');
        } elseif ($role === 'buyer') {
            $query->where('role', 'buyer');
        }

        $users = $query->latest()->paginate(25)->withQueryString();
        $buyersCount = User::where('role', 'buyer')->count();
        $sellersCount = User::where('role', 'seller')->whereHas('seller.project')->count();
        $totalCount = $buyersCount + $sellersCount;

        return view('admin.users', compact('users', 'role', 'buyersCount', 'sellersCount', 'totalCount'));
    }

    public function toggleUser(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['buyer', 'seller'], true), 422);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'Status akun diperbarui.');
    }

    public function pickupForm(): View
    {
        return view('admin.pickup', [
            'sellers' => Seller::query()->with('project')->orderBy('store_name')->get(['id', 'store_name', 'project_id']),
        ]);
    }

    public function pickup(Request $request, CompleteOrderFulfillment $completeOrderFulfillment): RedirectResponse
    {
        $data = $request->validate([
            'pickup_code' => ['required', 'string', 'max:32'],
            'seller_id' => ['required', 'integer', 'exists:sellers,id'],
        ]);
        $seller = Seller::query()->findOrFail($data['seller_id']);
        $fulfillment = $completeOrderFulfillment->handle($data['pickup_code'], $seller, $request->user());

        return back()
            ->with('pickup_group', [
                'order_code' => $fulfillment->order->order_code,
                'buyer_name' => $fulfillment->order->buyer->name,
                'seller_name' => $fulfillment->seller->store_name,
            ])
            ->with('status', 'Grup kelas berhasil ditandai sudah diserahkan.');
    }

    public function report(Request $request): View
    {
        $data = $request->validate(['date' => ['nullable', 'date']]);
        $date = $data['date'] ?? now()->toDateString();
        $orders = Order::whereDate('created_at', $date);

        return view('admin.reports', [
            'date' => $date,
            'totalOrders' => (clone $orders)->count(),
            'cancelledOrders' => (clone $orders)->where('status', 'cancelled')->count(),
            'paidOrders' => (clone $orders)->whereIn('status', ['paid', 'partially_picked_up', 'picked_up'])->count(),
            'revenue' => (clone $orders)->whereIn('status', ['paid', 'partially_picked_up', 'picked_up'])->sum('total_price'),
            'productsSold' => OrderItem::whereHas('order', fn ($query) => $query->whereDate('created_at', $date)->whereIn('status', ['paid', 'partially_picked_up', 'picked_up']))->sum('quantity'),
            'pickedUpOrders' => (clone $orders)->where('status', 'picked_up')->count(),
        ]);
    }
}
