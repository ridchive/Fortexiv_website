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
      <div class="field">
        <label for="carbon_functional_unit">Unit fungsional yang sebanding</label>
        <input class="input" id="carbon_functional_unit" name="carbon_functional_unit" maxlength="120" value="{{ old('carbon_functional_unit', $product->carbon_functional_unit) }}" placeholder="Contoh: 1 tas dengan kapasitas 10 liter">
      </div>
      <div class="field">
        <label for="carbon_baseline_emission_factor">Faktor emisi baseline (kg CO2e per unit fungsional)</label>
        <input class="input" id="carbon_baseline_emission_factor" name="carbon_baseline_emission_factor" type="number" step="0.000001" min="0" max="999999999999.999999" value="{{ old('carbon_baseline_emission_factor', $product->carbon_baseline_emission_factor) }}">
      </div>
      <div class="field">
        <label for="carbon_baseline_source">Sumber/metodologi faktor baseline</label>
        <textarea class="input" id="carbon_baseline_source" name="carbon_baseline_source" rows="2" maxlength="2000">{{ old('carbon_baseline_source', $product->carbon_baseline_source) }}</textarea>
      </div>
      <div class="field">
        <label for="carbon_footprint_value">Faktor emisi produk FORTEXIV (kg CO2e per unit fungsional)</label>
        <input class="input" id="carbon_footprint_value" name="carbon_footprint_value" type="number" step="0.000001" min="0" max="999999999999.999999" value="{{ old('carbon_footprint_value', $product->carbon_footprint_value) }}">
      </div>
      <div class="field">
        <label for="carbon_product_source">Sumber/metodologi faktor produk FORTEXIV</label>
        <textarea class="input" id="carbon_product_source" name="carbon_product_source" rows="2" maxlength="2000">{{ old('carbon_product_source', $product->carbon_product_source) }}</textarea>
      </div>
      <p class="muted">Isi kedua faktor berdasarkan batas fungsional yang sebanding. Perhitungan menggunakan kg CO2e; tanpa pasangan faktor lengkap, dampak transaksi ditandai belum lengkap.</p>
      <div class="field"><label for="review_notes">Catatan untuk seller (wajib untuk revisi/penolakan)</label><textarea class="input" id="review_notes" name="review_notes" rows="4" maxlength="2000">{{ old('review_notes') }}</textarea></div>
      <div class="stack"><button class="btn" name="decision" value="approved" type="submit">Setujui & publikasikan</button><button class="btn sec" name="decision" value="revision_requested" type="submit">Minta revisi</button><button class="btn bad" name="decision" value="rejected" type="submit">Tolak</button></div>
    </form>
  </section>
</div></div>
@endsection
