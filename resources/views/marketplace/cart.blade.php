@extends('layouts.app')

@section('title', 'Keranjang — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Keranjang</span></nav>
  <div class="pgh">
    <h1>Keranjang</h1>
    <p>Pesanan dari beberapa kelas digabung saat checkout, lalu diserahkan per grup kelas saat acara.</p>
  </div>
  @include('marketplace.partials.po-notice', ['window' => $window])

  @if($items->isEmpty())
    <div class="empty">
      <h3>Keranjangmu masih kosong</h3>
      <p>Temukan karya siswa lewat cerita proyek atau katalog produk.</p>
      <div class="section-actions" style="justify-content:center">
        <a class="btn" href="{{ route('projects.index') }}">Jelajahi proyek</a>
        <a class="btn sec" href="{{ route('catalog.index') }}">Lihat katalog</a>
      </div>
    </div>
  @else
    @php($cartTotal = $items->sum(fn ($item) => $item->product->price * $item->quantity))
    @foreach($groups as $groupItems)
      @php($firstProduct = $groupItems->first()->product)
      <section class="panel">
        <div class="section-head" style="margin-top:0">
          <div>
            <span class="eyebrow">GRUP KELAS</span>
            <h2>{{ $firstProduct->project?->class_name ?? $firstProduct->seller->project?->class_name ?? $firstProduct->seller->store_name }}</h2>
          </div>
          @if($firstProduct->project)
            <a href="{{ route('projects.show', $firstProduct->project) }}">Baca cerita proyek →</a>
          @endif
        </div>
        @foreach($groupItems as $item)
          @php($product = $item->product)
          <article class="cart-i">
            @if($product->image)
              <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
            @else
              <div class="product-image" style="min-height:74px;padding:8px">{{ $product->category?->name ?? 'Produk' }}</div>
            @endif
            <div>
              <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
              <div class="muted">{{ $product->category?->name ?? 'Produk kelas' }} · {{ \App\Support\Money::format($product->price) }} / buah</div>
              @if($product->stock < $item->quantity || $product->status !== 'published')
                <p class="errors">Stok atau ketersediaan berubah. Perbarui jumlah sebelum checkout.</p>
              @endif
            </div>
            <div class="cart-actions row">
              <form class="inline-form" method="post" action="{{ route('cart.update', $item) }}">
                @csrf
                @method('PATCH')
                <label class="sr-only" for="quantity-{{ $item->id }}">Jumlah {{ $product->name }}</label>
                <input class="input" id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="100" value="{{ $item->quantity }}" required>
                <button class="btn sec sm" type="submit">Perbarui</button>
              </form>
              <strong>{{ \App\Support\Money::format($product->price * $item->quantity) }}</strong>
              <form method="post" action="{{ route('cart.destroy', $item) }}">
                @csrf
                @method('DELETE')
                <button class="btn bad sm" type="submit">Hapus</button>
              </form>
            </div>
          </article>
        @endforeach
      </section>
    @endforeach

    <section class="panel">
      <div class="kv"><span>Total pesanan</span><strong>{{ \App\Support\Money::format($cartTotal) }}</strong></div>
      <p class="muted">Tidak ada pengiriman. Setiap grup kelas diserahkan secara terpisah di lokasi acara.</p>
      @if($checkoutAvailable)
        <a class="btn" href="{{ route('checkout.show', ['mode' => 'cart']) }}">Lanjut ke checkout</a>
      @else
        <p class="note warn">Keranjang tetap tersimpan. Checkout tersedia saat jendela PO dibuka.</p>
      @endif
      <a class="btn sec" href="{{ route('catalog.index') }}">Lanjut belanja</a>
    </section>
  @endif
</div>
@endsection
