@extends('layouts.app')

@section('title', 'Manage products — FORTEXIV')

@section('content')
<div class="section-head"><h1>Your products</h1><a class="btn" href="{{ route('seller.products.create') }}">Add product</a></div>
<div class="table-wrap"><table><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  @forelse($products as $product)
    <tr>
      <td>{{ $product->name }}</td><td>{{ $product->category?->name ?? '—' }}</td><td>{{ \App\Support\Money::format($product->price) }}</td><td>{{ $product->stock }}</td><td><span class="badge {{ $product->status }}">{{ ucfirst($product->status) }}</span></td>
      <td class="stack">
        <a href="{{ route('seller.products.edit', $product) }}">Edit</a>
        @if($product->status !== 'published')
          <form method="post" action="{{ route('seller.products.publish', $product) }}">@csrf @method('PATCH')<button class="btn btn-small" type="submit">Publish</button></form>
        @endif
        @if(!$product->orderItems()->exists())
          <form method="post" action="{{ route('seller.products.destroy', $product) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-small" type="submit">Delete</button></form>
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="6">No products yet. Add your first student-made product.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $products->links() }}</div>
@endsection
