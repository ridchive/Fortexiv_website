@extends('layouts.app')

@section('title', 'Scan QR Pesanan — Panel Penjual FORTEXIV')

@section('content')
<div data-qr-scanner data-preview-url="{{ route('seller.pickup.qr.preview') }}" data-confirm-url="{{ route('seller.pickup.qr.confirm') }}">
  <div class="section-head" style="margin-bottom: 20px;">
    <div>
      <div class="eyebrow">SERAH-TERIMA PESANAN (HARI H)</div>
      <h1>Verifikasi & Scan QR Pengambilan</h1>
      <p class="muted" style="margin-top: 4px; font-size: 14px;">Pindai QR pembeli (Class QR untuk seluruh barang kelas atau Product QR per unit) atau masukkan kode token secara manual.</p>
    </div>
    <div>
      <a class="btn sec" href="{{ route('seller.orders') }}">Daftar Semua Pesanan</a>
    </div>
  </div>

  <div class="grid" style="display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); gap: 24px; align-items: start;">
    <div>
      <section class="panel" style="margin: 0 0 20px;">
        <div class="eyebrow" style="margin-bottom: 8px;">OPSI 1: PEMINDAI KAMERA (SCAN QR)</div>
        <p class="muted" style="font-size: 13px; margin-bottom: 14px;">Klik tombol di bawah untuk menyalakan kamera. Format QR (Class QR / Product QR) akan terdeteksi otomatis.</p>
        
        <div class="btns" style="margin-bottom: 14px;">
          <button class="btn" type="button" data-qr-mode="class">📷 Buka Kamera (Auto-Detect / Class QR)</button>
          <button class="btn sec" type="button" data-qr-mode="product">Scan Product QR Khusus</button>
        </div>

        <div id="seller-qr-reader" style="max-width: 480px; margin: 16px auto; border-radius: 8px; overflow: hidden; background: var(--soft);"></div>

        <p class="note info" role="status" aria-live="polite" data-qr-message style="margin-top: 10px;">
          Siap digunakan. Tekan tombol kamera di atas atau masukkan kode manual di bawah.
        </p>
      </section>

      <section class="panel" style="margin: 0;">
        <div class="eyebrow" style="margin-bottom: 8px;">OPSI 2: INPUT KODE MANUAL</div>
        <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Jika kamera bermasalah atau pembeli menyebutkan kode secara lisan / chat:</p>
        <form class="inline-form" data-qr-manual-form style="display: flex; gap: 10px; align-items: flex-end;">
          <div class="field" style="flex: 1; margin: 0;">
            <label for="seller-qr-token" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Kode Token QR (contoh: FTXC-... atau FTXP-...)</label>
            <input class="input" id="seller-qr-token" name="token" placeholder="Ketik atau tempel kode QR di sini..." maxlength="80" autocomplete="off" style="font-family: monospace;">
          </div>
          <button class="btn sec" type="submit" style="white-space: nowrap;">Periksa Kode ➔</button>
        </form>
      </section>
    </div>

    <div>
      <section class="panel" data-qr-preview hidden style="margin: 0; border: 2px solid var(--good); background: var(--surface);">
        <div class="eyebrow" style="color: var(--good); margin-bottom: 8px;">DETAIL PESANAN PEMBELI</div>
        <h2 data-qr-order style="margin: 0 0 14px;"></h2>

        <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13.5px;">
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">NAMA PEMBELI / PENGAMBIL</div>
              <strong data-qr-buyer style="font-size: 15px;">—</strong>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">NOMOR KONTAK / WHATSAPP</div>
              <strong data-qr-phone style="font-size: 15px;">—</strong>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">GRUP KELAS</div>
              <span data-qr-class style="font-weight: 600;">—</span>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">UNIT BELUM DIAMBIL</div>
              <span class="badge warn" data-qr-pending>—</span>
            </div>
          </div>
        </div>

        <div style="margin-bottom: 18px;">
          <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">
            DAFTAR BARANG YANG HARUS DIBERIKAN:
          </div>
          <ul data-qr-products style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;"></ul>
        </div>

        <div style="padding-top: 14px; border-top: 1px solid var(--line);">
          <button class="btn" type="button" data-qr-confirm style="width: 100%; justify-content: center; font-size: 15px; padding: 12px; background: var(--good); border-color: var(--good); color: #fff;">
            ✅ Konfirmasi Serah Terima Barang (Delivered)
          </button>
          <p class="muted" style="font-size: 12px; text-align: center; margin: 8px 0 0;">
            Pastikan produk fisik telah diserahkan sebelum menekan tombol di atas.
          </p>
        </div>
      </section>
    </div>
  </div>
</div>
@endsection
