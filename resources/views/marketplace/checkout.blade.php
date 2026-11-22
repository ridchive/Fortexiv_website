@extends('layouts.app')

@section('title', 'Checkout — FORTEXIV')

@section('content')
<div class="wrap checkout-page">
  <nav class="crumb checkout-crumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Beranda</a><span aria-hidden="true">/</span><a href="{{ route('cart.index') }}">Keranjang</a><span aria-hidden="true">/</span><span aria-current="page">Checkout</span></nav>

  <header class="pgh checkout-heading">
    <h1>Konfirmasi Pesanan</h1>
    <p>{{ $mode === 'now' ? 'Beli Sekarang hanya memproses produk yang kamu pilih.' : 'Semua produk keranjang dikonfirmasi sebagai satu pesanan, dengan pengambilan terpisah per grup kelas.' }}</p>
  </header>

  <div class="checkout-po">
    @include('marketplace.partials.po-notice', ['window' => $window])
  </div>

  @php
    $paymentMethods = ['manual' => 'Manual (QRIS / Transfer Bank)'];
    $productCount = $items->sum(fn ($item) => $item['quantity']);
  @endphp

  <form method="post" action="{{ route('checkout') }}" class="checkout-layout" id="checkout-form">
    @csrf
    <input type="hidden" name="mode" value="{{ $mode }}">
    @if($mode === 'now')
      <input type="hidden" name="product_id" value="{{ $items->first()['product']->id }}">
      <input type="hidden" name="quantity" value="{{ $items->first()['quantity'] }}">
    @endif
    @if(count($paymentMethods) <= 1)
      <input type="hidden" name="payment_method" value="{{ array_key_first($paymentMethods) ?? 'manual' }}">
    @endif

    <div class="checkout-main">
      <section class="panel checkout-products">
        <div class="checkout-section-heading">
          <div>
            <span class="eyebrow">01 · PRODUK</span>
            <h2>Produk yang Dipesan</h2>
          </div>
          <span class="checkout-count">{{ $productCount }} barang</span>
        </div>

        <div class="checkout-compact-list" style="margin-top: 14px;">
          @foreach($items as $item)
            @php
              $product = $item['product'];
              $className = $product->project?->class_name ?? $product->seller?->project?->class_name ?? $product->seller?->store_name ?? 'Kelas';
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line);">
              <div style="min-width: 0;">
                <strong style="font-size: 14.5px; display: block; overflow-wrap: anywhere;">{{ $product->name }}</strong>
                <div class="muted" style="font-size: 12.5px; margin-top: 2px;">
                  {{ $item['quantity'] }} × {{ \App\Support\Money::format($product->price) }} &middot; <span style="color: var(--good); font-weight: 600;">{{ $className }}</span>
                </div>
              </div>
              <div style="text-align: right; white-space: nowrap;">
                <strong style="font-size: 14.5px;">{{ \App\Support\Money::format($item['subtotal']) }}</strong>
              </div>
            </div>
          @endforeach
        </div>

        <div style="margin-top: 16px; padding: 12px 14px; background: var(--soft); border-radius: 8px; font-size: 13px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
          <span class="muted">Nama &amp; Kontak Pemesan:</span>
          <strong>{{ auth()->user()->name }} @if(auth()->user()->phone) ({{ auth()->user()->phone }}) @endif</strong>
        </div>
      </section>
    </div>

    <aside class="panel checkout-summary" aria-labelledby="checkout-summary-title">
      <div class="checkout-section-heading">
        <div>
          <span class="eyebrow">02 · PEMBAYARAN</span>
          <h2 id="checkout-summary-title">Ringkasan Pembayaran</h2>
        </div>
      </div>

      <div style="margin: 16px 0; padding: 14px; background: var(--soft); border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--muted); margin-bottom: 8px;">
          <span>Metode Pembayaran</span>
          <strong style="color: var(--ink);">QRIS / Transfer Bank</strong>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--line);">
          <span style="font-weight: 700; color: var(--ink);">Total Tagihan</span>
          <strong style="color: var(--good); font-size: 1.3rem; font-family: var(--fd);">{{ \App\Support\Money::format($total) }}</strong>
        </div>
      </div>

      <label class="chk checkout-terms" for="accepted_terms" style="margin-top: 8px;">
        <input id="accepted_terms" name="accepted_terms" type="checkbox" value="1" @checked(old('accepted_terms', true)) required @error('accepted_terms') aria-invalid="true" aria-describedby="accepted_terms_error" @enderror>
        <span>Saya menyetujui <a href="{{ route('terms') }}" target="_blank" rel="noopener">Syarat &amp; Ketentuan</a> pemesanan.</span>
      </label>
      @error('accepted_terms')
        <p class="checkout-error checkout-terms-error" id="accepted_terms_error">{{ $message }}</p>
      @enderror

      <div class="checkout-actions" style="margin-top: 18px; display: flex; flex-direction: column; gap: 8px;">
        @if($checkoutAvailable)
          <button class="btn checkout-submit" id="checkout-submit" type="submit" aria-live="polite" style="width: 100%;">Buat Pesanan &amp; Lanjutkan Pembayaran</button>
        @else
          <button class="btn checkout-submit off" id="checkout-submit" type="submit" disabled style="width: 100%;">Checkout belum tersedia</button>
        @endif
        <a class="btn sec checkout-back" href="{{ $mode === 'now' ? route('products.show', $items->first()['product']) : route('cart.index') }}" style="width: 100%; text-align: center;">Kembali</a>
      </div>
    </aside>
  </form>
</div>
@endsection

@push('scripts')
  <script>
    (() => {
      const checkoutForm = document.getElementById('checkout-form');
      const checkoutSubmit = document.getElementById('checkout-submit');

      if (!checkoutForm || !checkoutSubmit) {
        return;
      }

      checkoutForm.addEventListener('submit', (event) => {
        if (checkoutForm.dataset.submitting === 'true') {
          event.preventDefault();
          return;
        }

        checkoutForm.dataset.submitting = 'true';
        checkoutSubmit.disabled = true;
        checkoutSubmit.setAttribute('aria-busy', 'true');
        checkoutSubmit.textContent = 'Memproses pesanan...';
      });
    })();
  </script>
@endpush
