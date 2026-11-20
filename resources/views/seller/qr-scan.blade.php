@extends('layouts.app')

@section('title', 'Scan QR Pesanan — Panel Penjual FORTEXIV')

@push('styles')
<style>
  .qr-scanner-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 24px;
    align-items: start;
  }
  .qr-scanner-btns {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }
  .qr-manual-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
  }
  #seller-qr-reader {
    width: 100% !important;
    max-width: 440px;
    margin: 16px auto;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background: #000;
  }
  #seller-qr-reader video {
    width: 100% !important;
    height: auto !important;
    border-radius: 12px;
    display: block;
    margin: 0 auto;
  }
  #seller-qr-reader canvas {
    max-width: 100% !important;
    height: auto !important;
  }
  @media (max-width: 860px) {
    .qr-scanner-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .qr-scanner-btns {
      flex-direction: column;
    }
    .qr-scanner-btns .btn {
      width: 100%;
      justify-content: center;
      text-align: center;
    }
    .qr-manual-form {
      flex-direction: column;
      align-items: stretch;
    }
    .qr-manual-form .btn {
      width: 100%;
      justify-content: center;
    }
    #seller-qr-reader {
      max-width: 100% !important;
    }
  }
</style>
@endpush

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

  <div class="qr-scanner-grid">
    <div>
      <section class="panel" style="margin: 0 0 20px;">
        <div class="eyebrow" style="margin-bottom: 8px;">OPSI 1: PEMINDAI KAMERA (SCAN QR)</div>
        <p class="muted" style="font-size: 13px; margin-bottom: 14px;">Klik tombol di bawah untuk menyalakan kamera. Format QR (Class QR / Product QR) akan terdeteksi otomatis.</p>
        
        <div class="qr-scanner-btns">
          <button class="btn" type="button" data-qr-mode="class">📷 Buka Kamera (Auto-Detect / Class QR)</button>
          <button class="btn sec" type="button" data-qr-mode="product">Scan Product QR Khusus</button>
        </div>

        <div id="seller-qr-reader"></div>

        <p class="note info" role="status" aria-live="polite" data-qr-message style="margin-top: 10px;">
          Siap digunakan. Tekan tombol kamera di atas atau masukkan kode manual di bawah.
        </p>
      </section>

      <section class="panel" style="margin: 0;">
        <div class="eyebrow" style="margin-bottom: 8px;">OPSI 2: INPUT KODE MANUAL</div>
        <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Jika kamera bermasalah atau pembeli menyebutkan kode secara lisan / chat:</p>
        <form class="qr-manual-form" data-qr-manual-form action="#" method="POST">
          @csrf
          <div class="field" style="flex: 1; margin: 0;">
            <label for="seller-qr-token" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Kode Token QR / Nomor Pesanan (contoh: FTXC-..., FTXP-..., atau FX-...)</label>
            <input class="input" id="seller-qr-token" name="token" placeholder="Ketik atau tempel kode di sini..." maxlength="80" autocomplete="off" style="font-family: monospace;">
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

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(() => {
  const scannerPage = document.querySelector('[data-qr-scanner]');
  if (!scannerPage) return;

  const reader = document.getElementById('seller-qr-reader');
  const message = scannerPage.querySelector('[data-qr-message]');
  const manualForm = scannerPage.querySelector('[data-qr-manual-form]');
  const tokenInput = manualForm.querySelector('[name="token"]');
  const preview = scannerPage.querySelector('[data-qr-preview]');
  const confirmButton = preview.querySelector('[data-qr-confirm]');
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.content : '';

  let activeType = 'class';
  let activeToken = null;
  let scanner = null;
  let scannerRunning = false;
  let processing = false;

  const setMessage = (text, type = 'info') => {
    message.className = `note ${type}`;
    message.textContent = text;
  };

  const responseError = (payload) => {
    if (payload.errors) {
      const firstKey = Object.keys(payload.errors)[0];
      return payload.errors[firstKey][0];
    }
    return payload.message || 'QR tidak dapat diproses.';
  };

  const post = async (url, values) => {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(values),
    });
    const payload = await response.json();
    if (!response.ok) {
      throw new Error(responseError(payload));
    }
    return payload;
  };

  const stopScanner = async () => {
    if (!scannerRunning || !scanner) return;
    try {
      await scanner.stop();
      scanner.clear();
    } catch (e) {
      console.warn('Stop scanner error:', e);
    }
    scanner = null;
    scannerRunning = false;
  };

  const detectQrType = (token) => {
    if (!token) return activeType || 'class';
    const clean = token.trim().toUpperCase();
    if (clean.startsWith('FTXP-')) return 'product';
    return 'class';
  };

  const showPreview = (data) => {
    activeToken = data.token || activeToken;
    activeType = data.type || activeType;

    if (preview.querySelector('[data-qr-order]')) {
      preview.querySelector('[data-qr-order]').textContent = `Order ID: ${data.order_code}`;
    }
    if (preview.querySelector('[data-qr-buyer]')) {
      preview.querySelector('[data-qr-buyer]').textContent = data.buyer_name || '—';
    }
    if (preview.querySelector('[data-qr-phone]')) {
      preview.querySelector('[data-qr-phone]').textContent = data.buyer_phone || '—';
    }
    if (preview.querySelector('[data-qr-class]')) {
      preview.querySelector('[data-qr-class]').textContent = data.class_name || '—';
    }
    if (preview.querySelector('[data-qr-pending]')) {
      preview.querySelector('[data-qr-pending]').textContent = `${data.pending_count} unit`;
    }

    const productsList = preview.querySelector('[data-qr-products]');
    if (productsList && data.products) {
      productsList.replaceChildren();
      data.products.forEach((prod) => {
        const li = document.createElement('li');
        li.style.cssText = 'padding: 8px 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 6px; font-size: 13px;';
        li.innerHTML = `<strong>${prod.name}</strong> × ${prod.quantity} unit &middot; <span class="badge ${prod.pending_count === 0 ? 'delivered' : 'waiting_verification'}">${prod.delivered_count} Diserahkan, ${prod.pending_count} Belum Diambil</span>`;
        productsList.appendChild(li);
      });
    }

    preview.hidden = false;
    confirmButton.disabled = false;
    preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  const previewToken = async (token) => {
    const cleanToken = token.trim().toUpperCase();
    activeToken = cleanToken;
    tokenInput.value = cleanToken;
    activeType = detectQrType(cleanToken);
    preview.hidden = true;
    setMessage('Memeriksa kode di sistem…', 'info');

    try {
      const payload = await post(scannerPage.dataset.previewUrl, {
        type: activeType,
        token: activeToken,
      });
      showPreview(payload.data);
      setMessage('Pesanan ditemukan! Periksa detail barang sebelum konfirmasi serah-terima.', 'info');
    } catch (err) {
      setMessage(err.message, 'warn');
    }
  };

  const startScanner = async (type) => {
    try {
      await stopScanner();
      activeType = type;
      preview.hidden = true;

      if (typeof Html5Qrcode === 'undefined') {
        setMessage('Memuat modul kamera scanner...', 'info');
        await new Promise((res) => setTimeout(res, 800));
        if (typeof Html5Qrcode === 'undefined') {
          setMessage('Modul kamera belum tersedia. Silakan gunakan Opsi 2 (Input Kode Manual) di bawah.', 'warn');
          return;
        }
      }

      setMessage(type === 'class' ? 'Arahkan kamera ke QR kelas pembeli.' : 'Arahkan kamera ke QR produk pembeli.', 'info');
      scanner = new Html5Qrcode('seller-qr-reader');
      await scanner.start(
        { facingMode: 'environment' },
        {
          fps: 10,
          qrbox: (w, h) => {
            const minEdge = Math.min(w, h);
            const size = Math.floor(minEdge * 0.7);
            return { width: size, height: size };
          },
        },
        async (decodedText) => {
          if (processing) return;
          processing = true;
          try {
            await stopScanner();
            await previewToken(decodedText);
          } catch (e) {
            setMessage(`Gagal memproses hasil scan: ${e.message}`, 'warn');
          } finally {
            processing = false;
          }
        },
        () => {}
      );
      scannerRunning = true;
    } catch (err) {
      if (scanner && !scannerRunning) {
        try { scanner.clear(); } catch (_) {}
        scanner = null;
      }
      setMessage(`Kamera tidak dapat dibuka: ${err.message || 'Izin kamera ditolak atau tidak tersedia'}. Gunakan input manual di bawah.`, 'warn');
    }
  };

  scannerPage.querySelectorAll('[data-qr-mode]').forEach((btn) => {
    btn.addEventListener('click', () => startScanner(btn.dataset.qrMode));
  });

  manualForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const val = tokenInput.value.trim();
    if (!val) {
      setMessage('Masukkan kode token atau nomor pesanan terlebih dahulu.', 'warn');
      return;
    }
    await previewToken(val);
  });

  confirmButton.addEventListener('click', async () => {
    if (!activeToken) return;
    confirmButton.disabled = true;
    setMessage('Menyimpan konfirmasi serah terima…', 'info');

    try {
      const result = await post(scannerPage.dataset.confirmUrl, {
        type: activeType,
        token: activeToken,
      });
      preview.hidden = true;
      tokenInput.value = '';
      activeToken = null;
      setMessage(`✅ ${result.message}`, 'info');
    } catch (err) {
      confirmButton.disabled = false;
      setMessage(err.message, 'warn');
    }
  });
})();
</script>
@endpush
