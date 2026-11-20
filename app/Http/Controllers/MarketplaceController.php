<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Services\CarbonImpactService;
use App\Services\MidtransService;
use App\Support\PurchaseWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use UnexpectedValueException;

class MarketplaceController extends Controller
{
    public function home(PurchaseWindow $purchaseWindow): View
    {
        $projects = Project::query()
            ->where('is_published', true)
            ->withCount(['products as published_products_count' => fn (Builder $query) => $query->where('status', 'published')->where('review_status', 'approved')])
            ->orderBy('id')
            ->get();

        $totalPublishedProducts = Product::query()
            ->where('status', 'published')
            ->where('review_status', 'approved')
            ->count();

        $eventSettings = EventSetting::current();
        $carbonImpact = app(CarbonImpactService::class);
        $carbonSummary = $carbonImpact->marketplaceMetrics();
        $impactSummary = $carbonImpact->impactSummary();
        $co2Reduced = $impactSummary['has_verified_data']
            ? number_format($carbonSummary['total_avoided_emissions'], 2, ',', '.')
            : 'Belum tersedia';

        $impactStats = [
            'co2_reduced' => $co2Reduced,
            'classes_count' => $projects->count(),
            'products_count' => $totalPublishedProducts,
        ];

        return view('marketplace.home', [
            'projects' => $projects,
            'products' => $this->availableProducts()->where('stock', '>', 0)->limit(8)->get(),
            'window' => $purchaseWindow->details(),
            'impactStats' => $impactStats,
            'eventSettings' => $eventSettings,
        ]);
    }

    public function impact(): View
    {
        $eventSettings = EventSetting::current();
        if (! $eventSettings) {
            $eventSettings = new EventSetting([
                'impact_title' => 'Jejak Dampak Nyata Green Ecosystem: Konsumsi Bijak, Bumi Lestari',
                'impact_intro' => 'Dokumentasi transparansi pengurangan jejak emisi karbon melalui proyek nyata dari kebun, dapur, dan studio karya sekolah.',
                'impact_baseline_co2' => 20.00,
            ]);
        }

        $products = Product::query()
            ->with(['seller', 'project'])
            ->orderBy('id')
            ->get();

        $carbonSummary = app(CarbonImpactService::class)->publicImpactSummary();
        foreach ($products as $product) {
            $productContribution = $carbonSummary['products']->get($product->id);
            $product->eligible_sold_units = $productContribution?->eligible_sold_units ?? 0;
            $product->eligible_carbon_contribution = $productContribution;
        }

        $impactSummary = app(CarbonImpactService::class)->impactSummary();
        $co2Reduced = $impactSummary['has_verified_data']
            ? number_format($carbonSummary['total_avoided_emissions'], 2, ',', '.')
            : 'Belum tersedia';

        return view('marketplace.impact', [
            'eventSettings' => $eventSettings,
            'products' => $products,
            'carbonSummary' => $carbonSummary,
            'totalCo2' => $carbonSummary['net_avoided_emissions'],
            'co2Reduced' => $co2Reduced,
            'impactSummary' => $impactSummary,
            'co2Base' => 0.0,
            'unlinkedCarbonProducts' => $carbonSummary['unlinked_products'],
        ]);
    }

