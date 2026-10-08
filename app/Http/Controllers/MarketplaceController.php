<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function home(Request $request): View
    {
        $products = Product::query()
            ->with(['seller', 'category', 'project'])
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->when($request->string('search')->trim()->toString(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($request->integer('category'), fn ($query, $category) => $query->where('category_id', $category))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.home', [
            'projects' => Project::query()->where('is_published', true)->orderBy('id')->get(),
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->status === 'published', 404);
        $product->load(['seller', 'category', 'project']);

        return view('marketplace.product', compact('product'));
    }

    public function project(Project $project): View
    {
        abort_unless($project->is_published, 404);
        $project->load(['products' => fn ($query) => $query->where('status', 'published')->where('stock', '>', 0)->with('seller')]);

        return view('marketplace.project', compact('project'));
    }

    public function account(Request $request): View
    {
        $user = $request->user()->load('seller.project');

        return view('marketplace.account', compact('user'));
    }

    public function cart(Request $request): View
    {
        $items = $request->user()->cartItems()->with('product.seller')->get();

        return view('marketplace.cart', compact('items'));
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $product = Product::whereKey($data['product_id'])->where('status', 'published')->firstOrFail();
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
        abort_if($data['quantity'] > $cartItem->product->stock, 422, 'Jumlah melebihi stok.');
        $cartItem->update(['quantity' => $data['quantity']]);

        return back()->with('status', 'Jumlah produk diperbarui.');
    }

    public function removeCart(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $cartItem->delete();

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        abort_unless($this->transferConfigured(), 503, 'Manual bank transfer details are not configured yet.');
        $data = $request->validate(['pickup_date' => ['required', 'date', 'after_or_equal:today']]);

        $order = DB::transaction(function () use ($request, $data) {
            $cartItems = CartItem::query()->where('user_id', $request->user()->id)->with('product')->lockForUpdate()->get();
            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang kamu masih kosong.']);
            }

            $total = 0;
            $orderItems = [];
            foreach ($cartItems as $cartItem) {
                $product = Product::query()->whereKey($cartItem->product_id)->lockForUpdate()->first();
                if (! $product || $product->status !== 'published' || $product->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages(['cart' => 'Stok salah satu produk berubah. Periksa kembali keranjangmu.']);
                }

                $subtotal = $product->price * $cartItem->quantity;
                $total += $subtotal;
                if ($total > 4_294_967_295) {
                    throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas maksimum transaksi.']);
                }
                $orderItems[] = [
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'product_name' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'price_at_order' => $product->price,
                    'subtotal' => $subtotal,
                ];
                $product->decrement('stock', $cartItem->quantity);
            }

            $order = Order::create([
                'order_code' => 'TEMP-'.Str::upper(Str::random(12)),
                'buyer_id' => $request->user()->id,
                'status' => 'pending_payment',
                'total_price' => $total,
                'pickup_date' => $data['pickup_date'],
            ]);
            $order->update(['order_code' => 'FX-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT)]);
            $order->items()->createMany($orderItems);
            CartItem::where('user_id', $request->user()->id)->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('status', 'Pesanan dibuat. Silakan unggah bukti transfer manual.');
    }

    public function orders(Request $request): View
    {
        $orders = $request->user()->orders()->with('items')->latest()->paginate(10);

        return view('marketplace.orders', compact('orders'));
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
        $order->load(['items.seller', 'payment', 'ticket']);

        return view('marketplace.order', compact('order'));
    }

    public function uploadPayment(Request $request, Order $order): RedirectResponse
    {
        abort_unless($this->transferConfigured(), 503, 'Manual bank transfer details are not configured yet.');
        abort_unless($order->buyer_id === $request->user()->id, 404);
        abort_unless($order->status === 'pending_payment', 422, 'Pesanan ini tidak menunggu pembayaran.');
        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'method' => ['required', 'string', 'max:50'],
        ]);
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
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
        $order->load(['items', 'ticket']);

        return view('marketplace.ticket', compact('order'));
    }

    private function transferConfigured(): bool
    {
        return filled(config('marketplace.bank_name'))
            && filled(config('marketplace.account_name'))
            && filled(config('marketplace.account_number'));
    }
}
