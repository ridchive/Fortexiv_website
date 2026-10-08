<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Project;
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
            'pendingCount' => $seller->products()->where('status', 'draft')->count(),
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
            'projects' => Project::where('is_published', true)->orderBy('class_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image'] = $request->file('image')?->store('products', 'public');
        $data['status'] = 'draft';
        $request->user()->seller->products()->create($data);

        return redirect()->route('seller.products.index')->with('status', 'Produk tersimpan sebagai draft.');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($request, $product);

        return view('seller.product-form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'projects' => Project::where('is_published', true)->orderBy('class_name')->get(),
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
        $product->update($data);

        return redirect()->route('seller.products.index')->with('status', 'Produk diperbarui.');
    }

    public function publish(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_if($product->stock < 1, 422, 'Tambahkan stok sebelum menerbitkan produk.');
        $product->update(['status' => 'published']);

        return back()->with('status', 'Produk sekarang tampil di marketplace.');
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
        $items = OrderItem::where('seller_id', $request->user()->seller->id)
            ->with(['order.buyer', 'order.payment', 'order.ticket'])
            ->latest()
            ->paginate(20);

        return view('seller.orders', compact('items'));
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
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
