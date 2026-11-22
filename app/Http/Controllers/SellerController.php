<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOrderFulfillment;
use App\Actions\CompleteOrderQr;
use App\Actions\PreviewOrderQr;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CarbonImpactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function dashboard(Request $request, CarbonImpactService $carbonImpact): View
    {
        $seller = $request->user()->seller;
        $items = OrderItem::where('seller_id', $seller->id)->with('order.buyer')->latest()->limit(8)->get();

        return view('seller.dashboard', [
            'seller' => $seller,
            'productsCount' => $seller->products()->count(),
            'ordersCount' => OrderItem::where('seller_id', $seller->id)->distinct('order_id')->count('order_id'),
            'pendingCount' => $seller->products()->where('review_status', 'pending_review')->count(),
            'carbonSummary' => $carbonImpact->sellerDashboardSummary($seller),
            'items' => $items,
        ]);
    }

    public function products(Request $request): View
    {
        $products = $request->user()->seller->products()->with('category')->latest()->paginate(12);

        return view('seller.products', compact('products'));
    }

    public function productStatus(Request $request): View
    {
        $seller = $request->user()->seller;
        $tab = $request->query('tab', 'totals');
        $selectedProductId = $request->query('product_id');
        $sort = $request->query('sort', 'newest');

        $allProducts = $seller->products()->orderBy('name')->get();

        $productsWithStats = $seller->products()
            ->with(['category', 'orderItems.order', 'orderItems.units'])
            ->get()
            ->map(function (Product $product) {
                $validItems = $product->orderItems->filter(function (OrderItem $item) {
                    return in_array($item->order?->status, ['paid', 'partially_picked_up', 'picked_up'], true);
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

        $salesQuery = OrderItem::query()
            ->where('seller_id', $seller->id)
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['paid', 'partially_picked_up', 'picked_up']);
            })
            ->with(['order.buyer', 'product', 'units']);

        if (filled($selectedProductId)) {
            $salesQuery->where('product_id', $selectedProductId);
        }

        if ($sort === 'oldest') {
            $salesQuery->oldest();
        } else {
            $salesQuery->latest();
        }

        $sales = $salesQuery->paginate(20)->withQueryString();

        return view('seller.product-status', [
            'seller' => $seller,
            'tab' => $tab,
            'allProducts' => $allProducts,
            'productsWithStats' => $productsWithStats,
            'sales' => $sales,
            'selectedProductId' => $selectedProductId,
            'sort' => $sort,
        ]);
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
        $data = $this->validatedProduct($request, requireImage: true);
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
        $data = $this->validatedProduct($request, requireImage: ! $product->image);
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
            ->with(['order.buyer', 'order.payment', 'fulfillment', 'units'])
            ->latest()
            ->paginate(20);

        return view('seller.orders', compact('items'));
    }

    public function qrScan(): View
    {
        return view('seller.qr-scan');
    }

    public function previewQr(Request $request, PreviewOrderQr $previewOrderQr): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['class', 'product'])],
            'token' => ['required', 'string', 'max:80'],
        ]);
        $preview = $previewOrderQr->handle(Str::upper(trim($data['token'])), $data['type'], $request->user()->seller);

        return response()->json(['data' => $preview]);
    }

    public function confirmQr(Request $request, CompleteOrderQr $completeOrderQr): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['class', 'product'])],
            'token' => ['required', 'string', 'max:80'],
        ]);
        $token = Str::upper(trim($data['token']));
        $seller = $request->user()->seller;

        if ($data['type'] === 'class') {
            $fulfillment = $completeOrderQr->completeClass($token, $seller, $request->user());

            return response()->json([
                'message' => "Seluruh produk kelas {$fulfillment->class_name} berhasil ditandai Delivered.",
                'status' => $fulfillment->status,
            ]);
        }

        $unit = $completeOrderQr->completeProduct($token, $seller, $request->user());
        $unit->load('orderItem');

        return response()->json([
            'message' => "Produk {$unit->orderItem->product_name} berhasil ditandai Delivered.",
            'status' => $unit->status,
        ]);
    }

    public function pickup(Request $request, CompleteOrderFulfillment $completeOrderFulfillment): RedirectResponse
    {
        $data = $request->validate(['pickup_code' => ['required', 'string', 'max:32']]);
        $seller = $request->user()->seller;

        $fulfillment = $completeOrderFulfillment->handle($data['pickup_code'], $seller, $request->user());

        return back()->with('status', "Grup {$fulfillment->seller->store_name} pada pesanan {$fulfillment->order->order_code} berhasil diserahkan.");
    }

    private function validatedProduct(Request $request, bool $requireImage = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => [$requireImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'stock.required' => 'Stok produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'image.required' => 'Gambar produk wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar yang didukung: JPG, JPEG, PNG, WebP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
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
