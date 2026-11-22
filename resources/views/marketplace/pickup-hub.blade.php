@extends('layouts.app')

@section('title', 'Tiket Pickup Toko — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Tiket Pickup</span></nav>

  <div class="pgh">
    <span class="eyebrow">DIGITAL PICKUP PASS</span>
    <h1>Tiket Pickup Toko</h1>
    <p>Semua produk dari toko/kelas yang sama digabung menjadi 1 QR code. Tunjukkan QR toko bersangkutan saat mengambil pesanan di booth.</p>
  </div>

  @include('marketplace.partials.buyer-tabs', ['activeTab' => 'tickets'])

  @if(empty($stores))
    <div class="empty">
      <h3>Belum ada tiket pickup aktif</h3>
      <p>Tiket pengambilan per toko akan otomatis muncul di sini setelah pembayaran pesananmu terverifikasi / lunas.</p>
      <div class="btns" style="justify-content: center; margin-top: 14px;">
        <a class="btn" href="{{ route('orders.index') }}">Lihat pesanan saya</a>
        <a class="btn sec" href="{{ route('catalog.index') }}">Jelajahi produk</a>
      </div>
    </div>
  @else
    <div class="ticket-hub-grid" style="display: flex; flex-direction: column; gap: 24px;">
      @foreach($stores as $sellerId => $store)
        @php
          $isDelivered = $store['total_pending'] === 0;
        @endphp
        <section class="panel ticket-group" style="border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; background: var(--surface);">
          <div class="ticket-group-heading" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; flex-wrap: wrap; margin-bottom: 20px;">
            <div>
              <span class="eyebrow">BOOTH / TOKO KELAS</span>
              <h2 style="margin: 4px 0 6px;">{{ $store['class_name'] }}</h2>
              <span class="muted small">Seller ID: #{{ $sellerId }}</span>
            </div>
            <div>
              @if($isDelivered)
                <span class="badge delivered">Semua produk sudah diserahkan</span>
              @else
                <span class="badge paid">{{ $store['total_pending'] }} item siap diambil</span>
              @endif
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; align-items: start;">
            <!-- QR Card -->
            <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 12px; padding: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
              @if(! $isDelivered)
                <div style="background: #ffffff; padding: 12px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 12px;">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($store['pickup_code']) }}" alt="QR Toko {{ $store['class_name'] }}" style="display: block; width: 170px; height: 170px;" loading="lazy">
                </div>
                <span class="eyebrow" style="font-size: 11px;">KODE TOKEN TOKO</span>
                <strong style="font-family: monospace; font-size: 15px; letter-spacing: 0.05em; color: var(--ink); margin: 4px 0 10px;">{{ $store['pickup_code'] }}</strong>
                <p class="muted small" style="margin: 0; line-height: 1.4;">Tunjukkan QR ini ke penjual di booth {{ $store['class_name'] }}. Sekali scan, seluruh barang pesananmu di toko ini langsung terkonfirmasi.</p>
              @else
                <div style="padding: 30px 16px;">
                  <span style="font-size: 42px; display: block; margin-bottom: 8px;">✅</span>
                  <strong style="font-size: 16px; color: var(--ink);">Selesai Diambil</strong>
                  <p class="muted small" style="margin-top: 6px;">Seluruh pesanan Anda di booth ini sudah berhasil diserahkan.</p>
                </div>
              @endif
            </div>

            <!-- Items List -->
            <div>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 1.1rem; margin: 0;">Rincian Barang yang Dipesan</h3>
                <span class="muted small">Total: {{ array_sum(array_column($store['products'], 'quantity')) }} pcs</span>
              </div>

              <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 12px; overflow: hidden;">
                <ul style="list-style: none; margin: 0; padding: 0;">
                  @foreach($store['products'] as $prod)
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--line); gap: 12px;">
                      <div>
                        <strong style="display: block; font-size: 14px; color: var(--ink);">{{ $prod['name'] }}</strong>
                        <span class="muted small">Total dibeli: {{ $prod['quantity'] }} pcs</span>
                      </div>
                      <div style="text-align: right;">
                        @if($prod['pending'] > 0)
                          <span class="badge paid" style="font-weight: 700;">{{ $prod['pending'] }} siap ambil</span>
                          @if($prod['delivered'] > 0)
                            <div class="muted small" style="margin-top: 4px;">{{ $prod['delivered'] }} sdh diambil</div>
                          @endif
                        @else
                          <span class="badge delivered">Lengkap diserahkan</span>
                        @endif
                      </div>
                    </li>
                  @endforeach
                </ul>
              </div>

              <div class="note info" style="margin-top: 14px; font-size: 12.5px; padding: 10px 14px;">
                💡 <strong>Catatan:</strong> Jika Anda melakukan pemesanan tambahan di toko ini, produk baru akan otomatis tergabung ke dalam QR di atas setelah pembayaran diverifikasi.
              </div>
            </div>
          </div>
        </section>
      @endforeach
    </div>
  @endif
</div>
@endsection
