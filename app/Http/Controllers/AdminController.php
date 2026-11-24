<?php

namespace App\Http\Controllers;

use App\Actions\CancelOrder;
use App\Actions\CompleteOrderFulfillment;
use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use App\Services\CarbonImpactService;
use App\Services\EmailService;
use App\Support\PurchaseWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'pendingPayments' => Payment::where('gateway', Payment::GATEWAY_MANUAL)->where('status', 'pending')->count(),
            'buyersCount' => User::where('role', 'buyer')->count(),
            'productsSold' => OrderItem::whereHas('order', fn ($query) => $query->whereIn('status', ['paid', 'partially_picked_up', 'picked_up']))->sum('quantity'),
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

    public function carbonEmissions(): View
    {
        return view('admin.carbon-emissions', [
            'carbonSummary' => app(CarbonImpactService::class)->adminDashboardSummary(),
            'carbonProducts' => Product::query()
                ->with(['seller.project', 'project'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateCarbonFactor(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'carbon_reduction_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.999999'],
        ]);

        $product->update([
            'carbon_reduction_value' => $data['carbon_reduction_value'] ?? null,
            'carbon_footprint_value' => $data['carbon_reduction_value'] !== null ? 0 : null,
            'carbon_baseline_emission_factor' => $data['carbon_reduction_value'] !== null ? $data['carbon_reduction_value'] : null,
            'carbon_footprint_unit' => $data['carbon_reduction_value'] !== null ? CarbonImpactService::UNIT : null,
            'carbon_baseline_source' => $data['carbon_reduction_value'] !== null ? 'Nilai pengurangan karbon langsung per produk' : null,
            'carbon_product_source' => $data['carbon_reduction_value'] !== null ? 'Nilai pengurangan karbon langsung per produk' : null,
            'carbon_functional_unit' => $data['carbon_reduction_value'] !== null ? '1 produk' : null,
        ]);

        return back()->with('status', 'Faktor emisi produk berhasil diperbarui untuk transaksi berikutnya.');
    }

    public function payments(): View
    {
        $payments = Payment::with(['order.buyer', 'order.items.seller.project', 'verifier'])->latest()->paginate(20);
        $qrisImage = config('marketplace.qris_image');
        $qrisExists = Storage::disk('public')->exists('qris-statis.png')
            || (filled($qrisImage) && is_file(public_path($qrisImage)));

        return view('admin.payments', compact('payments', 'qrisImage', 'qrisExists'));
    }

    public function updateQris(Request $request): RedirectResponse
    {
        $request->validate([
            'qris_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ], [
            'qris_image.required' => 'Pilih file gambar QRIS.',
            'qris_image.image' => 'File harus berupa gambar.',
            'qris_image.mimes' => 'Format file yang didukung: PNG, JPG, JPEG.',
            'qris_image.max' => 'Ukuran file gambar maksimal 5 MB.',
        ]);

        $file = $request->file('qris_image');

        // Simpan ke storage disk public (aman untuk shared hosting/cPanel)
        Storage::disk('public')->put('qris-statis.png', file_get_contents($file->getRealPath()));

        // Sinkronkan ke public/images jika folder dapat ditulis
        try {
            $destinationPath = public_path('images');
            if (! file_exists($destinationPath)) {
                @mkdir($destinationPath, 0755, true);
            }
            if (is_dir($destinationPath) && is_writable($destinationPath)) {
                @copy($file->getRealPath(), $destinationPath.DIRECTORY_SEPARATOR.'qris-statis.png');
            }
        } catch (\Throwable) {
            // Abaikan jika public/ tidak memiliki permission tulis di hosting
        }

        return back()->with('status', 'Foto QRIS pembayaran berhasil diperbarui.');
    }

    public function paymentProof(Payment $payment)
    {
        abort_unless($payment->gateway === Payment::GATEWAY_MANUAL && filled($payment->proof_image), 404);
        abort_unless(Storage::disk('local')->exists($payment->proof_image), 404);

        return Storage::disk('local')->response($payment->proof_image);
    }

    public function verify(Request $request, Payment $payment, CarbonImpactService $carbonImpact, EmailService $emailService): RedirectResponse
    {
        $data = $request->validate([
            'received_amount' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        $alreadyHandled = false;
        DB::transaction(function () use ($payment, $carbonImpact, $data, &$alreadyHandled) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            if (
                $lockedPayment->gateway !== Payment::GATEWAY_MANUAL
                || $lockedPayment->status !== 'pending'
                || $order->status !== 'waiting_verification'
            ) {
                $alreadyHandled = true;
                return;
            }

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

        if ($alreadyHandled) {
            return back()->with('error', 'Pembayaran ini sudah diproses atau bukan pembayaran manual.');
        }

        try {
            $order = $payment->order()->first();
            $order?->loadMissing('buyer');
            if ($order?->buyer?->email) {
                $emailService->send(
                    new PaymentVerifiedMail($order),
                    $order->buyer->email
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment verified email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Pembayaran diverifikasi dan ticket pickup diterbitkan.');
    }

    public function reject(Request $request, Payment $payment, EmailService $emailService, PurchaseWindow $purchaseWindow): RedirectResponse
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:255']]);
        $alreadyHandled = false;
        DB::transaction(function () use ($payment, $data, $purchaseWindow, &$alreadyHandled) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            if (
                $lockedPayment->gateway !== Payment::GATEWAY_MANUAL
                || $lockedPayment->status !== 'pending'
                || $order->status !== 'waiting_verification'
            ) {
                $alreadyHandled = true;
                return;
            }

            $lockedPayment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $data['notes'],
            ]);

            $graceMinutes = (int) config('marketplace.payment_rejection_grace_minutes', config('marketplace.payment_timeout_minutes', 10));
            $targetExpiry = now()->addMinutes($graceMinutes);
            $globalDeadline = $purchaseWindow->paymentDeadline();
            $newExpiresAt = ($globalDeadline && $globalDeadline->isFuture())
                ? $targetExpiry->min($globalDeadline)
                : $targetExpiry;

            $order->update([
                'status' => 'pending_payment',
                'expires_at' => $newExpiresAt,
            ]);
        });

        if ($alreadyHandled) {
            return back()->with('error', 'Pembayaran ini sudah diproses atau bukan pembayaran manual.');
        }

        try {
            $order = $payment->order()->first();
            $order?->loadMissing('buyer');
            if ($order?->buyer?->email) {
                $emailService->send(
                    new PaymentRejectedMail($order, $data['notes']),
                    $order->buyer->email
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment rejected email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Pembayaran ditolak. Pembeli dapat mengunggah bukti pembayaran baru.');
    }

    public function cancelOrder(Order $order, CancelOrder $cancelOrder): RedirectResponse
    {
        $cancelled = $cancelOrder->handle($order, 'Dibatalkan manual oleh admin');

        if (! $cancelled) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan karena statusnya sudah berubah.');
        }

        return back()->with('status', 'Pesanan #'.$order->order_code.' berhasil dibatalkan dan stok dikembalikan.');
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
        $preset = $request->query('preset');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Backwards compatibility for single date param
        if (! $preset && $request->filled('date')) {
            $startDate = $request->query('date');
            $endDate = $request->query('date');
            $preset = 'custom';
        }

        if (! $preset) {
            if ($startDate || $endDate) {
                $preset = 'custom';
            } else {
                $preset = 'event'; // default preset
            }
        }

        if ($preset === 'today') {
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        } elseif ($preset === 'event') {
            $startDate = '2026-11-23';
            $endDate = '2026-11-27';
        } elseif ($preset === 'all') {
            $startDate = null;
            $endDate = null;
        } elseif ($preset === 'custom') {
            // Keep $startDate and $endDate as passed
        }

        $validStatuses = ['paid', 'partially_picked_up', 'picked_up'];

        $applyDateFilter = function ($query, string $column = 'created_at') use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate($column, $startDate);
                } else {
                    $query->whereDate($column, '>=', $startDate)->whereDate($column, '<=', $endDate);
                }
            } elseif ($startDate) {
                $query->whereDate($column, '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate($column, '<=', $endDate);
            }
        };

        // Orders query in period
        $ordersQuery = Order::query();
        $applyDateFilter($ordersQuery, 'created_at');

        $totalOrders = (clone $ordersQuery)->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        $paidOrdersQuery = (clone $ordersQuery)->whereIn('status', $validStatuses);
        $paidOrders = $paidOrdersQuery->count();
        $revenue = $paidOrdersQuery->sum('total_price');

        // Products sold in period
        $productsSold = OrderItem::query()->whereHas('order', function ($q) use ($validStatuses, $applyDateFilter) {
            $q->whereIn('status', $validStatuses);
            $applyDateFilter($q, 'created_at');
        })->sum('quantity');

        // Units picked up (delivered) in period
        $deliveredUnits = OrderItemUnit::query()->whereHas('orderItem.order', function ($q) use ($validStatuses, $applyDateFilter) {
            $q->whereIn('status', $validStatuses);
            $applyDateFilter($q, 'created_at');
        })->where('status', 'delivered')->count();

        $totalUnits = OrderItemUnit::query()->whereHas('orderItem.order', function ($q) use ($validStatuses, $applyDateFilter) {
            $q->whereIn('status', $validStatuses);
            $applyDateFilter($q, 'created_at');
        })->count();

        if ($totalUnits === 0 && $productsSold > 0) {
            $totalUnits = $productsSold;
        }

        // All projects and class breakdown
        $projects = Project::query()
            ->with([
                'seller.user',
                'products' => function ($q) use ($validStatuses) {
                    $q->with([
                        'category',
                        'orderItems' => function ($itemQ) use ($validStatuses) {
                            $itemQ->whereHas('order', fn ($oq) => $oq->whereIn('status', $validStatuses))
                                ->with(['order', 'units']);
                        },
                    ]);
                },
            ])
            ->get();

        $classesData = $projects->map(function (Project $project) use ($startDate, $endDate) {
            $seller = $project->seller;
            $allProducts = $project->products;

            $periodSoldUnits = 0;
            $periodRevenue = 0;
            $allTimeSoldUnits = 0;
            $allTimeRevenue = 0;
            $classDeliveredUnits = 0;
            $classPendingUnits = 0;

            foreach ($allProducts as $product) {
                foreach ($product->orderItems as $item) {
                    $order = $item->order;
                    if (! $order) {
                        continue;
                    }

                    // All time stats
                    $allTimeSoldUnits += $item->quantity;
                    $allTimeRevenue += $item->subtotal;

                    // Period filter
                    $createdAt = $order->created_at?->toDateString();
                    $inPeriod = true;
                    if ($startDate && $endDate) {
                        $inPeriod = ($createdAt >= $startDate && $createdAt <= $endDate);
                    } elseif ($startDate) {
                        $inPeriod = ($createdAt >= $startDate);
                    } elseif ($endDate) {
                        $inPeriod = ($createdAt <= $endDate);
                    }

                    if ($inPeriod) {
                        $periodSoldUnits += $item->quantity;
                        $periodRevenue += $item->subtotal;
                    }

                    // Units status
                    foreach ($item->units as $unit) {
                        if ($unit->status === 'delivered') {
                            $classDeliveredUnits++;
                        } else {
                            $classPendingUnits++;
                        }
                    }
                }
            }

            return [
                'project' => $project,
                'seller' => $seller,
                'products_count' => $allProducts->count(),
                'period_sold_units' => $periodSoldUnits,
                'period_revenue' => $periodRevenue,
                'all_time_sold_units' => $allTimeSoldUnits,
                'all_time_revenue' => $allTimeRevenue,
                'delivered_units' => $classDeliveredUnits,
                'pending_units' => $classPendingUnits,
            ];
        });

        // Search & Filter
        $search = trim((string) $request->query('search', ''));
        $selectedGrade = $request->query('grade');
        $sort = $request->query('sort', 'revenue_desc');

        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $classesData = $classesData->filter(function ($item) use ($searchLower) {
                $className = mb_strtolower($item['project']->class_name ?? '');
                $title = mb_strtolower($item['project']->title ?? '');
                $storeName = mb_strtolower($item['seller']?->store_name ?? '');
                $userName = mb_strtolower($item['seller']?->user?->name ?? '');
                $userUsername = mb_strtolower($item['seller']?->user?->username ?? '');
                $projectId = (string) ($item['project']->id ?? '');
                $projectGrade = (string) ($item['project']->grade ?? '');

                return str_contains($className, $searchLower)
                    || str_contains($title, $searchLower)
                    || str_contains($storeName, $searchLower)
                    || str_contains($userName, $searchLower)
                    || str_contains($userUsername, $searchLower)
                    || str_contains('@'.$userUsername, $searchLower)
                    || str_contains($projectId, $searchLower)
                    || str_contains($projectGrade, $searchLower);
            });
        }

        if ($selectedGrade !== null && $selectedGrade !== '') {
            $classesData = $classesData->filter(function ($item) use ($selectedGrade) {
                return (string) $item['project']->grade === (string) $selectedGrade;
            });
        }

        // Sorting
        $classesData = match ($sort) {
            'revenue_asc' => $classesData->sortBy('period_revenue'),
            'qty_desc' => $classesData->sortByDesc('period_sold_units'),
            'all_time_desc' => $classesData->sortByDesc('all_time_revenue'),
            'name_asc' => $classesData->sortBy(fn ($item) => [$item['project']->grade, $item['project']->class_name]),
            default => $classesData->sortByDesc('period_revenue'),
        };

        $availableGrades = Project::query()->distinct()->orderBy('grade')->pluck('grade');

        return view('admin.reports', [
            'preset' => $preset,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'selectedGrade' => $selectedGrade,
            'sort' => $sort,
            'availableGrades' => $availableGrades,
            'totalOrders' => $totalOrders,
            'cancelledOrders' => $cancelledOrders,
            'paidOrders' => $paidOrders,
            'revenue' => $revenue,
            'productsSold' => $productsSold,
            'deliveredUnits' => $deliveredUnits,
            'totalUnits' => $totalUnits,
            'classesData' => $classesData,
        ]);
    }

    public function classReport(Request $request, Project $project): View
    {
        $seller = $project->seller;
        $validStatuses = ['paid', 'partially_picked_up', 'picked_up'];

        $products = $project->products()
            ->with(['category', 'orderItems.order', 'orderItems.units'])
            ->get();

        $productsWithStats = $products->map(function (Product $product) use ($validStatuses) {
            $validItems = $product->orderItems->filter(function (OrderItem $item) use ($validStatuses) {
                return in_array($item->order?->status, $validStatuses, true);
            });

            $soldUnits = $validItems->sum('quantity');
            $revenue = $validItems->sum('subtotal');

            $deliveredUnits = 0;
            $pendingUnits = 0;
            foreach ($validItems as $item) {
                $deliveredUnits += $item->units->where('status', 'delivered')->count();
                $pendingUnits += $item->units->where('status', 'pending')->count();
            }

            return [
                'product' => $product,
                'stock' => $product->stock,
                'sold_units' => $soldUnits,
                'delivered_units' => $deliveredUnits,
                'pending_units' => $pendingUnits,
                'revenue' => $revenue,
            ];
        });

        $totalRevenue = $productsWithStats->sum('revenue');
        $totalSold = $productsWithStats->sum('sold_units');
        $totalDelivered = $productsWithStats->sum('delivered_units');
        $totalPending = $productsWithStats->sum('pending_units');

        return view('admin.class-report', [
            'project' => $project,
            'seller' => $seller,
            'productsWithStats' => $productsWithStats,
            'totalRevenue' => $totalRevenue,
            'totalSold' => $totalSold,
            'totalDelivered' => $totalDelivered,
            'totalPending' => $totalPending,
        ]);
    }
}
