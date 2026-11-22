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
  .order-ticket { margin: 0; padding: clamp(18px, 2.5vw, 22px); border: 1px solid var(--good); border-radius: var(--radius); background: var(--good-bg); }
  .order-ticket .badge { margin-bottom: 10px; }
  .order-ticket p { margin: 10px 0 16px; color: var(--muted); font-size: 13px; }
  .order-ticket .btn { width: 100%; min-height: 44px; font-size: 14px; }
  .modal-backdrop-custom {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .modal-card-custom {
    background: var(--surface);
    color: var(--ink);
    border: 1px solid var(--line);
    border-radius: 14px;
    width: min(440px, 100%);
    box-shadow: 0 20px 45px rgba(0,0,0,0.25);
    overflow: hidden;
  }
  @media (max-width: 900px) {
    .order-layout { grid-template-columns: minmax(0, 1fr); gap: 20px; }
    .order-side { grid-template-columns: minmax(0, 1fr); }
  }
</style>

@php
  $qrisImageAvailable = \Illuminate\Support\Facades\Storage::disk('public')->exists('qris-statis.png')
      || (filled($qrisImage) && is_file(public_path($qrisImage)));
  $ticketAvailable = in_array($order->payment?->status, ['verified', 'partially_refunded'], true)
      && $order->ticket !== null
      && in_array($order->status, ['paid', 'partially_picked_up', 'picked_up'], true);
@endphp

@if($order->status === 'pending_payment')
  {{-- VIEW COMPACT PEMBAYARAN MOBILE-FIRST --}}
  <div class="wrap" style="max-width: 480px; margin: 0 auto; padding: 12px 16px 40px;">
    <nav class="crumb" aria-label="Breadcrumb" style="margin-bottom: 14px;">
      <a href="{{ route('orders.index') }}">← Kembali ke Pesanan Saya</a>
    </nav>

    <div class="panel" style="padding: 22px; border-radius: 14px; text-align: center;">
      <span class="badge pending_payment" style="margin-bottom: 8px;">Menunggu Pembayaran</span>
      <h1 style="font-size: 1.45rem; margin: 4px 0 2px;">Pembayaran Pesanan</h1>
      <p class="muted" style="font-size: 12.5px; margin: 0 0 16px;">Kode: <strong>{{ $order->order_code }}</strong></p>

      @if($order->payment?->status === 'rejected')
        <div style="margin-bottom: 14px; text-align: left; font-size: 13px; background: var(--bad-bg); color: var(--bad); padding: 10px 14px; border-radius: 8px;">
          <strong>Bukti sebelumnya ditolak:</strong> {{ $order->payment->notes ?: 'Silakan unggah bukti transfer yang jelas dan sesuai nominal.' }}
        </div>
      @endif

      {{-- FOTO QRIS --}}
      @if($qrisImageAvailable)
        <div style="background: #fff; padding: 12px; border: 1px solid var(--line); border-radius: 12px; display: inline-block; max-width: 100%; margin-bottom: 14px;">
          <img src="{{ Route::has('qris.image') ? route('qris.image') : url('/qris-image') }}" alt="QRIS FORTEXIV" style="max-height: 220px; width: auto; max-width: 100%; object-fit: contain; display: block; margin: 0 auto;">
        </div>
      @endif

      {{-- TOTAL HARGA & SALIN NOMINAL --}}
      <div style="background: var(--soft); padding: 14px; border-radius: 10px; margin-bottom: 18px;">
        <span class="muted" style="font-size: 12.5px; display: block;">Total yang perlu dibayar adalah:</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--good); font-family: var(--fd); margin: 4px 0 8px;">
          {{ \App\Support\Money::format($order->total_price) }}
        </div>
        <button type="button" class="btn sec btn-sm" onclick="copyRawPrice('{{ $order->total_price }}')" style="font-size: 12px; padding: 5px 14px;">
          <span id="copyBtnText">📋 Salin Nominal ({{ $order->total_price }})</span>
        </button>
      </div>

      {{-- FORM UPLOAD BUKTI TF --}}
      <form id="proofForm" method="post" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data" style="text-align: left;">
        @csrf
        <input type="hidden" name="method" value="QRIS">
        
        <div class="field" style="margin-bottom: 14px;">
          <label for="proof_input" style="display: block; font-size: 13px; font-weight: 700; margin-bottom: 6px;">
            Masukkan file bukti transfer (JPG, PNG, PDF):
          </label>
          <input class="input" id="proof_input" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required style="font-size: 13px;">
          @error('proof')
            <p class="checkout-error">{{ $message }}</p>
          @enderror
        </div>

        <button type="button" class="btn" id="btnShowConfirmPopup" onclick="handlePreSubmit()" style="width: 100%; min-height: 44px; font-weight: 700;">
          {{ $order->payment?->status === 'rejected' ? 'Unggah Ulang Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}
        </button>
      </form>

      {{-- COLLAPSIBLE RINCIAN PESANAN --}}
      <details style="margin-top: 16px; text-align: left; border-top: 1px solid var(--line); padding-top: 10px;">
        <summary class="muted" style="cursor: pointer; font-size: 12.5px; font-weight: 600;">
          Lihat rincian produk ({{ $order->items->sum('quantity') }} item) ▾
        </summary>
        <div style="margin-top: 10px; font-size: 12.5px; display: grid; gap: 8px;">
          @foreach($order->items as $item)
            <div style="display: flex; justify-content: space-between; gap: 10px;">
              <span>• {{ $item->product_name }} ({{ $item->quantity }}) — <span class="muted">{{ $item->class_name }}</span></span>
              <strong>{{ \App\Support\Money::format($item->subtotal) }}</strong>
            </div>
          @endforeach
        </div>
      </details>
    </div>
  </div>

  {{-- POPUP MODAL KONFIRMASI MANUAL --}}
  <div id="confirmManualModal" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-card-custom" role="dialog" aria-modal="true" style="max-width: 400px; text-align: center; padding: 24px;">
      <div style="font-size: 38px; margin-bottom: 6px;">⏳</div>
      <h3 style="margin: 0 0 10px; font-size: 1.25rem;">Informasi Verifikasi</h3>
      <p style="font-size: 13px; line-height: 1.55; color: var(--muted); margin: 0 0 20px;">
        Bukti transfer akan kami proses dan diverifikasi secara manual oleh admin. 
        <strong style="color: var(--ink);">Verifikasi dapat memakan waktu beberapa saat</strong>. 
        Tiket pickup QR akan otomatis aktif di halaman ini segera setelah verifikasi selesai.
      </p>
      <div style="display: flex; gap: 10px;">
        <button type="button" class="btn sec" onclick="closeConfirmModal()" style="flex: 1;">Batal</button>
        <button type="button" class="btn" onclick="submitProofForm()" style="flex: 1;">Lanjutkan</button>
      </div>
    </div>
  </div>

  <script>
    function copyRawPrice(price) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(price).then(() => {
          const btn = document.getElementById('copyBtnText');
          if (btn) {
            const orig = btn.textContent;
            btn.textContent = '✓ Nominal Disalin!';
            setTimeout(() => btn.textContent = orig, 2000);
          }
        });
      } else {
        alert('Nominal: ' + price);
      }
    }

    function handlePreSubmit() {
      const input = document.getElementById('proof_input');
      if (!input.files || input.files.length === 0) {
        input.reportValidity();
        return;
      }
      document.getElementById('confirmManualModal').style.display = 'flex';
    }

    function closeConfirmModal() {
      document.getElementById('confirmManualModal').style.display = 'none';
    }

    function submitProofForm() {
      document.getElementById('confirmManualModal').style.display = 'none';
      const btn = document.getElementById('btnShowConfirmPopup');
      btn.disabled = true;
      btn.textContent = 'Mengunggah bukti...';
      document.getElementById('proofForm').submit();
    }
  </script>

