<?php

namespace App\Http\Controllers;

use App\Actions\CancelOrder;
use App\Actions\CompleteOrderQr;
use App\Mail\PaymentProofUploadedMail;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\TermsVersion;
use App\Services\CarbonImpactService;
use App\Services\EmailService;
use App\Support\PurchaseWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function home(PurchaseWindow $purchaseWindow): View
    {
        $projects = Project::query()
            ->where('is_published', true)
            ->when(Schema::hasColumn('projects', 'has_article'), fn ($q) => $q->where('has_article', true))
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
        $hasArticleColumn = Schema::hasColumn('projects', 'has_article');

        $projects = Project::query()
            ->where('is_published', true)
            ->when($hasArticleColumn, fn ($q) => $q->where('has_article', true))
            ->when($request->integer('class'), fn (Builder $query, int $classId) => $query->whereKey($classId))
            ->orderBy('grade')
            ->orderBy('class_name')
            ->paginate(12)
            ->withQueryString();

        $classesQuery = Project::query()->where('is_published', true);
        if ($hasArticleColumn) {
            $classesQuery->where('has_article', true);
        }

        return view('marketplace.projects', [
            'projects' => $projects,
            'classes' => $classesQuery->orderBy('grade')->orderBy('class_name')->get(['id', 'class_name', 'grade']),
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
                ->orderBy('grade')
                ->orderBy('class_name')
                ->get(['id', 'class_name', 'grade']),
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
        abort_unless($project->is_published && (! Schema::hasColumn('projects', 'has_article') || $project->has_article), 404);
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
        $publishedVersion = TermsVersion::currentPublished();

        return view('marketplace.terms', [
            'termsVersion' => $publishedVersion->version_label,
            'sections' => $publishedVersion->sections,
            'isPreview' => false,
        ]);
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
        if ($cartItem->product->status !== 'published' || $cartItem->product->review_status !== 'approved' || $data['quantity'] > $cartItem->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Produk tidak tersedia sebanyak jumlah yang diminta. Hapus produk atau kurangi jumlahnya.',
            ]);
        }
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
        $publishedTerms = TermsVersion::currentPublished();

        $activePendingOrder = $request->user()->orders()
            ->where('status', 'pending_payment')
            ->latest('id')
            ->first();

        if ($activePendingOrder && $activePendingOrder->isExpired()) {
            $activePendingOrder = null;
        }

        return view('marketplace.checkout', [
            'items' => $items,
            'groups' => $groups,
            'mode' => $mode,
            'total' => $items->sum('subtotal'),
            'window' => $purchaseWindow->details(),
            'checkoutAvailable' => $purchaseWindow->canAcceptOrders(),
            'termsVersion' => $publishedTerms->version_label,
            'termsVersionId' => $publishedTerms->id,
            'manualPaymentConfigured' => $this->transferConfigured(),
            'activePendingOrder' => $activePendingOrder,
        ]);
    }

    public function checkout(Request $request, PurchaseWindow $purchaseWindow): RedirectResponse
    {
        abort_unless($purchaseWindow->canAcceptOrders(), 422, 'PO belum dibuka atau sudah ditutup. Pesanan baru tidak dapat dibuat.');
        $data = $request->validate([
            'mode' => ['required', Rule::in(['cart', 'now'])],
            'product_id' => ['required_if:mode,now', 'nullable', 'integer', 'exists:products,id'],
            'quantity' => ['required_if:mode,now', 'nullable', 'integer', 'min:1', 'max:100'],
            'pickup_contact_name' => ['nullable', 'string', 'max:100'],
            'pickup_contact_phone' => ['nullable', 'string', 'max:30'],
            'accepted_terms' => ['required', 'accepted'],
            'terms_version_id' => ['nullable', 'integer', 'exists:terms_versions,id'],
            'payment_method' => ['sometimes', 'required', Rule::in([Payment::GATEWAY_MANUAL])],
        ], [
            'accepted_terms.required' => 'Setujui syarat & ketentuan sebelum mengonfirmasi pesanan.',
            'accepted_terms.accepted' => 'Setujui syarat & ketentuan sebelum mengonfirmasi pesanan.',
        ]);

        // Cek jika user sudah membuat pesanan pending_payment aktif (belum expired) untuk produk yang sama
        $existingPendingOrder = $request->user()->orders()
            ->where('status', 'pending_payment')
            ->latest('id')
            ->first();

        if ($existingPendingOrder && ! $existingPendingOrder->isExpired()) {
            if ($data['mode'] === 'now') {
                $hasMatchingProduct = $existingPendingOrder->items()
                    ->where('product_id', (int) $data['product_id'])
                    ->exists();

                if ($hasMatchingProduct) {
                    return redirect()->route('orders.show', $existingPendingOrder)
                        ->with('status', 'Mengarahkan ke pesanan Anda yang sedang menunggu pembayaran.');
                }
            }
        }

        $pickupContactName = trim((string) ($request->input('pickup_contact_name') ?: $request->user()->name));
        $pickupContactPhone = trim((string) ($request->input('pickup_contact_phone') ?: $request->user()->phone ?: '-'));

        $order = DB::transaction(function () use ($request, $data, $purchaseWindow, $pickupContactName, $pickupContactPhone): Order {
            if (! $purchaseWindow->canAcceptOrders()) {
                throw ValidationException::withMessages(['po' => 'Jendela PO sudah berubah. Pesanan baru tidak dapat dibuat.']);
            }

            $publishedTerms = TermsVersion::query()
                ->where('status', 'published')
                ->latest('id')
                ->lockForUpdate()
                ->firstOrFail();
            if (isset($data['terms_version_id']) && (int) $data['terms_version_id'] !== $publishedTerms->id) {
                throw ValidationException::withMessages([
                    'accepted_terms' => 'Terms of Service telah diperbarui. Tinjau versi terbaru dan setujui sebelum checkout.',
                ]);
            }

            $items = $data['mode'] === 'now'
                ? $this->buyNowItems((int) $data['product_id'], (int) $data['quantity'])
                : $this->cartItems($request);

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong. Pilih produk sebelum melanjutkan.']);
            }

            // Urutkan item berdasarkan product_id secara menaik untuk mencegah database deadlock
            $items = $items->sortBy('product_id')->values();

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
                    'carbon_reduction_value_at_purchase' => $product->carbon_reduction_value,
                    'carbon_footprint_value_at_purchase' => $product->carbon_reduction_value !== null ? 0 : $product->carbon_footprint_value,
                    'carbon_footprint_unit_at_purchase' => CarbonImpactService::UNIT,
                    'carbon_baseline_emission_factor_at_purchase' => $product->carbon_reduction_value !== null ? $product->carbon_reduction_value : $product->carbon_baseline_emission_factor,
                    'carbon_baseline_source_at_purchase' => $product->carbon_reduction_value !== null ? 'Nilai pengurangan karbon langsung per produk' : $product->carbon_baseline_source,
                    'carbon_product_source_at_purchase' => $product->carbon_reduction_value !== null ? 'Nilai pengurangan karbon langsung per produk' : $product->carbon_product_source,
                    'carbon_functional_unit_at_purchase' => $product->carbon_reduction_value !== null ? '1 produk' : $product->carbon_functional_unit,
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

            $timeoutMinutes = config('marketplace.payment_timeout_minutes', 10);
            $orderTimeout = now()->addMinutes($timeoutMinutes);
            $globalDeadline = $purchaseWindow->paymentDeadline();
            $expiresAt = $globalDeadline ? $orderTimeout->min($globalDeadline) : $orderTimeout;

            $order = Order::query()->create([
                'order_code' => 'TEMP-'.Str::upper(Str::random(12)),
                'buyer_id' => $request->user()->id,
                'status' => 'pending_payment',
                'total_price' => $total,
                'pickup_date' => $eventAt->toDateString(),
                'pickup_contact_name' => $pickupContactName,
                'pickup_contact_phone' => $pickupContactPhone,
                'terms_accepted_at' => now(),
                'terms_version_id' => $publishedTerms->id,
                'terms_version' => $publishedTerms->version_label,
                'expires_at' => $expiresAt,
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
                    'carbon_reduction_value_at_purchase' => $item['carbon_reduction_value_at_purchase'],
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

            return $order;
        });

        $status = $this->transferConfigured()
            ? 'Pesanan tersimpan. Silakan lanjutkan pembayaran manual dan unggah bukti pembayaran.'
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

    public function order(Request $request, Order $order, PurchaseWindow $purchaseWindow, CancelOrder $cancelOrder): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);

        if ($order->status === 'pending_payment' && $order->isExpired()) {
            $cancelOrder->handle($order, 'Pesanan dibatalkan otomatis karena melewati batas waktu pembayaran.');
            $order->refresh();
        }

        $order->loadMissing('payment');
        if (in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true)
            && in_array($order->payment?->status, ['verified', 'partially_refunded'], true)
            && ! $order->ticket()->exists()) {
            try {
                $order->ticket()->firstOrCreate([], [
                    'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                    'issued_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException|QueryException) {
                // Idempotent: Abaikan jika tiket telah dibuat secara bersamaan oleh request paralel
            }
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
            'manualPaymentConfigured' => $this->transferConfigured(),
            'qrisImage' => config('marketplace.qris_image'),
        ]);
    }

    public function uploadPayment(Request $request, Order $order, EmailService $emailService, CancelOrder $cancelOrder): RedirectResponse
    {
        abort_unless($this->transferConfigured(), 503, 'Instruksi pembayaran manual belum dikonfigurasi.');
        abort_unless($order->buyer_id === $request->user()->id, 404);

        if ($order->status === 'pending_payment' && $order->isExpired()) {
            $cancelOrder->handle($order, 'Pesanan dibatalkan otomatis karena melewati batas waktu pembayaran.');
            abort(422, 'Batas waktu pembayaran untuk pesanan ini telah berakhir.');
        }

        abort_unless($order->status === 'pending_payment', 422, 'Pesanan ini tidak menunggu pembayaran.');

        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'method' => ['required', 'string', 'max:50'],
        ]);

        $oldProofPath = null;
        $newProofPath = null;

        DB::transaction(function () use ($order, $data, $cancelOrder, &$oldProofPath, &$newProofPath): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status === 'pending_payment' && $lockedOrder->isExpired()) {
                $cancelOrder->handle($lockedOrder, 'Pesanan dibatalkan otomatis karena melewati batas waktu pembayaran.');
                abort(422, 'Batas waktu pembayaran untuk pesanan ini telah berakhir.');
            }

            abort_unless($lockedOrder->status === 'pending_payment', 422, 'Status pesanan telah berubah.');

            $payment = $lockedOrder->payment()->lockForUpdate()->first();
            abort_unless(
                $payment === null || ($payment->gateway === Payment::GATEWAY_MANUAL && $payment->status === 'rejected'),
                422,
                'Pesanan ini tidak dapat menerima bukti pembayaran baru.',
            );

            if ($payment && $payment->proof_image) {
                $oldProofPath = $payment->proof_image;
            }

            $newProofPath = $data['proof']->store('payment-proofs', 'local');

            $paymentAttributes = [
                'proof_image' => $newProofPath,
                'method' => $data['method'],
                'gateway' => Payment::GATEWAY_MANUAL,
                'status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
                'notes' => null,
                'received_amount' => null,
            ];
            if ($payment) {
                $payment->update($paymentAttributes);
            } else {
                $lockedOrder->payment()->create($paymentAttributes);
            }
            $lockedOrder->update(['status' => 'waiting_verification']);
        });

        if ($oldProofPath && $oldProofPath !== $newProofPath && Storage::disk('local')->exists($oldProofPath)) {
            Storage::disk('local')->delete($oldProofPath);
        }

        try {
            $order->loadMissing('buyer');
            if ($order->buyer?->email) {
                $emailService->send(
                    new PaymentProofUploadedMail($order),
                    $order->buyer->email
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment proof uploaded email', [
                'order_code' => $order->order_code,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Bukti transfer berhasil dikirim! Pembayaran Anda sedang diproses dan diverifikasi secara manual oleh admin. Proses verifikasi dapat memakan waktu beberapa saat. Tiket pickup QR akan otomatis aktif setelah pembayaran diverifikasi.');
    }

    public function ticket(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);

        return redirect()->route('tickets.hub');
    }

    public function pickupHub(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->whereIn('status', ['paid', 'partially_picked_up'])
            ->with(['fulfillments.seller.project', 'fulfillments.project', 'fulfillments.items.units'])
            ->latest()
            ->get();

        $stores = [];
        foreach ($orders as $order) {
            foreach ($order->fulfillments as $fulfillment) {
                $seller = $fulfillment->seller;
                if (! $seller) {
                    continue;
                }
                $sellerId = $seller->id;

                if (! isset($stores[$sellerId])) {
                    $className = $fulfillment->class_name ?? $fulfillment->project?->class_name ?? $seller->store_name;
                    $stores[$sellerId] = [
                        'seller' => $seller,
                        'class_name' => $className,
                        'pickup_code' => CompleteOrderQr::generateStoreToken($user->id, $sellerId),
                        'total_pending' => 0,
                        'total_delivered' => 0,
                        'products' => [],
                    ];
                }

                foreach ($fulfillment->items as $item) {
                    $pName = $item->product_name;
                    if (! isset($stores[$sellerId]['products'][$pName])) {
                        $stores[$sellerId]['products'][$pName] = [
                            'name' => $pName,
                            'quantity' => 0,
                            'pending' => 0,
                            'delivered' => 0,
                        ];
                    }

                    $pendingUnits = $item->units->where('status', 'pending')->count();
                    $deliveredUnits = $item->units->where('status', 'delivered')->count();

                    $stores[$sellerId]['products'][$pName]['quantity'] += (int) $item->quantity;
                    $stores[$sellerId]['products'][$pName]['pending'] += $pendingUnits;
                    $stores[$sellerId]['products'][$pName]['delivered'] += $deliveredUnits;

                    $stores[$sellerId]['total_pending'] += $pendingUnits;
                    $stores[$sellerId]['total_delivered'] += $deliveredUnits;
                }
            }
        }

        uasort($stores, function ($a, $b) {
            if ($a['total_pending'] > 0 && $b['total_pending'] === 0) {
                return -1;
            }
            if ($a['total_pending'] === 0 && $b['total_pending'] > 0) {
                return 1;
            }

            return strcmp($a['class_name'], $b['class_name']);
        });

        return view('marketplace.pickup-hub', [
            'stores' => $stores,
            'activeCount' => count(array_filter($stores, fn ($s) => $s['total_pending'] > 0)),
        ]);
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

    public function qrisImage()
    {
        if (Storage::disk('public')->exists('qris-statis.png')) {
            return response()->file(Storage::disk('public')->path('qris-statis.png'), [
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        $defaultPath = public_path(config('marketplace.qris_image', 'images/qris-statis.png'));
        if (is_file($defaultPath)) {
            return response()->file($defaultPath, [
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        $basePublicPath = base_path('public/images/qris-statis.png');
        if (is_file($basePublicPath)) {
            return response()->file($basePublicPath, [
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        abort(404);
    }

    private function transferConfigured(): bool
    {
        $qrisImage = config('marketplace.qris_image');
        $hasQris = Storage::disk('public')->exists('qris-statis.png')
            || (filled($qrisImage) && is_file(public_path($qrisImage)));

        return $hasQris
            || (filled(config('marketplace.bank_name'))
            && filled(config('marketplace.account_name'))
            && filled(config('marketplace.account_number')));
    }
}
