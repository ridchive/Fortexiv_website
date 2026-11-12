@extends('layouts.app')

@section('title', 'Tinjau '.$product->name.' — Admin FORTEXIV')

@section('content')
<div class="wrap"><nav class="crumb"><a href="{{ route('admin.products.index') }}">Review produk</a> / <span>{{ $product->name }}</span></nav>
<div class="two">
  <section class="panel">
    <div class="eyebrow">DATA PRODUK DARI SELLER</div><h1>{{ $product->name }}</h1>
    @if($product->image)<p><img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" style="max-width:100%;max-height:320px;object-fit:contain"></p>@endif
    <div class="kv"><span>Seller / kelas</span><strong>{{ $product->seller->project?->class_name ?? $product->seller->store_name }}</strong></div>
    <div class="kv"><span>Kategori</span><strong>{{ $product->category?->name ?? '—' }}</strong></div>
    <div class="kv"><span>Harga / stok</span><strong>{{ \App\Support\Money::format($product->price) }} / {{ $product->stock }}</strong></div>
    <p>{{ $product->description }}</p>
    @if($product->project)<p>Proyek: <a href="{{ route('projects.show', $product->project) }}">{{ $product->project->title }}</a></p>@endif
    <p>Status: <span class="badge {{ $product->review_status }}">{{ str_replace('_', ' ', $product->review_status) }}</span></p>
  </section>
  <section class="form-card">
    <h2>Keputusan Admin</h2>
    <p class="muted">Admin mengelola cerita produk dan keputusan publikasi. Catatan revisi/penolakan wajib diberikan agar seller tahu tindak lanjutnya.</p>
    @if($product->review_notes)<p class="note warn">Catatan sebelumnya: {{ $product->review_notes }}</p>@endif
    <form method="post" action="{{ route('admin.products.decide', $product) }}">
      @csrf @method('PUT')
      <div class="field"><label for="story">Cerita produk (ditulis/dikelola Admin)</label><textarea class="input" id="story" name="story" rows="8" maxlength="10000">{{ old('story', $product->story) }}</textarea></div>
      <div class="field"><label for="review_notes">Catatan untuk seller (wajib untuk revisi/penolakan)</label><textarea class="input" id="review_notes" name="review_notes" rows="4" maxlength="2000">{{ old('review_notes') }}</textarea></div>
      <div class="stack"><button class="btn" name="decision" value="approved" type="submit">Setujui & publikasikan</button><button class="btn sec" name="decision" value="revision_requested" type="submit">Minta revisi</button><button class="btn bad" name="decision" value="rejected" type="submit">Tolak</button></div>
    </form>
  </section>
</div></div>
@endsection
