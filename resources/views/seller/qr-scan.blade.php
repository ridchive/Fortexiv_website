@extends('layouts.app')

@section('title', 'Scan QR Pesanan — Panel Penjual FORTEXIV')

@section('content')
<style>
  .qr-scanner-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 24px;
    align-items: start;
  }
  
  /* Scanner Card & Viewport */
  .scanner-viewport-wrap {
    position: relative;
    width: 100%;
    max-width: 320px;
    height: 320px;
    margin: 16px auto;
    border-radius: 16px;
    overflow: hidden;
    background: #060a07;
    border: 2px solid rgba(169, 203, 91, 0.35);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #seller-qr-reader {
    width: 100% !important;
    height: 100% !important;
    position: absolute !important;
    inset: 0 !important;
    border: none !important;
    padding: 0 !important;
  }
  #seller-qr-reader > div {
    width: 100% !important;
    height: 100% !important;
    position: absolute !important;
    inset: 0 !important;
    border: none !important;
  }
  #seller-qr-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
  }
  #seller-qr-reader canvas {
    display: none !important;
  }
  /* Hide html5-qrcode's default rigid pixel borders */
  #qr-shaded-region {
    display: none !important;
  }

  /* Scanner Standby Placeholder */
  .scanner-standby {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    text-align: center;
    background: radial-gradient(circle at center, rgba(30, 48, 34, 0.9) 0%, rgba(6, 10, 7, 0.98) 100%);
    color: var(--muted);
    z-index: 5;
    transition: opacity 0.2s ease;
  }
  .scanner-standby.hidden {
    display: none !important;
  }

  /* Custom Native-like Responsive Viewfinder Overlay */
  .scanner-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }
  .scanner-overlay.active {
    display: flex !important;
  }
  .scanner-frame {
    position: relative;
    width: 210px;
    height: 210px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
    border-radius: 16px;
  }
  /* 4 Corner Markers */
  .scanner-corner {
    position: absolute;
    width: 28px;
    height: 28px;
    border: 3.5px solid var(--lime);
  }
  .scanner-corner.tl { top: 0; left: 0; border-right: none; border-bottom: none; border-top-left-radius: 14px; }
  .scanner-corner.tr { top: 0; right: 0; border-left: none; border-bottom: none; border-top-right-radius: 14px; }
  .scanner-corner.bl { bottom: 0; left: 0; border-right: none; border-top: none; border-bottom-left-radius: 14px; }
  .scanner-corner.br { bottom: 0; right: 0; border-left: none; border-top: none; border-bottom-right-radius: 14px; }

  /* Animated Laser Scan Beam */
  .scanner-laser {
    position: absolute;
    left: 8px;
    right: 8px;
    height: 2.5px;
    background: linear-gradient(90deg, transparent, var(--lime), transparent);
    box-shadow: 0 0 10px var(--lime);
    border-radius: 2px;
    animation: scannerScan 2.2s ease-in-out infinite alternate;
  }
  @keyframes scannerScan {
    0% { top: 10px; opacity: 0.3; }
    50% { opacity: 1; }
    100% { top: calc(100% - 12px); opacity: 0.3; }
  }

  /* Action Controls */
  .scanner-controls {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
  }
  .scanner-toggle-btn {
    width: 100%;
    max-width: 320px;
    justify-content: center;
    padding: 11px 18px;
    font-weight: 700;
    font-size: 14px;
    border-radius: 8px;
  }

  .qr-manual-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
  }

  @media (max-width: 860px) {
    .qr-scanner-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }
    .scanner-viewport-wrap {
      max-width: 280px;
      height: 280px;
    }
    .scanner-frame {
      width: 180px;
      height: 180px;
    }
    .qr-manual-form {
      flex-direction: column;
      align-items: stretch;
    }
    .qr-manual-form .btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>
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
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 6px;">
          <div class="eyebrow" style="margin: 0;">OPSI 1: PEMINDAI KAMERA (SCAN QR)</div>
          <span class="badge open" style="font-size: 11px; font-weight: 700; padding: 3px 8px;">✨ Auto-Detect Semua Format QR</span>
        </div>
        <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Pindai QR pembeli secara langsung menggunakan kamera. Class QR maupun Product QR akan otomatis terverifikasi.</p>

        <!-- Viewport Container with Native-like Responsive Overlay -->
        <div class="scanner-viewport-wrap">
          <div id="seller-qr-reader"></div>

          <!-- Standby Placeholder -->
          <div class="scanner-standby" data-scanner-standby>
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(140, 208, 143, 0.12); border: 1px solid var(--lime); display: flex; align-items: center; justify-content: center; margin-bottom: 10px; color: var(--lime);">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
            </div>
            <strong style="color: var(--ink); font-size: 14px;">Kamera Siap Digunakan</strong>
            <span style="font-size: 12px; margin-top: 4px; line-height: 1.4;">Tekan tombol di bawah untuk menyalakan kamera & memindai QR.</span>
          </div>

          <!-- Active Target Viewfinder Overlay -->
          <div class="scanner-overlay" data-scanner-overlay>
            <div class="scanner-frame">
              <span class="scanner-corner tl"></span>
              <span class="scanner-corner tr"></span>
              <span class="scanner-corner bl"></span>
              <span class="scanner-corner br"></span>
              <span class="scanner-laser"></span>
            </div>
          </div>
        </div>

        <!-- Controls & Toggle -->
        <div class="scanner-controls">
          <button class="btn lime scanner-toggle-btn" type="button" data-scanner-toggle style="width: 100%; justify-content: center; font-size: 15px; padding: 12px;">
            📷 Buka Kamera Pemindai
          </button>
        </div>

        <p class="note info" role="status" aria-live="polite" data-qr-message style="margin-top: 12px; font-size: 13px; text-align: center;">
          Siap digunakan. Tekan tombol kamera di atas atau gunakan input kode manual di bawah.
        </p>
      </section>

      <section class="panel" style="margin: 0;">
        <div class="eyebrow" style="margin-bottom: 8px;">OPSI 2: INPUT KODE MANUAL</div>
        <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Jika kamera bermasalah atau pembeli menyebutkan kode secara lisan / chat:</p>
        <form class="qr-manual-form" data-qr-manual-form action="#" method="POST">
          @csrf
          <div class="field" style="flex: 1; margin: 0;">
            <label for="seller-qr-token" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Kode Token QR / Nomor Pesanan (contoh: FTXS-..., FTXC-..., atau FTXP-...)</label>
            <input class="input" id="seller-qr-token" name="token" placeholder="Ketik atau tempel kode di sini..." maxlength="80" autocomplete="off" style="font-family: monospace;">
          </div>
          <button class="btn sec" type="submit" style="white-space: nowrap;">Periksa Kode ➔</button>
        </form>
      </section>
    </div>

    <div>
      <section class="panel" data-qr-preview hidden style="margin: 0; border: 2px solid var(--good); background: var(--surface);">
        <div class="eyebrow" style="color: var(--good); margin-bottom: 8px;">DETAIL SEBELUM KONFIRMASI</div>
        <h2 data-qr-order style="margin: 0 0 14px; color: var(--good);">✅ Barang Berhasil Diserahkan</h2>

        <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13.5px;">
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">NAMA PEMBELI / PENGAMBIL</div>
              <strong data-qr-buyer style="font-size: 15px;">—</strong>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">BOOTH / KELAS</div>
              <span data-qr-class style="font-weight: 600;">—</span>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">STATUS PENYERAHAN</div>
              <span class="badge delivered" data-qr-status>Delivered</span>
            </div>
            <div>
              <div class="muted" style="font-size: 11.5px; font-weight: 700;">TOTAL ITEM DISERAHKAN</div>
              <strong data-qr-delivered style="font-size: 15px; color: var(--good);">—</strong>
            </div>
          </div>
        </div>

        <div style="margin-bottom: 18px;">
          <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">
            DAFTAR BARANG YANG TELAH DIBERIKAN:
          </div>
          <ul data-qr-products style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;"></ul>
        </div>

        <div style="padding-top: 14px; border-top: 1px solid var(--line);">
          <button class="btn lime" type="button" data-qr-next style="width: 100%; justify-content: center; font-size: 14px; padding: 10px;">
            📷 Pindai QR Pesanan Berikutnya
          </button>
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
  const standbyEl = scannerPage.querySelector('[data-scanner-standby]');
  const overlayEl = scannerPage.querySelector('[data-scanner-overlay]');
  const toggleBtn = scannerPage.querySelector('[data-scanner-toggle]');
  const manualForm = scannerPage.querySelector('[data-qr-manual-form]');
  const tokenInput = manualForm.querySelector('[name="token"]');
  const preview = scannerPage.querySelector('[data-qr-preview]');
  const nextButton = preview.querySelector('[data-qr-next]');
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

  const resetScannerUI = () => {
    standbyEl?.classList.remove('hidden');
    overlayEl?.classList.remove('active');
    if (toggleBtn) {
      toggleBtn.innerHTML = '📷 Buka Kamera Pemindai';
      toggleBtn.className = 'btn lime scanner-toggle-btn';
    }
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
    if (!scannerRunning || !scanner) {
      resetScannerUI();
      return;
    }
    try {
      await scanner.stop();
      scanner.clear();
    } catch (e) {
      console.warn('Stop scanner error:', e);
    }
    scanner = null;
    scannerRunning = false;
    resetScannerUI();
  };

  const detectQrType = (token) => {
    if (!token) return 'class';
    const clean = token.trim().toUpperCase();
    if (clean.startsWith('FTXP-')) return 'product';
    if (clean.startsWith('FTXS-')) return 'store';
    return 'class';
  };

  const showDeliveryResult = (result) => {
    const data = result.data || {};
    if (preview.querySelector('[data-qr-buyer]')) {
      preview.querySelector('[data-qr-buyer]').textContent = data.buyer_name || 'Pembeli';
    }
    if (preview.querySelector('[data-qr-class]')) {
      preview.querySelector('[data-qr-class]').textContent = data.store_name || '—';
    }
    if (preview.querySelector('[data-qr-delivered]')) {
      preview.querySelector('[data-qr-delivered]').textContent = `${data.delivered_count || 1} unit selesai diserahkan`;
    }

    const productsList = preview.querySelector('[data-qr-products]');
    if (productsList) {
      productsList.replaceChildren();
      if (data.products && data.products.length > 0) {
        data.products.forEach((prod) => {
          const li = document.createElement('li');
          li.style.cssText = 'padding: 8px 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 6px; font-size: 13px; display: flex; justify-content: space-between; align-items: center;';
          li.innerHTML = `<span><strong>${prod.name}</strong> × ${prod.delivered_now} unit</span> <span class="badge delivered">Diserahkan</span>`;
          productsList.appendChild(li);
        });
      }
    }

    preview.hidden = false;
    preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  const deliverToken = async (token) => {
    const cleanToken = token.trim().toUpperCase();
    activeToken = cleanToken;
    tokenInput.value = cleanToken;
    activeType = detectQrType(cleanToken);
    preview.hidden = true;
    setMessage('Memproses serah terima barang di sistem…', 'info');

    try {
      const result = await post(scannerPage.dataset.confirmUrl, {
        type: activeType,
        token: activeToken,
      });

      showDeliveryResult(result);
      setMessage(`✅ ${result.message}`, 'info');
      tokenInput.value = '';
    } catch (err) {
      setMessage(`❌ ${err.message}`, 'warn');
    }
  };

  const startScanner = async () => {
    try {
      await stopScanner();
      preview.hidden = true;

      if (typeof Html5Qrcode === 'undefined') {
        setMessage('Memuat modul kamera scanner...', 'info');
        await new Promise((res) => setTimeout(res, 800));
        if (typeof Html5Qrcode === 'undefined') {
          setMessage('Modul kamera belum tersedia. Silakan gunakan Opsi 2 (Input Kode Manual) di bawah.', 'warn');
          return;
        }
      }

      setMessage('Membuka kamera...', 'info');
      scanner = new Html5Qrcode('seller-qr-reader');
      await scanner.start(
        { facingMode: 'environment' },
        {
          fps: 10,
        },
        async (decodedText) => {
          if (processing) return;
          processing = true;
          try {
            await stopScanner();
            await deliverToken(decodedText);
          } catch (e) {
            setMessage(`Gagal memproses hasil scan: ${e.message}`, 'warn');
          } finally {
            processing = false;
          }
        },
        () => {}
      );
      scannerRunning = true;
      standbyEl?.classList.add('hidden');
      overlayEl?.classList.add('active');
      if (toggleBtn) {
        toggleBtn.innerHTML = '⏹ Matikan Kamera';
        toggleBtn.className = 'btn sec scanner-toggle-btn';
      }
      setMessage('Kamera aktif. Posisikan QR di dalam kotak pemindai.', 'info');
    } catch (err) {
      if (scanner && !scannerRunning) {
        try { scanner.clear(); } catch (_) {}
        scanner = null;
      }
      resetScannerUI();
      setMessage(`Kamera tidak dapat dibuka: ${err.message || 'Izin kamera ditolak atau tidak tersedia'}. Gunakan input manual di bawah.`, 'warn');
    }
  };

  if (toggleBtn) {
    toggleBtn.addEventListener('click', async () => {
      if (scannerRunning) {
        await stopScanner();
        setMessage('Kamera dinonaktifkan. Anda dapat menyalakannya kembali kapan saja.', 'info');
      } else {
        await startScanner();
      }
    });
  }

  manualForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const val = tokenInput.value.trim();
    if (!val) {
      setMessage('Masukkan kode token atau nomor pesanan terlebih dahulu.', 'warn');
      return;
    }
    await deliverToken(val);
  });

  if (nextButton) {
    nextButton.addEventListener('click', async () => {
      preview.hidden = true;
      await startScanner();
    });
  }
})();
</script>
@endpush