    public function projects(Request $request): View
    {
        $projects = Project::query()
            ->where('is_published', true)
            ->when($request->integer('class'), fn (Builder $query, int $classId) => $query->whereKey($classId))
            ->orderBy('class_name')
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.projects', [
            'projects' => $projects,
            'classes' => Project::query()->where('is_published', true)->orderBy('class_name')->get(['id', 'class_name']),
        ]);
    }

    public function catalog(Request $request, PurchaseWindow $purchaseWindow): View
    {
        $products = $this->availableProducts()
            ->when($request->string('search')->trim()->toString(), function (Builder $query, string $search): void {
                $query->where(function (Builder $productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery
                            ->where('class_name', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%"))
                        ->orWhereHas('seller', fn (Builder $sellerQuery) => $sellerQuery->where('store_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->integer('class'), fn (Builder $query, int $classId) => $query->where('project_id', $classId))
            ->when($request->integer('category'), fn (Builder $query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->query('stock') === 'available', fn (Builder $query) => $query->where('stock', '>', 0))
            ->when($request->query('stock') === 'sold_out', fn (Builder $query) => $query->where('stock', 0))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.catalog', [
            'products' => $products,
            'classes' => Project::query()
                ->where('is_published', true)
                ->whereIn('class_name', [
                    'kelas 1',
                    'kelas 2',
                    'kelas 3',
                    'kelas 4',
                    'kelas 5',
                    'kelas 7',
                    'kelas 8',
                    'ISTC basic & Intermediate',
                    'ISTC Advance & Master',
                ])
                ->orderBy('id')
                ->get(['id', 'class_name']),
            'categories' => Category::query()->orderBy('name')->get(),
            'window' => $purchaseWindow->details(),
        ]);
    }

    public function product(Product $product, PurchaseWindow $purchaseWindow): View
    {
        abort_unless($product->status === 'published' && $product->review_status === 'approved', 404);
        $product->load(['seller.project', 'category', 'project']);

        return view('marketplace.product', [
            'product' => $product,
            'window' => $purchaseWindow->details(),
        ]);
    }

    public function project(Project $project, PurchaseWindow $purchaseWindow): View
    {
        abort_unless($project->is_published, 404);
        $project->load([
            'products' => fn (HasMany $query) => $query
                ->where('status', 'published')
                ->where('review_status', 'approved')
                ->with(['seller', 'category']),
        ]);

        return view('marketplace.project', [
            'project' => $project,
            'window' => $purchaseWindow->details(),
        ]);
    }

    public function help(Request $request): View
    {
        return view('marketplace.help', [
            'activeSection' => in_array($request->query('section'), ['cara-memesan', 'pengambilan-barang'], true)
                ? $request->query('section')
                : 'cara-memesan',
        ]);
    }

    public function terms(): View
    {
        return view('marketplace.terms', ['termsVersion' => config('marketplace.terms_version')]);
    }

    public function account(Request $request): View
    {
        $user = $request->user()->load('seller.project');

        return view('marketplace.account', compact('user'));
    }

    public function cart(Request $request, PurchaseWindow $purchaseWindow): View
    {
        $items = $request->user()->cartItems()->with('product.seller.project', 'product.project', 'product.category')->get();
        $groups = $items->groupBy(fn (CartItem $item): string => (string) ($item->product->project_id ?? $item->product->seller->project_id ?? 'seller-'.$item->product->seller_id));

        return view('marketplace.cart', [
            'items' => $items,
            'groups' => $groups,
            'window' => $purchaseWindow->details(),
            'checkoutAvailable' => $purchaseWindow->canAcceptOrders(),
        ]);
    }

    public function addToCart(Request $request, PurchaseWindow $purchaseWindow): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        abort_unless($purchaseWindow->canAcceptOrders(), 422, 'PO belum dibuka atau sudah ditutup. Produk masih dapat dilihat di katalog.');

        $product = Product::query()->whereKey($data['product_id'])->where('status', 'published')->where('review_status', 'approved')->firstOrFail();
        $item = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
        $quantity = ($item->exists ? $item->quantity : 0) + $data['quantity'];
        if ($quantity > $product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
        }

        $item->quantity = $quantity;
        $item->save();

        return redirect()->route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function updateCart(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:100']]);
        abort_if($cartItem->product->status !== 'published' || $cartItem->product->review_status !== 'approved' || $data['quantity'] > $cartItem->product->stock, 422, 'Produk tidak tersedia sebanyak jumlah yang diminta. Hapus produk atau kurangi jumlahnya.');
        $cartItem->update(['quantity' => $data['quantity']]);

        return back()->with('status', 'Jumlah produk diperbarui.');
    }

    public function removeCart(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $cartItem->delete();

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function checkoutShow(Request $request, PurchaseWindow $purchaseWindow): View|RedirectResponse
    {
        $mode = $request->query('mode') === 'now' ? 'now' : 'cart';

        if ($mode === 'now') {
            $data = $request->validate([
                'product' => ['required', 'integer', 'exists:products,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            ]);
            $product = Product::query()->whereKey($data['product'])->where('status', 'published')->where('review_status', 'approved')->with(['seller.project', 'project', 'category'])->firstOrFail();
            $items = collect([[
                'product' => $product,
                'quantity' => (int) $data['quantity'],
                'subtotal' => $product->price * (int) $data['quantity'],
            ]]);
        } else {
            $items = $request->user()->cartItems()
                ->with('product.seller.project', 'product.project', 'product.category')
                ->get()
                ->map(fn (CartItem $item): array => [
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);
        }

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang masih kosong. Pilih produk sebelum melanjutkan.']);
        }

        $groups = $items->groupBy(fn (array $item): string => (string) ($item['product']->project_id ?? $item['product']->seller->project_id ?? 'seller-'.$item['product']->seller_id));

        return view('marketplace.checkout', [
            'items' => $items,
            'groups' => $groups,
            'mode' => $mode,
            'total' => $items->sum('subtotal'),
            'window' => $purchaseWindow->details(),
            'checkoutAvailable' => $purchaseWindow->canAcceptOrders(),
            'termsVersion' => config('marketplace.terms_version'),
        ]);
    }

    public function checkout(Request $request, PurchaseWindow $purchaseWindow, MidtransService $midtrans): RedirectResponse
    {
        abort_unless($purchaseWindow->canAcceptOrders(), 422, 'PO belum dibuka atau sudah ditutup. Pesanan baru tidak dapat dibuat.');
        $data = $request->validate([
            'mode' => ['required', Rule::in(['cart', 'now'])],
            'product_id' => ['required_if:mode,now', 'nullable', 'integer', 'exists:products,id'],
            'quantity' => ['required_if:mode,now', 'nullable', 'integer', 'min:1', 'max:100'],
            'pickup_contact_name' => ['required', 'string', 'max:100'],
            'pickup_contact_phone' => ['required', 'string', 'max:30'],
            'accepted_terms' => ['required', 'accepted'],
            'payment_method' => ['sometimes', 'required', Rule::in([Payment::GATEWAY_MANUAL, Payment::GATEWAY_MIDTRANS])],
        ], [
            'accepted_terms.required' => 'Setujui Terms of Service sebelum mengonfirmasi pesanan.',
            'accepted_terms.accepted' => 'Setujui Terms of Service sebelum mengonfirmasi pesanan.',
            'pickup_contact_name.required' => 'Nama pengambil wajib diisi.',
            'pickup_contact_phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $paymentMethod = $data['payment_method'] ?? Payment::GATEWAY_MANUAL;

        if ($paymentMethod === Payment::GATEWAY_MIDTRANS) {
            abort_unless(filled(config('services.midtrans.server_key')), 503, 'Midtrans belum dikonfigurasi.');
        }

        $order = DB::transaction(function () use ($request, $data, $paymentMethod, $purchaseWindow): Order {
            if (! $purchaseWindow->canAcceptOrders()) {
                throw ValidationException::withMessages(['po' => 'Jendela PO sudah berubah. Pesanan baru tidak dapat dibuat.']);
            }

            $items = $data['mode'] === 'now'
                ? $this->buyNowItems((int) $data['product_id'], (int) $data['quantity'])
                : $this->cartItems($request);

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong. Pilih produk sebelum melanjutkan.']);
            }

            $total = 0;
            $orderItems = [];
            foreach ($items as $item) {
                $product = Product::query()->whereKey($item['product_id'])->lockForUpdate()->first();
                if (! $product || $product->status !== 'published' || $product->review_status !== 'approved' || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['cart' => 'Stok atau ketersediaan produk berubah. Periksa kembali pesananmu.']);
                }

                $product->loadMissing(['project', 'seller.project']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                if ($total > 4_294_967_295) {
                    throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas maksimum transaksi.']);
                }

                $projectId = $product->project_id ?? $product->seller->project_id;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price_at_order' => $product->price,
                    'subtotal' => $subtotal,
                    'carbon_footprint_value_at_purchase' => $product->carbon_footprint_value,
                    'carbon_footprint_unit_at_purchase' => CarbonImpactService::UNIT,
                    'carbon_baseline_emission_factor_at_purchase' => $product->carbon_baseline_emission_factor,
                    'carbon_baseline_source_at_purchase' => $product->carbon_baseline_source,
                    'carbon_product_source_at_purchase' => $product->carbon_product_source,
                    'carbon_functional_unit_at_purchase' => $product->carbon_functional_unit,
                    'carbon_quantity_at_purchase' => $item['quantity'],
                    'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
                    'carbon_snapshot_source' => 'checkout',
                    'project_id' => $projectId,
                    'class_key' => $projectId ? 'project-'.$projectId : 'seller-'.$product->seller_id,
                    'class_name' => $product->project?->class_name ?? $product->seller->project?->class_name ?? $product->seller->store_name,
                ];
                $product->decrement('stock', $item['quantity']);
            }

            $classGroups = collect($orderItems)->groupBy('class_key');
            foreach ($classGroups as $classItems) {
                if ($classItems->pluck('seller_id')->unique()->count() > 1) {
                    throw ValidationException::withMessages(['cart' => 'Setiap kelas hanya dapat berisi produk dari satu seller. Pisahkan produk kelas ini pada pesanan berbeda.']);
                }
            }

            $eventAt = $purchaseWindow->eventAt();
            if (! $eventAt) {
                throw ValidationException::withMessages(['po' => 'Tanggal acara belum dikonfigurasi. Hubungi admin sebelum memesan.']);
            }

            $order = Order::query()->create([
                'order_code' => 'TEMP-'.Str::upper(Str::random(12)),
                'buyer_id' => $request->user()->id,
                'status' => 'pending_payment',
                'total_price' => $total,
                'pickup_date' => $eventAt->toDateString(),
                'pickup_contact_name' => $data['pickup_contact_name'],
                'pickup_contact_phone' => $data['pickup_contact_phone'],
                'terms_accepted_at' => now(),
                'terms_version' => config('marketplace.terms_version'),
            ]);
            $order->update(['order_code' => 'FX-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT)]);

            $fulfillments = [];
            foreach ($classGroups as $classKey => $classItems) {
                $class = $classItems->first();
                $fulfillments[$classKey] = OrderFulfillment::query()->create([
                    'order_id' => $order->id,
                    'seller_id' => $class['seller_id'],
                    'project_id' => $class['project_id'],
                    'class_key' => $classKey,
                    'class_name' => $class['class_name'],
                    'pickup_code' => 'FTXC-'.Str::upper(Str::random(24)),
                ]);
            }

            foreach ($orderItems as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'seller_id' => $item['seller_id'],
                    'order_fulfillment_id' => $fulfillments[$item['class_key']]->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price_at_order' => $item['price_at_order'],
                    'subtotal' => $item['subtotal'],
                    'carbon_footprint_value_at_purchase' => $item['carbon_footprint_value_at_purchase'],
                    'carbon_footprint_unit_at_purchase' => CarbonImpactService::UNIT,
                    'carbon_baseline_emission_factor_at_purchase' => $item['carbon_baseline_emission_factor_at_purchase'],
                    'carbon_baseline_source_at_purchase' => $item['carbon_baseline_source_at_purchase'],
                    'carbon_product_source_at_purchase' => $item['carbon_product_source_at_purchase'],
                    'carbon_functional_unit_at_purchase' => $item['carbon_functional_unit_at_purchase'],
                    'carbon_quantity_at_purchase' => $item['carbon_quantity_at_purchase'],
                    'carbon_calculation_version_at_purchase' => $item['carbon_calculation_version_at_purchase'],
                    'carbon_snapshot_source' => 'checkout',
                ]);

                for ($unit = 0; $unit < $item['quantity']; $unit++) {
                    $orderItem->units()->create([
                        'qr_token' => 'FTXP-'.Str::upper(Str::random(28)),
                    ]);
                }
            }

            if ($data['mode'] === 'cart') {
                CartItem::query()->where('user_id', $request->user()->id)->delete();
            }

            if ($paymentMethod === Payment::GATEWAY_MIDTRANS) {
                $order->payment()->create([
                    'gateway' => Payment::GATEWAY_MIDTRANS,
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        if ($paymentMethod === Payment::GATEWAY_MIDTRANS) {
            try {
                $transaction = $midtrans->createSnapTransaction($order);
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                Log::warning('Midtrans checkout could not be completed.', [
                    'order_code' => $order->order_code,
                    'exception' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                return redirect()->route('orders.show', $order)
                    ->with('error', 'Halaman pembayaran belum berhasil disiapkan. Setelah koneksi Midtrans pulih, coba lagi dari halaman pesanan. Jika transaksi percobaan sudah terlihat di dashboard Midtrans, minta admin mengeceknya sebelum mengulang.');
            }

            $order->payment()->update([
                'midtrans_order_id' => $transaction['order_id'],
                'snap_token' => $transaction['token'],
                'snap_redirect_url' => $transaction['redirect_url'],
            ]);

            return redirect()->away($transaction['redirect_url']);
        }

        $status = $this->transferConfigured()
            ? 'Pesanan tersimpan. Silakan lanjutkan pembayaran dan unggah bukti transfer.'
            : 'Pesanan berhasil disimpan di database dan menunggu informasi pembayaran.';

        return redirect()->route('orders.show', $order)->with('status', $status);
    }

    public function orders(Request $request, CarbonImpactService $carbonImpact): View
    {
        $orders = $request->user()->orders()
            ->withCount('fulfillments')
            ->with(['fulfillments.seller', 'payment'])
            ->latest()
            ->paginate(10);

        return view('marketplace.orders', [
            'orders' => $orders,
            'carbonSummary' => $carbonImpact->buyerDashboardSummary($request->user()),
        ]);
    }

    public function order(Request $request, Order $order, PurchaseWindow $purchaseWindow): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        $order->loadMissing('payment');
        if (in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true)
            && in_array($order->payment?->status, ['verified', 'partially_refunded'], true)
            && ! $order->ticket()->exists()) {
            $order->ticket()->create([
                'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                'issued_at' => now(),
            ]);
        }
        $order->load(['buyer', 'fulfillments.seller.project', 'fulfillments.project', 'fulfillments.items.units', 'payment', 'ticket']);
        $eventAt = $purchaseWindow->eventAt();

        return view('marketplace.order', [
            'order' => $order,
            'pickupTime' => $eventAt?->toDateString() === $order->pickup_date->toDateString()
                ? $eventAt->format('H:i')
                : null,
            'pickupLocation' => $purchaseWindow->location(),
            'pickupInstructions' => $purchaseWindow->pickupInstructions(),
        ]);
    }

    public function retryMidtransPayment(Request $request, Order $order, MidtransService $midtrans): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        abort_unless($order->status === 'pending_payment', 422, 'Pesanan ini tidak menunggu pembayaran.');

        try {
            $redirectUrl = DB::transaction(function () use ($order, $midtrans): string {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                abort_unless($lockedOrder->status === 'pending_payment', 422, 'Status pesanan telah berubah.');

                $payment = $lockedOrder->payment()->lockForUpdate()->firstOrFail();
                abort_unless($payment->gateway === Payment::GATEWAY_MIDTRANS, 422, 'Pesanan ini tidak menggunakan Midtrans.');
                abort_unless($payment->status === 'pending', 422, 'Status pembayaran telah berubah.');

                if (filled($payment->snap_redirect_url)) {
                    return $payment->snap_redirect_url;
                }

                abort_unless(filled(config('services.midtrans.server_key')), 503, 'Midtrans belum dikonfigurasi.');

                $transaction = $midtrans->createSnapTransaction($lockedOrder);
                $payment->update([
                    'midtrans_order_id' => $transaction['order_id'],
                    'snap_token' => $transaction['token'],
                    'snap_redirect_url' => $transaction['redirect_url'],
                ]);

                return $transaction['redirect_url'];
            });
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Midtrans checkout retry could not be completed.', [
                'order_code' => $order->order_code,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Halaman pembayaran belum berhasil disiapkan. Periksa koneksi Midtrans, lalu coba lagi. Jika transaksi sudah terlihat di dashboard Midtrans, minta admin mengeceknya sebelum mengulang.');
        }

        return redirect()->away($redirectUrl);
    }

    public function refreshMidtransPaymentStatus(Request $request, Order $order, MidtransService $midtrans): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        abort_unless($order->status === 'pending_payment', 422, 'Pesanan ini tidak menunggu pembayaran.');

        $payment = $order->payment()->firstOrFail();
        abort_unless($payment->gateway === Payment::GATEWAY_MIDTRANS, 422, 'Pesanan ini tidak menggunakan Midtrans.');
        abort_unless($payment->status === 'pending', 422, 'Status pembayaran telah berubah.');

        try {
            $transactionStatus = $midtrans->syncTransactionStatus($order);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Midtrans payment status refresh could not be completed.', [
                'order_code' => $order->order_code,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Status pembayaran belum dapat diperiksa ke Midtrans. Coba lagi beberapa saat.');
        }

        return redirect()->route('orders.show', $order)
            ->with('status', 'Status Midtrans berhasil diperiksa: '.$transactionStatus.'.');
    }

    public function midtransPaymentStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);

        $order->loadMissing(['payment', 'ticket']);
        $payment = $order->payment;
        abort_unless($payment?->gateway === Payment::GATEWAY_MIDTRANS, 404);

        $ticketAvailable = in_array($payment->status, ['verified', 'partially_refunded'], true)
            && $order->ticket !== null
            && in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true);

        return response()->json([
            'payment_status' => $payment->status,
            'order_status' => $order->status,
            'ticket_available' => $ticketAvailable,
            'ticket_url' => $ticketAvailable ? route('tickets.show', $order) : null,
            'is_pending' => $payment->status === 'pending',
        ])->header('Cache-Control', 'no-store');
    }

    public function uploadPayment(Request $request, Order $order): RedirectResponse
    {
        abort_unless($this->transferConfigured(), 503, 'Informasi transfer bank belum dikonfigurasi.');
        abort_unless($order->buyer_id === $request->user()->id, 404);
        abort_unless($order->status === 'pending_payment', 422, 'Pesanan ini tidak menunggu pembayaran.');
        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'method' => ['required', 'string', 'max:50'],
        ]);
        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedOrder->status === 'pending_payment', 422, 'Status pesanan telah berubah.');
            abort_unless(
                ! $lockedOrder->payment()->where('gateway', Payment::GATEWAY_MIDTRANS)->exists(),
                422,
                'Pesanan ini menggunakan pembayaran Midtrans.',
            );
            $lockedOrder->payment()->create([
                'proof_image' => $data['proof']->store('payment-proofs', 'local'),
                'method' => $data['method'],
                'gateway' => Payment::GATEWAY_MANUAL,
                'status' => 'pending',
            ]);
            $lockedOrder->update(['status' => 'waiting_verification']);
        });

        return back()->with('status', 'Bukti transfer berhasil dikirim dan menunggu verifikasi admin.');
    }

    public function ticket(Request $request, Order $order): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        $order->loadMissing('payment');
        abort_unless(
            in_array($order->payment?->status, ['verified', 'partially_refunded'], true)
                && in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true),
            404,
        );
        if (! $order->ticket()->exists()) {
            $order->ticket()->create([
                'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                'issued_at' => now(),
            ]);
        }
        abort_unless($order->ticket()->exists(), 404);
        $order->load(['fulfillments.seller.project', 'fulfillments.project', 'fulfillments.items.units', 'ticket']);

        return view('marketplace.ticket', compact('order'));
    }

    private function availableProducts(): Builder
    {
        return Product::query()
            ->with(['seller.project', 'category', 'project'])
            ->where('status', 'published')
            ->where('review_status', 'approved');
    }

    /**
     * @return Collection<int, array{product_id: int, quantity: int}>
     */
    private function buyNowItems(int $productId, int $quantity): Collection
    {
        return collect([['product_id' => $productId, 'quantity' => $quantity]]);
    }

    /**
     * @return Collection<int, array{product_id: int, quantity: int}>
     */
    private function cartItems(Request $request): Collection
    {
        return CartItem::query()
            ->where('user_id', $request->user()->id)
            ->lockForUpdate()
            ->get()
            ->map(fn (CartItem $item): array => ['product_id' => $item->product_id, 'quantity' => $item->quantity]);
    }

    private function transferConfigured(): bool
    {
        return filled(config('marketplace.bank_name'))
            && filled(config('marketplace.account_name'))
            && filled(config('marketplace.account_number'));
    }
}
