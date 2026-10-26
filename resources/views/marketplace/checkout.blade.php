@extends('layouts.app')

@section('title', 'Checkout — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('cart.index') }}">Keranjang</a> / <span>Checkout</span></nav>
  <div class="pgh">
    <h1>Konfirmasi pesanan</h1>
    <p>{{ $mode === 'now' ? 'Beli Sekarang hanya memproses produk yang kamu pilih.' : 'Semua produk keranjang dikonfirmasi sebagai satu pesanan, dengan pengambilan terpisah per grup kelas.' }}</p>
  </div>
  @include('marketplace.partials.po-notice', ['window' => $window])

  @foreach($groups as $groupItems)
    @php($firstProduct = $groupItems->first()['product'])
    <section class="panel">
      <div class="section-head" style="margin-top:0">
        <div>
          <span class="eyebrow">GRUP PICKUP · KELAS</span>
          <h2>{{ $firstProduct->project?->class_name ?? $firstProduct->seller->project?->class_name ?? $firstProduct->seller->store_name }}</h2>
        </div>
        @if($firstProduct->project)
          <a href="{{ route('projects.show', $firstProduct->project) }}">Cerita proyek →</a>
        @endif
      </div>
      @foreach($groupItems as $item)
        <div class="kv">
          <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
          <strong>{{ \App\Support\Money::format($item['subtotal']) }}</strong>
        </div>
      @endforeach
    </section>
  @endforeach

  <div class="two">
    <section class="panel">
      <h2>Kontak pengambil</h2>
      @if(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
        <p class="muted">Setelah pesanan dibuat, unggah bukti transfer. Pesanan siap diambil setelah pembayaran diverifikasi.</p>
      @else
        <p class="note warn">Pesanan tetap akan disimpan dengan status menunggu pembayaran. Metode pembayaran belum dikonfigurasi; jangan lakukan pembayaran sampai instruksi resmi tersedia.</p>
      @endif
      @if($window['paymentDeadline'])
        <p><strong>Tenggat pembayaran:</strong> {{ $window['paymentDeadline']->translatedFormat('d/m/Y H:i') }}</p>
      @endif
      @if($window['eventAt'])
        <p><strong>Pengambilan:</strong> {{ $window['eventAt']->translatedFormat('d/m/Y H:i') }}</p>
      @endif
      @if($window['location'])
        <p><strong>Lokasi:</strong> {{ $window['location'] }}</p>
      @endif
      @if($window['pickupInstructions'])
        <p>{{ $window['pickupInstructions'] }}</p>
      @endif
    </section>
    <section class="panel">
      <h2>Ringkasan pembayaran</h2>
      <div class="kv"><span>Jumlah grup kelas</span><strong>{{ $groups->count() }}</strong></div>
      <div class="kv"><span>Total</span><strong>{{ \App\Support\Money::format($total) }}</strong></div>
      <p class="muted">Satu order akan dibuat. Status pengambilan setiap kelas diperbarui secara terpisah.</p>
    </section>
  </div>

  <form method="post" action="{{ route('checkout') }}" class="panel">
    @csrf
    <input type="hidden" name="mode" value="{{ $mode }}">
    @if($mode === 'now')
      <input type="hidden" name="product_id" value="{{ $items->first()['product']->id }}">
      <input type="hidden" name="quantity" value="{{ $items->first()['quantity'] }}">
    @endif
    <h2>Data pengambil dan persetujuan</h2>
    <div class="two">
      <div class="field">
        <label for="pickup_contact_name">Nama pengambil</label>
        <input class="input" id="pickup_contact_name" name="pickup_contact_name" value="{{ old('pickup_contact_name', auth()->user()->name) }}" maxlength="100" autocomplete="name" required>
      </div>
      <div class="field">
        <label for="pickup_contact_phone">Nomor WhatsApp pengambil</label>
        <input class="input" id="pickup_contact_phone" name="pickup_contact_phone" type="tel" value="{{ old('pickup_contact_phone') }}" maxlength="30" autocomplete="tel" required>
      </div>
    </div>
    <label class="chk" for="accepted_terms">
      <input id="accepted_terms" name="accepted_terms" type="checkbox" value="1" @checked(old('accepted_terms')) required>
      <span>Saya telah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a> (versi {{ $termsVersion }}).</span>
    </label>
    @if($checkoutAvailable)
      <button class="btn" type="submit">Buat pesanan · {{ \App\Support\Money::format($total) }}</button>
    @else
      <button class="btn off" type="submit" disabled>Checkout belum tersedia</button>
    @endif
    <a class="btn sec" href="{{ $mode === 'now' ? route('products.show', $items->first()['product']) : route('cart.index') }}">Kembali</a>
  </form>
</div>
@endsection
