@extends('layouts.app')

@section('title', $product->name.' — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('catalog.index') }}">Katalog</a> / <span>{{ $product->name }}</span></nav>
  @include('marketplace.partials.po-notice', ['window' => $window])
  <article class="detail-card product-detail-card">
    <div class="detail-image">
      @if($product->image)
        <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
      @else
        @include('marketplace.partials.product-photo', ['category' => $product->category?->name, 'alt' => 'Foto referensi '.$product->name])
      @endif
    </div>
    <div class="detail-copy">
      <div class="row">
        <span class="chip">{{ $product->category?->name ?? 'Karya kelas' }}</span>
        @if($product->stock > 0)
          <span class="badge open">Stok {{ $product->stock }}</span>
        @else
          <span class="badge closed">Stok habis</span>
        @endif
      </div>
      <h1>{{ $product->name }}</h1>
      <div class="price" style="font:800 1.5rem var(--fd); margin-bottom: 16px;">{{ \App\Support\Money::format($product->price) }}</div>

      <section class="box">
        <span class="eyebrow">INFORMASI PRODUK</span>
        <p class="muted" style="margin: 4px 0 8px; font-size: 13.5px;">{{ $product->project?->class_name ?? $product->seller->store_name }} &middot; {{ $product->category?->name ?? 'Produk kelas' }}</p>
        @if($product->description)
          <p style="margin: 0;">{{ $product->description }}</p>
        @endif
      </section>

      @if($product->story)
        <section class="box">
          <span class="eyebrow">CERITA PRODUK</span>
          <p style="margin: 4px 0 0;">{{ $product->story }}</p>
        </section>
      @endif

      @if($product->project)
        <section class="box">
          <span class="eyebrow">PROYEK KELAS TERKAIT</span>
          <h3 style="margin: 4px 0 0;"><a href="{{ route('projects.show', $product->project) }}">{{ $product->project->title }}</a></h3>
        </section>
      @endif

      @if($product->carbon_reduction_value !== null)
        <section class="box">
          <span class="eyebrow">PENGURANGAN EMISI PRODUK</span>
          <p style="margin: 4px 0 0;"><strong>{{ number_format((float) $product->carbon_reduction_value, 2, ',', '.') }} kg CO2e</strong> dikurangi per produk.</p>
        </section>
      @elseif($product->carbon_baseline_emission_factor !== null && $product->carbon_footprint_value !== null)
        <section class="box">
          <span class="eyebrow">FAKTOR EMISI PER UNIT FUNGSIONAL</span>
          <p style="margin: 4px 0 0;">Baseline konvensional: <strong>{{ number_format((float) $product->carbon_baseline_emission_factor, 2, ',', '.') }} kg CO2e</strong></p>
          <p>Produk FORTEXIV: <strong>{{ number_format((float) $product->carbon_footprint_value, 2, ',', '.') }} kg CO2e</strong></p>
          <p>Unit fungsional: {{ $product->carbon_functional_unit }}</p>
          <p class="muted">Sumber baseline: {{ $product->carbon_baseline_source }}</p>
          <p class="muted" style="margin-bottom: 0;">Sumber produk: {{ $product->carbon_product_source }}</p>
        </section>
      @endif

      <p style="margin: 12px 0 16px;"><a href="{{ route('help') }}">Petunjuk pengambilan &rarr;</a></p>
      @if($product->stock > 0 && $window['status'] === 'open')
        @auth
          @if(auth()->user()->role === 'buyer')
            <form method="post" action="{{ route('cart.store') }}" class="panel">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <div class="field">
                <label for="cart_quantity">Jumlah untuk keranjang</label>
                <input class="input" id="cart_quantity" name="quantity" type="number" value="{{ old('quantity', 1) }}" min="1" max="{{ min($product->stock, 100) }}" required>
              </div>
              <button class="btn sec" type="submit">Tambah ke Keranjang</button>
            </form>
            <form method="get" action="{{ route('checkout.show') }}" class="panel">
              <input type="hidden" name="mode" value="now">
              <input type="hidden" name="product" value="{{ $product->id }}">
              <div class="field">
                <label for="buy_now_quantity">Jumlah Beli Sekarang</label>
                <input class="input" id="buy_now_quantity" name="quantity" type="number" value="1" min="1" max="{{ min($product->stock, 100) }}" required>
              </div>
              <button class="btn" type="submit">Beli Sekarang</button>
              <p class="muted">Checkout hanya untuk produk ini. Isi keranjang tidak ikut dibayar.</p>
            </form>
          @else
            <p class="note warn">Masuk sebagai Buyer untuk membuat pesanan.</p>
          @endif
        @else
          <a class="btn" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">Masuk untuk memesan</a>
        @endauth
      @elseif($product->stock < 1)
        <p class="note bad">Stok produk ini habis. Pilih produk lain dari kelas yang sama.</p>
      @else
        <p class="note warn">Pemesanan belum tersedia karena {{ $window['status'] === 'upcoming' ? 'PO belum dibuka' : ($window['status'] === 'closed' ? 'PO sudah ditutup' : 'jadwal PO belum dikonfigurasi') }}.</p>
      @endif
    </div>
  </article>
</div>
@endsection
