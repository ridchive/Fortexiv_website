@extends('layouts.app')

@section('title', 'Kelola produk — FORTEXIV')

@section('content')
@php($reviewStatusLabels = ['draft' => 'Draf', 'pending_review' => 'Menunggu review admin', 'approved' => 'Disetujui', 'revision_requested' => 'Perlu revisi', 'rejected' => 'Ditolak'])
<div class="section-head"><h1>Produk Anda</h1><a class="btn" href="{{ route('seller.products.create') }}">Tambah produk</a></div>
<div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
  @forelse($products as $product)
    <tr>
      <td>{{ $product->name }}@if($product->review_notes)<p class="muted small">Catatan admin: {{ $product->review_notes }}</p>@endif</td><td>{{ $product->category?->name ?? '—' }}</td><td>{{ \App\Support\Money::format($product->price) }}</td><td>{{ $product->stock }}</td><td><span class="badge {{ $product->review_status }}">{{ $reviewStatusLabels[$product->review_status] ?? $product->review_status }}</span></td>
      <td class="stack">
        <a href="{{ route('seller.products.edit', $product) }}">Ubah</a>
        @if($product->review_status !== 'pending_review')
          <form method="post" action="{{ route('seller.products.submit-review', $product) }}">@csrf @method('PATCH')<button class="btn btn-small" type="submit">Ajukan review</button></form>
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
