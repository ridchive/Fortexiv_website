@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_code.' — FORTEXIV')

@section('content')
<style>
  .order-detail { max-width: 1120px; }
  .order-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin: 20px 0 28px; }
  .order-header h1 { margin-top: 4px; font-size: clamp(1.75rem, 4vw, 2.6rem); overflow-wrap: anywhere; }
  .order-created { margin: 8px 0 0; color: var(--muted); font-size: 14px; }
  .order-header > .badge { margin-top: 8px; padding: 7px 12px; font-size: 12px; white-space: nowrap; }
  .order-layout { display: grid; grid-template-columns: minmax(0, 1.85fr) minmax(280px, 1fr); align-items: start; gap: 24px; }
  .order-main, .order-side { display: grid; min-width: 0; gap: 22px; }
  .order-section-heading { display: flex; justify-content: space-between; align-items: end; gap: 12px; margin-bottom: 12px; }
  .order-section-heading h2, .order-summary h2, .order-ticket h2 { margin-top: 3px; font-size: clamp(1.3rem, 2.5vw, 1.65rem); }
  .order-section-heading > span { color: var(--muted); font-size: 13px; white-space: nowrap; }
  .order-groups { display: grid; gap: 12px; }
  .order-group { min-width: 0; padding: clamp(16px, 2.5vw, 22px); border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); }
  .order-group-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
  .order-group-header > div { min-width: 0; }
  .order-group-header h3 { margin-top: 5px; overflow-wrap: anywhere; }
  .order-group-header > .badge { flex: 0 0 auto; margin-top: 2px; }
  .order-seller { display: block; margin-top: 5px; color: var(--muted); font-size: 13px; }
  .order-story-link { display: inline-flex; margin-top: 8px; color: var(--good); font-size: 13px; font-weight: 700; text-decoration-thickness: 1px; text-underline-offset: 3px; }
  .order-state--problem { background: var(--bad-bg); color: var(--bad); }
  .order-state--resolution { background: var(--info-bg); color: var(--info); }
  .order-state--final { background: var(--good-bg); color: var(--good); }
  .order-items { margin: 16px 0 0; padding: 0; border-top: 1px solid var(--line); list-style: none; }
  .order-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line); }
  .order-item:last-child { border-bottom: 0; }
  .order-item-name { display: block; font-size: 14px; }
  .order-item-meta, .order-item-money span { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; }
  .order-item-money { text-align: right; white-space: nowrap; }
  .order-item-money strong { font-size: 14px; }
  .order-group-total { display: flex; justify-content: space-between; gap: 12px; padding-top: 12px; border-top: 1px solid var(--line); font-size: 13px; }
  .order-group-total span { color: var(--muted); }
  .order-handover-time { margin: 10px 0 0; color: var(--muted); font-size: 12px; }
  .order-summary, .order-pickup { margin: 0; padding: clamp(18px, 2.5vw, 22px); }
  .order-summary h2, .order-pickup h2 { margin-bottom: 12px; }
  .order-summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 11px 0; border-bottom: 1px solid var(--line); font-size: 13px; }
  .order-summary-row > span { color: var(--muted); }
  .order-summary-row > strong, .order-summary-row > .badge { text-align: right; }
  .order-summary-row.order-total { align-items: center; padding-top: 14px; }
  .order-summary-row.order-total strong { color: var(--ink); font-size: 18px; }
  .order-summary-row:last-of-type { border-bottom: 0; }
  .order-summary-note { margin: 12px 0 0; color: var(--muted); font-size: 12px; }
  .order-payment-actions { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); }
  .order-payment-actions h3 { margin-bottom: 8px; font-size: 1rem; }
  .order-payment-actions p { font-size: 13px; }
  .order-payment-actions .btn { margin: 4px 0; }
  .order-payment-feedback { margin: 12px 0 0; padding: 11px 13px; border-radius: 8px; font-size: 13px; line-height: 1.5; }
  .order-payment-feedback--pending { background: var(--info-bg); color: var(--info); }
  .order-payment-feedback--success { background: var(--good-bg); color: var(--good); }
  .order-payment-feedback--problem { background: var(--bad-bg); color: var(--bad); }
  .order-payment-feedback--refund { background: var(--info-bg); color: var(--info); }
  .order-payment-actions .field { margin-top: 12px; }
  .order-transfer-details { padding: 12px; border-radius: 8px; background: var(--soft); line-height: 1.7; }
  .order-qris-image { display: block; width: min(100%, 360px); margin: 12px auto; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
  .order-payment-amount { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 12px 0; }
  .order-copy-feedback { min-height: 20px; color: var(--muted); font-size: 12px; }
  .order-pickup-details { display: grid; gap: 14px; }
  .order-pickup-detail { display: grid; grid-template-columns: 22px minmax(0, 1fr); align-items: start; gap: 10px; }
  .order-pickup-detail svg { margin-top: 2px; color: var(--good); }
  .order-pickup-detail span, .order-pickup-detail strong { display: block; }
  .order-pickup-detail span { color: var(--muted); font-size: 12px; }
  .order-pickup-detail strong { margin-top: 2px; font-size: 14px; overflow-wrap: anywhere; }
  .order-pickup-instructions { margin: 14px 0 0; padding-top: 12px; border-top: 1px solid var(--line); color: var(--muted); font-size: 13px; }
  .order-ticket { margin: 0; padding: clamp(18px, 2.5vw, 22px); border: 1px solid var(--good); border-radius: var(--radius); background: var(--good-bg); }
  .order-ticket .badge { margin-bottom: 10px; }
  .order-ticket p { margin: 10px 0 16px; color: var(--muted); font-size: 13px; }
  .order-ticket .btn { width: 100%; min-height: 44px; font-size: 14px; }
  .order-detail .badge.order-state--problem { background: var(--bad-bg); color: var(--bad); }
  .order-detail .badge.order-state--resolution { background: var(--info-bg); color: var(--info); }
  .order-detail .badge.order-state--final { background: var(--good-bg); color: var(--good); }
  .order-detail .badge.good { background: var(--good-bg); color: var(--good); }
  .order-detail .badge.order-payment--problem { background: var(--bad-bg); color: var(--bad); }
  @media (max-width: 900px) {
    .order-layout { grid-template-columns: minmax(0, 1fr); gap: 20px; }
    .order-side { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
    .order-summary { grid-row: span 2; }
  }
  @media (max-width: 620px) {
    .order-header { flex-direction: column; gap: 8px; margin: 16px 0 22px; }
    .order-header > .badge { margin-top: 0; white-space: normal; }
    .order-side { grid-template-columns: minmax(0, 1fr); }
    .order-summary { grid-row: auto; }
    .order-group-header { flex-direction: column; gap: 8px; }
    .order-group-header > .badge { align-self: flex-start; }
    .order-item { grid-template-columns: minmax(0, 1fr); gap: 4px; }
    .order-item-money { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; text-align: left; }
    .order-item-money span { margin-top: 0; text-align: right; white-space: normal; }
  }
</style>

@php
  $orderStatusLabels = [
      'pending_payment' => 'Perlu pembayaran',
      'waiting_verification' => 'Menunggu konfirmasi',
      'paid' => 'Aktif untuk pickup',
      'partially_picked_up' => 'Sebagian selesai',
      'picked_up' => 'Selesai',
      'in_resolution' => 'Dalam resolusi',
      'cancelled' => 'Dibatalkan',
  ];
  $orderStatusClasses = [
      'pending_payment' => 'pending_payment',
      'waiting_verification' => 'waiting_verification',
      'paid' => 'paid',
      'partially_picked_up' => 'partially_delivered',
      'picked_up' => 'picked_up',
      'in_resolution' => 'order-state--resolution',
      'cancelled' => 'cancelled',
  ];
  $fulfillmentStatusLabels = [
      'pending' => 'Menunggu pemenuhan',
      'ready_for_pickup' => 'Siap pickup',
      'partially_delivered' => 'Sebagian diserahkan',
      'delivered' => 'Diserahkan',
      'problem' => 'Bermasalah',
      'in_resolution' => 'Dalam resolusi',
      'final_via_resolution' => 'Final melalui resolusi',
  ];
  $fulfillmentStatusClasses = [
      'pending' => 'pending',
      'ready_for_pickup' => '',
      'partially_delivered' => 'partially_delivered',
      'delivered' => 'delivered',
      'problem' => 'order-state--problem',
      'in_resolution' => 'order-state--resolution',
      'final_via_resolution' => 'order-state--final',
  ];
  $paymentStatusLabel = match ($order->payment?->status) {
      'verified' => 'Dibayar',
      'pending' => $order->status === 'waiting_verification' ? 'Menunggu konfirmasi' : 'Menunggu Pembayaran',
      'rejected' => 'Pembayaran ditolak',
      'failed' => 'Pembayaran gagal',
      'expired' => 'Pembayaran kedaluwarsa',
      'cancelled' => 'Pembayaran dibatalkan',
      'refunded' => 'Dana dikembalikan',
      'partially_refunded' => 'Dana dikembalikan sebagian',
      default => match ($order->status) {
          'pending_payment' => 'Menunggu pembayaran',
          'waiting_verification' => 'Menunggu konfirmasi',
          default => 'Belum tersedia',
      },
  };
  $paymentStatusClass = match ($order->payment?->status) {
      'verified' => 'verified',
      'pending' => 'pending',
      'rejected', 'failed', 'expired', 'cancelled' => 'order-payment--problem',
      'refunded', 'partially_refunded' => 'order-state--resolution',
      default => '',
  };
  $qrisImageAvailable = filled($qrisImage) && is_file(public_path($qrisImage));
  $ticketAvailable = in_array($order->payment?->status, ['verified', 'partially_refunded'], true)
      && $order->ticket !== null
      && in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true);
  $midtransPending = $order->payment?->gateway === \App\Models\Payment::GATEWAY_MIDTRANS
      && $order->payment?->status === 'pending'
      && $order->status === 'pending_payment';
  $paymentFeedback = match ($order->payment?->status) {
      'pending' => 'Memverifikasi pembayaran dengan Midtrans. Status akan diperbarui otomatis.',
      'verified' => 'Pembayaran berhasil. Pesanan siap diproses untuk pickup.',
      'failed', 'rejected' => 'Pembayaran gagal. Pesanan belum dapat diproses untuk pickup.',
      'expired' => 'Pembayaran kedaluwarsa. Pesanan belum dibayar.',
      'cancelled' => 'Pembayaran dibatalkan.',
      'refunded' => 'Dana pembayaran telah dikembalikan. QR pickup tidak tersedia.',
      'partially_refunded' => 'Dana dikembalikan sebagian. Hubungi tim FORTEXIV untuk memastikan item pickup yang terdampak.',
      default => null,
  };
  $paymentFeedbackClass = match ($order->payment?->status) {
      'pending' => 'pending',
      'verified' => 'success',
      'failed', 'rejected', 'expired', 'cancelled' => 'problem',
      'refunded', 'partially_refunded' => 'refund',
      default => 'pending',
  };
@endphp

<div class="wrap order-detail">
  <nav class="crumb" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a> /
    <a href="{{ route('orders.index') }}">Pesanan saya</a> /
    <span>{{ $order->order_code }}</span>
  </nav>

  <header class="order-header">
    <div>
      <span class="eyebrow">DETAIL PESANAN</span>
      <h1>{{ $order->order_code }}</h1>
      <p class="order-created">Dibuat {{ $order->created_at->format('d/m/Y · H:i') }}</p>
    </div>
    <span class="badge {{ $orderStatusClasses[$order->status] ?? '' }}" data-order-status-badge>
      {{ $orderStatusLabels[$order->status] ?? $order->status }}
    </span>
  </header>

  <div class="order-layout">
    <div class="order-main">
      <section class="order-section" aria-labelledby="fulfillment-heading">
        <div class="order-section-heading">
          <div>
            <span class="eyebrow">PICKUP PER KELAS</span>
            <h2 id="fulfillment-heading">Status grup kelas</h2>
          </div>
          <span>{{ $order->fulfillments->count() }} grup kelas</span>
        </div>

        <div class="order-groups">
          @forelse($order->fulfillments as $fulfillment)
            @php
              $groupQuantity = $fulfillment->items->sum('quantity');
              $groupSubtotal = $fulfillment->items->sum('subtotal');
          @endphp
            <article class="order-group">
              <header class="order-group-header">
                <div>
                  <span class="eyebrow">GRUP KELAS</span>
                  <h3>{{ $fulfillment->class_name ?? $fulfillment->project?->class_name ?? $fulfillment->seller->store_name }}</h3>
                  <span class="order-seller">{{ $fulfillment->seller->store_name }}</span>
                  @if($fulfillment->project?->is_published)
                    <a class="order-story-link" href="{{ route('projects.show', $fulfillment->project) }}">Lihat cerita proyek</a>
                  @endif
                </div>
                <span class="badge {{ $fulfillmentStatusClasses[$fulfillment->status] ?? '' }}">
                  {{ $fulfillmentStatusLabels[$fulfillment->status] ?? $fulfillment->status }}
                </span>
              </header>

              <ul class="order-items">
                @foreach($fulfillment->items as $item)
                  @php
                    $deliveredCount = $item->units->where('status', 'delivered')->count();
                    $itemStatus = $deliveredCount === $item->quantity
                        ? 'Diserahkan'
                        : ($deliveredCount > 0 ? 'Sebagian diserahkan' : 'Menunggu pickup');
                  @endphp
                  <li class="order-item">
                    <div>
                      <strong class="order-item-name">{{ $item->product_name }} × {{ $item->quantity }}</strong>
                      @if($item->carbon_contribution !== null)
                        <span class="order-item-meta">Kontribusi CEA {{ number_format((float) $item->carbon_contribution, 6, ',', '.') }} kg CO2e</span>
                      @elseif($order->payment?->status !== 'verified')
                        <span class="order-item-meta">Kontribusi menunggu verifikasi pembayaran</span>
                      @elseif(!in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true))
                        <span class="order-item-meta">Kontribusi belum dapat dihitung untuk pesanan ini</span>
                      @elseif($item->carbon_baseline_emission_factor_at_purchase === null || $item->carbon_footprint_value_at_purchase === null)
                        <span class="order-item-meta">Data faktor emisi belum lengkap</span>
                      @else
                        <span class="order-item-meta">Kontribusi belum tercatat</span>
                      @endif
                    </div>
                    <div class="order-item-money">
                      <strong>{{ \App\Support\Money::format($item->subtotal) }}</strong>
                      <span>{{ $itemStatus }}</span>
                    </div>
                  </li>
                @endforeach
              </ul>

              <div class="order-group-total">
                <span>{{ $groupQuantity }} produk dalam grup ini</span>
                <strong>{{ \App\Support\Money::format($groupSubtotal) }}</strong>
              </div>
              @if($fulfillment->handed_over_at)
                <p class="order-handover-time">Diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
              @endif
            </article>
          @empty
            <div class="empty"><p>Informasi grup pickup belum tersedia untuk pesanan ini.</p></div>
          @endforelse
        </div>
      </section>

      <section class="panel order-pickup" aria-labelledby="pickup-heading">
        <span class="eyebrow">HARI PENGAMBILAN</span>
        <h2 id="pickup-heading">Informasi pengambilan</h2>
        <div class="order-pickup-details">
          <div class="order-pickup-detail">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <rect x="3" y="5" width="18" height="16" rx="2"></rect>
              <path d="M16 3v4M8 3v4M3 10h18"></path>
            </svg>
            <div>
              <span>Tanggal dan jam pickup</span>
              <strong>{{ $order->pickup_date->format('d/m/Y') }}</strong>
              @if($pickupTime)
                <span>Pukul {{ $pickupTime }}</span>
              @endif
            </div>
          </div>
          <div class="order-pickup-detail">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
              <circle cx="12" cy="10" r="2.5"></circle>
            </svg>
            <div>
              <span>Lokasi pengambilan</span>
              <strong>{{ $pickupLocation ?: 'Informasi lokasi belum tersedia.' }}</strong>
            </div>
          </div>
        </div>
        @if($pickupInstructions)
          <p class="order-pickup-instructions">{{ $pickupInstructions }}</p>
        @endif
      </section>
    </div>

    <aside class="order-side" aria-label="Ringkasan dan tiket pickup">
      <section class="panel order-summary" aria-labelledby="summary-heading">
        <span class="eyebrow">RINGKASAN</span>
        <h2 id="summary-heading">Pesanan Anda</h2>
        <div class="order-summary-row">
          <span>Jumlah grup kelas</span>
          <strong>{{ $order->fulfillments->count() }} grup</strong>
        </div>
        <div class="order-summary-row order-total">
          <span>Total pembayaran</span>
          <strong>{{ \App\Support\Money::format($order->total_price) }}</strong>
        </div>
        <div class="order-summary-row">
          <span>Status pembayaran</span>
          <span class="badge {{ $paymentStatusClass }}" data-payment-status-badge>{{ $paymentStatusLabel }}</span>
        </div>
        @if(($order->payment?->gateway === \App\Models\Payment::GATEWAY_MIDTRANS || $order->payment?->status === 'verified') && $paymentFeedback)
          <p class="order-payment-feedback order-payment-feedback--{{ $paymentFeedbackClass }}" data-payment-feedback role="status" aria-live="polite">{{ $paymentFeedback }}</p>
        @endif
        @if($midtransPending)
          <div data-midtrans-payment-monitor data-status-url="{{ route('payments.midtrans.status', $order) }}" hidden></div>
        @endif
        <div class="order-summary-row">
          <span>Nama pengambil</span>
          <strong>{{ $order->pickup_contact_name ?? $order->buyer->name }}</strong>
        </div>
        <div class="order-summary-row">
          <span>WhatsApp pengambil</span>
          <strong dir="ltr">{{ $order->pickup_contact_phone ?: 'Belum tersedia' }}</strong>
        </div>
        @if($order->payment?->notes && $order->payment?->status !== 'rejected')
          <p class="order-summary-note">Catatan admin: {{ $order->payment->notes }}</p>
        @endif
        @if($order->terms_accepted_at)
          <p class="order-summary-note">Ketentuan pesanan disetujui {{ $order->terms_accepted_at->format('d/m/Y H:i') }}.</p>
        @endif

        @if($order->status === 'pending_payment')
          <div class="order-payment-actions" data-payment-actions>
            <h3>Selesaikan pembayaran</h3>
            @if($order->payment?->gateway === \App\Models\Payment::GATEWAY_MIDTRANS)
              <p class="muted">Selesaikan pembayaran di halaman Midtrans. Setelah kembali, status akan diperbarui otomatis.</p>
              @if($order->payment->snap_redirect_url)
                <a class="btn" href="{{ $order->payment->snap_redirect_url }}">Lanjutkan pembayaran</a>
              @elseif($order->payment->status === 'pending')
                <p class="muted">Halaman pembayaran belum dapat disiapkan. Silakan coba lagi.</p>
                <form method="post" action="{{ route('payments.midtrans.retry', $order) }}">
                  @csrf
                  <button class="btn" type="submit">Coba siapkan pembayaran lagi</button>
                </form>
                <p class="note warn">Jika Anda sudah menyelesaikan pembayaran, hubungi admin sebelum mencoba lagi.</p>
              @else
                <p class="muted">Pembayaran belum dapat dilanjutkan. Hubungi admin untuk memeriksa transaksi.</p>
              @endif
            @else
              @if($order->payment?->status === 'rejected')
                <p class="order-payment-feedback order-payment-feedback--problem" role="alert">
                  Pembayaran ditolak. Alasan: {{ $order->payment->notes ?: 'Admin belum memberikan alasan.' }}
                </p>
              @endif
              @if($qrisImageAvailable)
                <img class="order-qris-image" src="{{ asset($qrisImage) }}" alt="QRIS statis resmi FORTEXIV">
              @endif
              <div class="order-payment-amount">
                <strong>{{ \App\Support\Money::format($order->total_price) }}</strong>
                <button class="btn sec" type="button" data-copy-payment-amount="{{ $order->total_price }}">Salin Nominal</button>
              </div>
              <p class="order-copy-feedback" data-copy-payment-feedback aria-live="polite"></p>
              @if($qrisImageAvailable)
                <p>Scan QRIS di atas, masukkan nominal yang sesuai, lalu unggah screenshot bukti pembayaran di bawah ini.</p>
              @elseif(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
                <p>Transfer sesuai total pesanan, lalu unggah bukti transfer JPG, PNG, atau PDF (maksimum 5 MB).</p>
              @else
                <p class="note warn">Poster QRIS statis FORTEXIV atau informasi transfer belum dikonfigurasi. Jangan lakukan pembayaran sampai instruksi resmi tersedia.</p>
              @endif
              @if(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
                <p class="order-transfer-details">
                  <strong>{{ config('marketplace.bank_name') }}</strong><br>
                  Nama rekening: {{ config('marketplace.account_name') }}<br>
                  Nomor rekening: {{ config('marketplace.account_number') }}
                </p>
              @endif
              @if($manualPaymentConfigured)
                <form method="post" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data">
                  @csrf
                  <div class="field">
                    <label for="method">Metode pembayaran</label>
                    <input class="input" id="method" name="method" value="{{ old('method', $qrisImageAvailable ? 'QRIS' : 'Transfer Bank') }}" maxlength="50" required>
                    @error('method')
                      <p class="checkout-error">{{ $message }}</p>
                    @enderror
                  </div>
                  <div class="field">
                    <label for="proof">Bukti pembayaran (JPG, PNG, atau PDF; maksimum 5 MB)</label>
                    <input class="input" id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                    @error('proof')
                      <p class="checkout-error">{{ $message }}</p>
                    @enderror
                  </div>
                  <button class="btn" type="submit">{{ $order->payment?->status === 'rejected' ? 'Unggah Ulang Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}</button>
                </form>
              @endif
            @endif
          </div>
        @elseif($order->status === 'waiting_verification' && $order->payment?->status === 'pending')
          <p class="order-summary-note">Pembayaran sedang diperiksa oleh admin. QR pickup akan aktif setelah pembayaran diverifikasi.</p>
        @endif
      </section>

      <section class="order-ticket" aria-labelledby="ticket-heading">
        <span class="badge {{ $ticketAvailable ? 'good' : 'pending' }}" data-ticket-availability-badge>
          {{ $ticketAvailable ? 'Tiket tersedia' : 'Tiket belum tersedia' }}
        </span>
        <h2 id="ticket-heading">Tiket & QR pickup</h2>
        <div data-ticket-pending @if($ticketAvailable) hidden @endif>
          <p data-ticket-pending-message>
            @if(in_array($order->payment?->status, ['pending', null], true))
              QR pickup akan tersedia setelah pembayaran terverifikasi.
            @else
              QR pickup belum tersedia karena status pembayaran atau pesanan saat ini.
            @endif
          </p>
        </div>
        <div data-ticket-ready @unless($ticketAvailable) hidden @endunless>
          <p>Tampilkan QR kepada petugas kelas saat mengambil produk. Penyerahan tetap dikonfirmasi untuk setiap grup kelas.</p>
          <a class="btn lime" href="{{ route('tickets.show', $order) }}" data-ticket-link>Buka Tiket & Tampilkan QR</a>
        </div>
      </section>
    </aside>
  </div>
</div>
@endsection

@if($order->status === 'pending_payment' && $order->payment?->gateway !== \App\Models\Payment::GATEWAY_MIDTRANS)
  @push('scripts')
    <script>
      (() => {
        const copyButton = document.querySelector('[data-copy-payment-amount]');
        const feedback = document.querySelector('[data-copy-payment-feedback]');

        if (!copyButton || !feedback) {
          return;
        }

        copyButton.addEventListener('click', () => {
          if (!navigator.clipboard?.writeText) {
            feedback.textContent = 'Browser tidak mendukung salin otomatis. Silakan salin nominal secara manual.';
            return;
          }

          navigator.clipboard.writeText(copyButton.dataset.copyPaymentAmount)
            .then(() => {
              feedback.textContent = 'Nominal berhasil disalin.';
            })
            .catch(() => {
              feedback.textContent = 'Nominal gagal disalin. Silakan salin secara manual.';
            });
        });
      })();
    </script>
  @endpush
@endif