@else

  {{-- VIEW PESANAN LENGKAP / WAITING VERIFICATION / LUNAS / TIKET PICKUP --}}
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
      <span class="badge {{ $order->status }}">
        @if($order->status === 'waiting_verification')
          ⏳ Menunggu Verifikasi Admin
        @elseif($order->status === 'paid')
          ✓ Pembayaran Lunas
        @else
          {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        @endif
      </span>
    </header>

    @if($order->status === 'waiting_verification')
      <div class="panel" style="background: var(--good-bg); border-color: var(--good); padding: 18px 20px; margin-bottom: 24px; border-radius: 12px;">
        <strong style="color: var(--good); font-size: 1.05rem; display: block; margin-bottom: 4px;">
          ⏳ Bukti Transfer Telah Diterima!
        </strong>
        <p style="margin: 0; font-size: 13.5px; color: var(--ink); line-height: 1.55;">
          Pembayaran Anda sedang diverifikasi secara manual oleh admin. 
          <strong>Verifikasi dapat memakan waktu beberapa saat</strong>. 
          Tiket &amp; QR pickup akan otomatis muncul dan aktif di halaman ini setelah verifikasi berhasil.
        </p>
      </div>
    @endif

    <div class="order-layout">
      <div class="order-main">
        <section aria-labelledby="order-items-heading">
          <div class="order-section-heading">
            <h2 id="order-items-heading">Produk yang dipesan</h2>
            <span>{{ $order->items->sum('quantity') }} produk</span>
          </div>

          <div class="order-groups">
            <div class="order-group">
              <ul class="order-items" style="margin-top: 0; border-top: 0;">
                @foreach($order->items as $item)
                  <li class="order-item">
                    <div>
                      <strong class="order-item-name">{{ $item->product_name }}</strong>
                      <span class="order-item-meta">{{ $item->quantity }} × {{ \App\Support\Money::format($item->price_at_order) }} &middot; {{ $item->class_name }}</span>
                    </div>
                    <div class="order-item-money">
                      <strong>{{ \App\Support\Money::format($item->subtotal) }}</strong>
                    </div>
                  </li>
                @endforeach
              </ul>
              <div class="order-group-total" style="margin-top: 12px;">
                <span>Total</span>
                <strong>{{ \App\Support\Money::format($order->total_price) }}</strong>
              </div>
            </div>
          </div>
        </section>
      </div>

      <aside class="order-side">
        <section class="order-ticket" aria-labelledby="ticket-heading">
          <span class="badge {{ $ticketAvailable ? 'good' : 'pending' }}">
            {{ $ticketAvailable ? 'Tiket Tersedia' : 'Tiket Menunggu Verifikasi' }}
          </span>
          <h2 id="ticket-heading" style="margin-top: 8px;">Tiket &amp; QR Pickup</h2>
          
          @if($ticketAvailable)
            <p>Tunjukkan tiket QR ini kepada petugas kelas saat pengambilan produk pada Hari H.</p>
            <a class="btn lime" href="{{ route('tickets.show', $order) }}">Buka Tiket &amp; Tampilkan QR</a>
          @else
            <p>QR pickup akan otomatis aktif di sini segera setelah bukti pembayaran Anda diverifikasi oleh admin.</p>
          @endif
        </section>
      </aside>
    </div>
  </div>

@endif
@endsection
