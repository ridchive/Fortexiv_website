<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Product;
use App\Models\Project;
use App\Support\PurchaseWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function home(PurchaseWindow $purchaseWindow): View
    {
        return view('marketplace.home', [
            'projects' => Project::query()
                ->where('is_published', true)
                ->withCount(['products as published_products_count' => fn (Builder $query) => $query->where('status', 'published')])
                ->orderBy('id')
                ->limit(4)
                ->get(),
            'products' => $this->availableProducts()->where('stock', '>', 0)->limit(8)->get(),
            'window' => $purchaseWindow->details(),
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
            'classes' => Project::query()->where('is_published', true)->orderBy('class_name')->get(['id', 'class_name']),
            'categories' => Category::query()->orderBy('name')->get(),
            'window' => $purchaseWindow->details(),
        ]);
    }

    public function product(Product $product, PurchaseWindow $purchaseWindow): View
    {
        abort_unless($product->status === 'published', 404);
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
                ->with(['seller', 'category']),
        ]);

        return view('marketplace.project', [
            'project' => $project,
            'window' => $purchaseWindow->details(),
        ]);
    }

    public function help(): View
    {
        return view('marketplace.help');
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

        $product = Product::query()->whereKey($data['product_id'])->where('status', 'published')->firstOrFail();
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
        abort_if($cartItem->product->status !== 'published' || $data['quantity'] > $cartItem->product->stock, 422, 'Produk tidak tersedia sebanyak jumlah yang diminta. Hapus produk atau kurangi jumlahnya.');
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
            $product = Product::query()->whereKey($data['product'])->where('status', 'published')->with(['seller.project', 'project', 'category'])->firstOrFail();
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

    public function checkout(Request $request, PurchaseWindow $purchaseWindow): RedirectResponse
    {
        abort_unless($purchaseWindow->canAcceptOrders(), 422, 'PO belum dibuka atau sudah ditutup. Pesanan baru tidak dapat dibuat.');
        $data = $request->validate([
            'mode' => ['required', Rule::in(['cart', 'now'])],
            'product_id' => ['required_if:mode,now', 'nullable', 'integer', 'exists:products,id'],
            'quantity' => ['required_if:mode,now', 'nullable', 'integer', 'min:1', 'max:100'],
            'pickup_contact_name' => ['required', 'string', 'max:100'],
            'pickup_contact_phone' => ['required', 'string', 'max:30'],
            'accepted_terms' => ['required', 'accepted'],
        ], [
            'accepted_terms.accepted' => 'Setujui Terms of Service sebelum mengonfirmasi pesanan.',
            'pickup_contact_name.required' => 'Nama pengambil wajib diisi.',
            'pickup_contact_phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $order = DB::transaction(function () use ($request, $data, $purchaseWindow): Order {
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
                if (! $product || $product->status !== 'published' || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['cart' => 'Stok atau ketersediaan produk berubah. Periksa kembali pesananmu.']);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                if ($total > 4_294_967_295) {
                    throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas maksimum transaksi.']);
                }

                $orderItems[] = [
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price_at_order' => $product->price,
                    'subtotal' => $subtotal,
                ];
                $product->decrement('stock', $item['quantity']);
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
            $order->items()->createMany($orderItems);

            foreach (collect($orderItems)->pluck('seller_id')->unique() as $sellerId) {
                OrderFulfillment::query()->create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'pickup_code' => 'FTXG-'.Str::upper(Str::random(12)),
                ]);
            }

            if ($data['mode'] === 'cart') {
                CartItem::query()->where('user_id', $request->user()->id)->delete();
            }

            return $order;
        });

        $status = $this->transferConfigured()
            ? 'Pesanan tersimpan. Silakan lanjutkan pembayaran dan unggah bukti transfer.'
            : 'Pesanan berhasil disimpan di database dan menunggu informasi pembayaran.';

        return redirect()->route('orders.show', $order)->with('status', $status);
    }

    public function orders(Request $request): View
    {
        $orders = $request->user()->orders()
            ->withCount('fulfillments')
            ->with(['fulfillments.seller'])
            ->latest()
            ->paginate(10);

        return view('marketplace.orders', compact('orders'));
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        $order->load(['buyer', 'items.seller', 'fulfillments.seller.project', 'payment', 'ticket']);

        return view('marketplace.order', compact('order'));
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
            $lockedOrder->payment()->create([
                'proof_image' => $data['proof']->store('payment-proofs', 'local'),
                'method' => $data['method'],
                'status' => 'pending',
            ]);
            $lockedOrder->update(['status' => 'waiting_verification']);
        });

        return back()->with('status', 'Bukti transfer berhasil dikirim dan menunggu verifikasi admin.');
    }

    public function ticket(Request $request, Order $order): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        abort_unless($order->ticket()->exists(), 404);
        $order->load(['items', 'fulfillments.seller.project', 'ticket']);

        return view('marketplace.ticket', compact('order'));
    }

    private function availableProducts(): Builder
    {
        return Product::query()
            ->with(['seller.project', 'category', 'project'])
            ->where('status', 'published');
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
