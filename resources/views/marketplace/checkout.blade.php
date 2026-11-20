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

  @php($paymentMethods = ['manual' => 'Manual (QRIS / Transfer Bank)'])
  @php($productCount = $items->sum(fn ($item) => $item['quantity']))

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
            <h2>Produk dalam pesanan</h2>
          </div>
          <span class="checkout-count">{{ $groups->count() }} grup kelas</span>
        </div>
        <p class="checkout-order-note">Produk dari beberapa kelas tetap menjadi satu pesanan buyer; pengambilan dikelola per grup kelas.</p>

        @foreach($groups as $groupItems)
          @php($firstProduct = $groupItems->first()['product'])
          @php($project = $firstProduct->project ?? $firstProduct->seller->project)
          <section class="checkout-group">
            <div class="checkout-group-heading">
              <div>
                <span class="eyebrow">GRUP PICKUP · KELAS</span>
                <h3>{{ $project?->class_name ?? $firstProduct->seller->store_name }}</h3>
              </div>
              @if($project)
                <a class="checkout-story" href="{{ route('projects.show', $project) }}">Cerita proyek <span aria-hidden="true">→</span></a>
              @endif
            </div>

            <div class="checkout-item-list">
              @foreach($groupItems as $item)
                @php($product = $item['product'])
                <article class="checkout-product">
                  <div class="checkout-product-name">
                    <strong>{{ $product->name }}</strong>
                  </div>
                  <div class="checkout-product-metric">
                    <span>Jumlah</span>
                    <strong>{{ $item['quantity'] }}</strong>
                  </div>
                  <div class="checkout-product-metric">
                    <span>Harga satuan</span>
                    <strong>{{ \App\Support\Money::format($product->price) }}</strong>
                  </div>
                  <div class="checkout-product-metric checkout-product-subtotal">
                    <span>Subtotal</span>
                    <strong>{{ \App\Support\Money::format($item['subtotal']) }}</strong>
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endforeach
      </section>

      <section class="panel checkout-contact">
        <div class="checkout-section-heading">
          <div>
            <span class="eyebrow">02 · PENGAMBILAN</span>
            <h2>Kontak pengambil</h2>
          </div>
        </div>

        @if($window['eventAt'] || $window['location'] || $window['pickupInstructions'])
          <div class="checkout-pickup-details">
            @if($window['eventAt'])
              <div>
                <span>Jadwal pengambilan</span>
                <strong>{{ $window['eventAt']->translatedFormat('d/m/Y H:i') }}</strong>
              </div>
            @endif
            @if($window['location'])
              <div>
                <span>Lokasi pengambilan</span>
                <strong>{{ $window['location'] }}</strong>
              </div>
            @endif
            @if($window['pickupInstructions'])
              <div class="checkout-pickup-instructions">
                <span>Instruksi pengambilan</span>
                <p>{{ $window['pickupInstructions'] }}</p>
              </div>
            @endif
          </div>
        @endif

        <div class="checkout-fields">
          <div class="field">
            <label for="pickup_contact_name">Nama pengambil</label>
            <input class="input" id="pickup_contact_name" name="pickup_contact_name" value="{{ old('pickup_contact_name', auth()->user()->name) }}" maxlength="100" autocomplete="name" required @error('pickup_contact_name') aria-invalid="true" aria-describedby="pickup_contact_name_error" @enderror>
            @error('pickup_contact_name')
              <p class="checkout-error" id="pickup_contact_name_error">{{ $message }}</p>
            @enderror
          </div>
          <div class="field">
            <label for="pickup_contact_phone">Nomor WhatsApp pengambil</label>
            <input class="input" id="pickup_contact_phone" name="pickup_contact_phone" type="tel" value="{{ old('pickup_contact_phone') }}" maxlength="30" autocomplete="tel" required @error('pickup_contact_phone') aria-invalid="true" aria-describedby="pickup_contact_phone_error" @enderror>
            @error('pickup_contact_phone')
              <p class="checkout-error" id="pickup_contact_phone_error">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </section>
    </div>

    <aside class="panel checkout-summary" aria-labelledby="checkout-summary-title">
      <div class="checkout-section-heading">
        <div>
          <span class="eyebrow">03 · PEMBAYARAN</span>
          <h2 id="checkout-summary-title">Ringkasan pembayaran</h2>
        </div>
      </div>

      <div class="checkout-summary-counts">
        <div><span>Grup kelas</span><strong>{{ $groups->count() }}</strong></div>
        <div><span>Jumlah produk</span><strong>{{ $productCount }}</strong></div>
        <div><span>Metode pembayaran</span><strong>Manual (QRIS / Transfer Bank)</strong></div>
      </div>

      <div class="checkout-totals">
        <div class="checkout-total-row">
          <span>Subtotal</span>
          <strong>{{ \App\Support\Money::format($total) }}</strong>
        </div>
        <div class="checkout-total-row checkout-grand-total">
          <span>Total pembayaran</span>
          <strong>{{ \App\Support\Money::format($total) }}</strong>
        </div>
      </div>
      <p class="checkout-summary-note">Total mencakup subtotal seluruh produk dalam satu pesanan buyer.</p>

      @if($manualPaymentConfigured)
        <p class="muted checkout-payment-note">Setelah pesanan dibuat, bayar dengan QRIS statis atau transfer bank, lalu unggah bukti pembayaran untuk diverifikasi admin.</p>
      @else
        <p class="note warn checkout-payment-note">Pesanan tetap akan disimpan dengan status menunggu pembayaran. Instruksi QRIS atau transfer belum dikonfigurasi; jangan lakukan pembayaran sampai instruksi resmi tersedia.</p>
      @endif

      @if($window['paymentDeadline'])
        <p class="checkout-deadline"><strong>Tenggat pembayaran:</strong> {{ $window['paymentDeadline']->translatedFormat('d/m/Y H:i') }}</p>
      @endif

      <label class="chk checkout-terms" for="accepted_terms">
        <input id="accepted_terms" name="accepted_terms" type="checkbox" value="1" @checked(old('accepted_terms')) required @error('accepted_terms') aria-invalid="true" aria-describedby="accepted_terms_error" @enderror>
        <span>Saya telah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a>.</span>
      </label>
      @error('accepted_terms')
        <p class="checkout-error checkout-terms-error" id="accepted_terms_error">{{ $message }}</p>
      @enderror

      <div class="checkout-actions">
        @if($checkoutAvailable)
          <button class="btn checkout-submit" id="checkout-submit" type="submit" aria-live="polite">Buat Pesanan &amp; Lanjutkan Pembayaran</button>
        @else
          <button class="btn checkout-submit off" id="checkout-submit" type="submit" disabled>Checkout belum tersedia</button>
        @endif
        <a class="btn sec checkout-back" href="{{ $mode === 'now' ? route('products.show', $items->first()['product']) : route('cart.index') }}">Kembali</a>
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
