<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOrderFulfillment;
use App\Models\Category;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $seller = $request->user()->seller;
        $items = OrderItem::where('seller_id', $seller->id)->with('order.buyer')->latest()->limit(8)->get();

        return view('seller.dashboard', [
            'seller' => $seller,
            'productsCount' => $seller->products()->count(),
            'ordersCount' => OrderItem::where('seller_id', $seller->id)->distinct('order_id')->count('order_id'),
            'pendingCount' => $seller->products()->where('review_status', 'pending_review')->count(),
            'items' => $items,
        ]);
    }

    public function products(Request $request): View
    {
        $products = $request->user()->seller->products()->with('category')->latest()->paginate(12);

        return view('seller.products', compact('products'));
    }

    public function create(Request $request): View
    {
        return view('seller.product-form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image'] = $request->file('image')?->store('products', 'public');
        $data['project_id'] = $request->user()->seller->project_id;
        $data['status'] = 'draft';
        $data['review_status'] = 'draft';
        $request->user()->seller->products()->create($data);

        return redirect()->route('seller.products.index')->with('status', 'Produk tersimpan sebagai draf. Ajukan untuk ditinjau admin agar dapat ditampilkan.');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($request, $product);

        return view('seller.product-form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        $data = $this->validatedProduct($request);
        if ($product->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
        } else {
            unset($data['image']);
        }
        $data['project_id'] = $request->user()->seller->project_id;
        $data['status'] = 'draft';
        $data['review_status'] = 'draft';
        $data['review_notes'] = null;
        $data['reviewed_by'] = null;
        $data['reviewed_at'] = null;
        $product->update($data);

        return redirect()->route('seller.products.index')->with('status', 'Perubahan tersimpan sebagai draf. Ajukan kembali untuk ditinjau admin.');
    }

    public function submitForReview(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_if($product->stock < 1, 422, 'Tambahkan stok sebelum mengajukan produk untuk ditinjau.');
        abort_if(! $product->category_id || ! $product->project_id, 422, 'Produk harus memiliki kategori dan proyek kelas.');
        abort_if($product->review_status === 'pending_review', 422, 'Produk ini sudah menunggu tinjauan admin.');

        $product->update([
            'status' => 'draft',
            'review_status' => 'pending_review',
            'review_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        return back()->with('status', 'Produk diajukan untuk ditinjau admin. Produk belum tampil di marketplace.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_if($product->orderItems()->exists(), 422, 'Produk yang sudah masuk riwayat pesanan tidak dapat dihapus. Arsipkan produk dengan mengubah stok menjadi nol.');
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return back()->with('status', 'Produk dihapus.');
    }

    public function orders(Request $request): View
    {
        $seller = $request->user()->seller;
        $items = OrderItem::where('seller_id', $seller->id)
            ->with(['order.buyer', 'order.payment', 'order.ticket'])
            ->latest()
            ->paginate(20);
        $fulfillments = OrderFulfillment::query()
            ->whereIn('order_id', $items->pluck('order_id'))
            ->where('seller_id', $seller->id)
            ->get()
            ->keyBy('order_id');

        foreach ($items as $item) {
            $item->setRelation('fulfillment', $fulfillments->get($item->order_id));
        }

        return view('seller.orders', compact('items'));
    }

    public function pickup(Request $request, CompleteOrderFulfillment $completeOrderFulfillment): RedirectResponse
    {
        $data = $request->validate(['pickup_code' => ['required', 'string', 'max:32']]);
        $seller = $request->user()->seller;

        $fulfillment = $completeOrderFulfillment->handle($data['pickup_code'], $seller, $request->user());

        return back()->with('status', "Grup {$fulfillment->seller->store_name} pada pesanan {$fulfillment->order->order_code} berhasil diserahkan.");
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->seller->id, 404);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
