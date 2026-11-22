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

  @if(isset($activePendingOrder) && $activePendingOrder)
    <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
      <div>
        <strong style="color: #f59e0b; display: block; font-size: 14px;">⚠️ Anda sudah memiliki pesanan yang menunggu pembayaran ({{ $activePendingOrder->order_code }})</strong>
        <span class="muted" style="font-size: 12.5px;">Batas waktu pembayaran masih aktif. Anda dapat langsung melanjutkan pembayaran pesanan ini.</span>
      </div>
      <a href="{{ route('orders.show', $activePendingOrder) }}" class="btn" style="font-size: 13px; padding: 7px 16px;">Lanjutkan Pembayaran →</a>
    </div>
  @endif

  @php
    $paymentMethods = ['manual' => 'Manual (QRIS / Transfer Bank)'];
    $productCount = $items->sum(fn ($item) => $item['quantity']);
  @endphp

  <form method="post" action="{{ route('checkout') }}" class="checkout-layout" id="checkout-form">
    @csrf
    <input type="hidden" name="mode" value="{{ $mode }}">
    <input type="hidden" name="terms_version_id" value="{{ $termsVersionId }}">
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
          <div>
            <span class="eyebrow" style="font-size: 11px; margin-bottom: 2px; display: block;">02 · PENGAMBILAN</span>
            <h2 style="font-size: 14px; margin: 0;">Kontak pengambil</h2>
          </div>
          <div style="text-align: right;">
            <strong>{{ auth()->user()->name }}</strong>
            @if(auth()->user()->phone)
              <span class="muted">({{ auth()->user()->phone }})</span>
            @endif
          </div>
        </div>

        @if($window['location'])
          <div style="margin-top: 10px; padding: 10px 14px; background: var(--soft); border-radius: 8px; font-size: 13px;">
            <span class="muted">Lokasi pengambilan:</span>
            <strong>{{ $window['location'] }}</strong>
          </div>
        @endif
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
          <strong style="color: var(--ink);">Manual (QRIS / Transfer Bank)</strong>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--line);">
          <span style="font-weight: 700; color: var(--ink);">Total Tagihan</span>
          <strong style="color: var(--good); font-size: 1.3rem; font-family: var(--fd);">{{ \App\Support\Money::format($total) }}</strong>
        </div>
      </div>

      @if($manualPaymentConfigured)
        <p class="muted checkout-payment-note" style="font-size: 12px; margin: 4px 0 10px;">Setelah pesanan dibuat, bayar dengan QRIS statis atau transfer bank, lalu unggah bukti pembayaran untuk diverifikasi admin.</p>
      @else
        <p class="note warn checkout-payment-note" style="font-size: 12px; margin: 4px 0 10px;">Pesanan tetap akan disimpan dengan status menunggu pembayaran. Instruksi QRIS atau transfer belum dikonfigurasi; jangan lakukan pembayaran sampai instruksi resmi tersedia.</p>
      @endif

      <label class="chk checkout-terms" for="accepted_terms" style="margin-top: 8px;">
        <input id="accepted_terms" name="accepted_terms" type="checkbox" value="1" @checked(old('accepted_terms', true)) required @error('accepted_terms') aria-invalid="true" aria-describedby="accepted_terms_error" @enderror>
        <span>Saya menyetujui <a href="{{ route('terms') }}" target="_blank" rel="noopener">Syarat &amp; ketentuan</a> pemesanan (versi {{ $termsVersion }}).</span>
      </label>
      @error('accepted_terms')
        <p class="checkout-error checkout-terms-error" id="accepted_terms_error">{{ $message }}</p>
      @enderror

      <div class="checkout-actions" style="margin-top: 18px; display: flex; flex-direction: column; gap: 8px;">
        @if($checkoutAvailable)
          @if(isset($activePendingOrder) && $activePendingOrder && $mode === 'now' && $activePendingOrder->items()->where('product_id', $items->first()['product']->id)->exists())
            <a class="btn checkout-submit" href="{{ route('orders.show', $activePendingOrder) }}" style="width: 100%; text-align: center; text-decoration: none;">Lanjutkan Pembayaran ({{ $activePendingOrder->order_code }})</a>
          @else
            <button class="btn checkout-submit" id="checkout-submit" type="submit" aria-live="polite" style="width: 100%;">Buat Pesanan &amp; Lanjutkan Pembayaran</button>
          @endif
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

      function resetSubmitButton() {
        if (!checkoutSubmit) return;
        if (checkoutForm) {
          checkoutForm.dataset.submitting = 'false';
        }
        checkoutSubmit.disabled = false;
        checkoutSubmit.removeAttribute('aria-busy');
        if (checkoutSubmit.tagName && checkoutSubmit.tagName.toLowerCase() === 'button') {
          checkoutSubmit.textContent = 'Buat Pesanan & Lanjutkan Pembayaran';
        }
      }

      // Pastikan tombol aktif kembali saat navigasi browser Back / Forward (bfcache)
      window.addEventListener('pageshow', resetSubmitButton);

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
        if (checkoutSubmit.tagName && checkoutSubmit.tagName.toLowerCase() === 'button') {
          checkoutSubmit.textContent = 'Memproses pesanan...';
        }
      });
    })();
  </script>
@endpush
