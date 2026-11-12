@extends('layouts.app')

@section('title', 'Kelola produk — FORTEXIV')

@section('content')
@php($productStatusLabels = ['draft' => 'Draf', 'published' => 'Diterbitkan'])
<div class="section-head"><h1>Produk Anda</h1><a class="btn" href="{{ route('seller.products.create') }}">Tambah produk</a></div>
<div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
  @forelse($products as $product)
    <tr>
      <td>{{ $product->name }}</td><td>{{ $product->category?->name ?? '—' }}</td><td>{{ \App\Support\Money::format($product->price) }}</td><td>{{ $product->stock }}</td><td><span class="badge {{ $product->status }}">{{ $productStatusLabels[$product->status] ?? $product->status }}</span></td>
      <td class="stack">
        <a href="{{ route('seller.products.edit', $product) }}">Ubah</a>
        @if($product->status !== 'published')
          <form method="post" action="{{ route('seller.products.publish', $product) }}">@csrf @method('PATCH')<button class="btn btn-small" type="submit">Terbitkan</button></form>
        @endif
        @if(!$product->orderItems()->exists())
          <form method="post" action="{{ route('seller.products.destroy', $product) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-small" type="submit">Hapus</button></form>
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="6">Belum ada produk. Tambahkan produk karya siswa pertama Anda.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $products->links() }}</div>
@endsection
