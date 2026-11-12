@extends('layouts.app')

@section('title', 'Review produk — Admin FORTEXIV')

@section('content')
@php($labels = ['pending_review' => 'Menunggu review', 'approved' => 'Disetujui', 'revision_requested' => 'Perlu revisi', 'rejected' => 'Ditolak', 'draft' => 'Draf'])
<div class="section-head"><div><span class="eyebrow">KATALOG</span><h1>Review produk</h1></div><a class="btn sec" href="{{ route('admin.articles.index') }}">Kelola artikel</a></div>
<p class="muted">Produk baru dan perubahan produk harus ditinjau Admin. Produk hanya tampil untuk publik setelah disetujui.</p>
<div class="chip-row">@foreach($labels as $key => $label)<a class="chip {{ $status === $key ? 'good' : '' }}" href="{{ route('admin.products.index', ['status' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>@endforeach</div>
<div class="table-wrap"><table><thead><tr><th>Produk</th><th>Seller / kelas</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Catatan review</th><th></th></tr></thead><tbody>
  @forelse($products as $product)
    <tr><td><strong>{{ $product->name }}</strong></td><td>{{ $product->seller->project?->class_name ?? $product->seller->store_name }}</td><td>{{ $product->category?->name ?? '—' }}</td><td>{{ \App\Support\Money::format($product->price) }}</td><td>{{ $product->stock }}</td><td>{{ $product->review_notes ?? '—' }}</td><td><a class="btn sec sm" href="{{ route('admin.products.review', $product) }}">Tinjau</a></td></tr>
  @empty
    <tr><td colspan="7">Tidak ada produk pada status ini.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $products->links() }}</div>
@endsection
