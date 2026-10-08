<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'pendingPayments' => Payment::where('status', 'pending')->count(),
            'buyersCount' => User::where('role', 'buyer')->count(),
            'productsSold' => OrderItem::whereHas('order', fn ($query) => $query->whereIn('status', ['paid', 'picked_up']))->sum('quantity'),
            'payments' => Payment::with('order.buyer')->where('status', 'pending')->latest()->limit(6)->get(),
        ]);
    }

    public function payments(): View
    {
        $payments = Payment::with('order.buyer')->latest()->paginate(20);

        return view('admin.payments', compact('payments'));
    }

    public function paymentProof(Payment $payment)
    {
        abort_unless(Storage::disk('local')->exists($payment->proof_image), 404);

        return Storage::disk('local')->download($payment->proof_image);
    }

    public function verify(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedPayment->status === 'pending' && $order->status === 'waiting_verification', 422, 'Pembayaran ini sudah diproses.');

            $lockedPayment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
            $order->update(['status' => 'paid']);
            $order->ticket()->create([
                'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                'issued_at' => now(),
            ]);
        });

        return back()->with('status', 'Pembayaran diverifikasi dan ticket pickup diterbitkan.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($payment, $data) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->with('items')->lockForUpdate()->firstOrFail();
            abort_unless($lockedPayment->status === 'pending' && $order->status === 'waiting_verification', 422, 'Pembayaran ini sudah diproses.');

            $lockedPayment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $data['notes'],
            ]);
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
        });

        return back()->with('status', 'Pembayaran ditolak dan stok dikembalikan.');
    }

    public function users(): View
    {
        $users = User::whereIn('role', ['buyer', 'seller'])->with('seller')->latest()->paginate(25);

        return view('admin.users', compact('users'));
    }

    public function toggleUser(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['buyer', 'seller'], true), 422);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'Status akun diperbarui.');
    }

    public function pickupForm(): View
    {
        return view('admin.pickup');
    }

    public function pickup(Request $request): RedirectResponse
    {
        $data = $request->validate(['ticket_code' => ['required', 'string', 'max:32']]);
        $order = DB::transaction(function () use ($request, $data) {
            $ticket = Ticket::where('ticket_code', Str::upper(trim($data['ticket_code'])))->lockForUpdate()->first();
            if (! $ticket || $ticket->picked_up_at) {
                throw ValidationException::withMessages(['ticket_code' => 'Ticket tidak valid atau sudah pernah dipakai.']);
            }
            $order = Order::whereKey($ticket->order_id)->lockForUpdate()->firstOrFail();
            if ($order->status !== 'paid') {
                throw ValidationException::withMessages(['ticket_code' => 'Pesanan ini belum lunas atau sudah selesai diambil.']);
            }
            $ticket->update(['picked_up_at' => now(), 'picked_up_by' => $request->user()->id]);
            $order->update(['status' => 'picked_up']);

            return $order;
        });

        return back()->with('pickup_order', $order->load('buyer'))->with('status', 'Ticket valid. Pesanan berhasil ditandai sudah diambil.');
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
            'paidOrders' => (clone $orders)->whereIn('status', ['paid', 'picked_up'])->count(),
            'revenue' => (clone $orders)->whereIn('status', ['paid', 'picked_up'])->sum('total_price'),
            'productsSold' => OrderItem::whereHas('order', fn ($query) => $query->whereDate('created_at', $date)->whereIn('status', ['paid', 'picked_up']))->sum('quantity'),
            'pickedUpOrders' => (clone $orders)->where('status', 'picked_up')->count(),
        ]);
    }
}
