@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran — FORTEXIV Admin')

@section('content')
@php
  $paymentStatusLabels = [
    'pending' => 'Menunggu Verifikasi',
    'verified' => 'Terverifikasi',
    'rejected' => 'Ditolak',
  ];
@endphp

<style>
  .admin-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
  }
  .admin-page-header h1 {
    margin: 0 0 6px;
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
  }
  .payments-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
  }
  .payments-table th {
    background: var(--soft);
    padding: 12px 14px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: var(--muted);
    border-bottom: 1px solid var(--line);
    white-space: nowrap;
  }
  .payments-table td {
    padding: 14px;
    border-bottom: 1px solid var(--line);
    vertical-align: top;
  }
  .buyer-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .buyer-meta .buyer-name {
    font-weight: 700;
    color: var(--ink);
  }
  .buyer-meta .buyer-user {
    font-size: 12px;
    color: var(--muted);
  }
  .buyer-meta .buyer-wa {
    font-size: 12px;
    color: var(--good);
    text-decoration: none;
    font-weight: 600;
  }
  .buyer-meta .buyer-wa:hover {
    text-decoration: underline;
  }
  .items-mini-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 280px;
    font-size: 12.5px;
  }
  .item-mini-row {
    line-height: 1.4;
  }
  .item-class-badge {
    color: var(--muted);
    font-size: 11.5px;
  }
  .proof-thumbnail-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 6px;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: var(--surface);
    cursor: pointer;
    text-decoration: none;
    color: var(--ink);
    font-size: 12px;
    transition: border-color .15s;
  }
  .proof-thumbnail-btn:hover {
    border-color: var(--good);
  }
  .proof-thumb-img {
    width: 38px;
    height: 38px;
    object-fit: cover;
    border-radius: 4px;
    background: var(--soft);
  }
  .actions-cell {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 140px;
  }
  .btn-action-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }
  .modal-backdrop-custom {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0,0,0,0.6);
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
    border-radius: 12px;
    width: min(480px, 100%);
    box-shadow: 0 20px 45px rgba(0,0,0,0.25);
    overflow: hidden;
  }
  .modal-card-custom-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
  }
  .modal-card-custom-head h3 {
    margin: 0;
    font-size: 1.15rem;
  }
  .modal-card-custom-body {
    padding: 20px;
    display: grid;
    gap: 14px;
  }
  .modal-card-custom-footer {
    padding: 14px 20px 18px;
    border-top: 1px solid var(--line);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }
  .lightbox-modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(0,0,0,0.85);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .lightbox-content {
    max-width: 90vw;
    max-height: 85vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
  }
  .lightbox-close {
    position: absolute;
    top: 20px;
    right: 24px;
    background: transparent;
    border: 0;
    color: #fff;
    font-size: 32px;
    cursor: pointer;
    line-height: 1;
  }
</style>

<div class="admin-page-header">
  <div>
    <h1>Verifikasi Pembayaran</h1>
    <p class="muted">Tinjau dan verifikasi bukti transfer/QRIS dari pembeli untuk menerbitkan tiket pickup.</p>
  </div>
  <div>
    <button type="button" class="btn sec btn-sm" onclick="openQrisModal()">
      <span>⚙️ Atur Gambar QRIS</span>
    </button>
  </div>
</div>

<div class="panel" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
  <div class="table-wrap" style="margin: 0; border: 0;">
    <table class="payments-table">
      <thead>
        <tr>
          <th>Waktu Transaksi</th>
          <th>Kode Pesanan</th>
          <th>Pembeli</th>
          <th>Barang &amp; Kelas</th>
          <th>Total</th>
          <th>Bukti Transfer</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          @php
            $buyer = $payment->order?->buyer;
            $phone = $payment->order?->pickup_contact_phone ?: ($buyer?->phone ?? '');
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
            if (str_starts_with($cleanPhone, '0')) {
              $cleanPhone = '62' . substr($cleanPhone, 1);
            }
          @endphp
          <tr>
            {{-- WAKTU TRANSAKSI --}}
            <td style="white-space: nowrap;">
              <strong>{{ $payment->created_at->format('d/m/Y') }}</strong>
              <div class="muted" style="font-size: 12px;">{{ $payment->created_at->format('H:i:s') }} WIB</div>
            </td>

            {{-- KODE PESANAN --}}
            <td style="white-space: nowrap;">
              <strong style="font-family: monospace; font-size: 13px;">{{ $payment->order?->order_code ?? '-' }}</strong>
              <div class="muted" style="font-size: 11.5px;">{{ $payment->method ?? 'QRIS Manual' }}</div>
            </td>

            {{-- IDENTITAS PEMBELI --}}
            <td>
              <div class="buyer-meta">
                <span class="buyer-name">{{ $payment->order?->pickup_contact_name ?: ($buyer?->name ?? 'Pembeli') }}</span>
                @if($buyer?->username)
                  <span class="buyer-user">{{ '@' . $buyer->username }}</span>
                @endif
                @if($phone)
                  <a class="buyer-wa" href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener" title="Kirim WhatsApp">
                    💬 {{ $phone }}
                  </a>
                @else
                  <span class="muted" style="font-size: 12px;">Tanpa nomor HP</span>
                @endif
              </div>
            </td>

            {{-- BARANG & KELAS --}}
            <td>
              <div class="items-mini-list">
                @if($payment->order && $payment->order->items->isNotEmpty())
                  @foreach($payment->order->items as $item)
                    <div class="item-mini-row">
                      <strong>{{ $item->product_name }}</strong> ({{ $item->quantity }})
                      <div class="item-class-badge">
                        {{ $item->class_name ?: ($item->product?->project?->class_name ?: 'Kelas') }}
                      </div>
                    </div>
                  @endforeach
                @else
                  <span class="muted">-</span>
                @endif
              </div>
            </td>

            {{-- TOTAL HARGA --}}
            <td style="white-space: nowrap;">
              <strong style="color: var(--ink); font-size: 14px;">
                {{ \App\Support\Money::format($payment->order?->total_price ?? 0) }}
              </strong>
              @if($payment->received_amount !== null && $payment->received_amount != $payment->order?->total_price)
                <div class="muted" style="font-size: 11px;">Masuk: {{ \App\Support\Money::format($payment->received_amount) }}</div>
              @endif
            </td>

            {{-- BUKTI PEMBAYARAN --}}
            <td style="white-space: nowrap;">
              @if($payment->proof_image)
                <button type="button" class="proof-thumbnail-btn" onclick="openLightbox('{{ route('admin.payments.proof', $payment) }}')">
                  <img class="proof-thumb-img" src="{{ route('admin.payments.proof', $payment) }}" alt="Bukti Transfer">
                  <span>Lihat SS ↗</span>
                </button>
              @else
                <span class="muted">— Belum ada —</span>
              @endif
            </td>

            {{-- STATUS --}}
            <td style="white-space: nowrap;">
              <span class="badge {{ $payment->status }}">
                {{ $paymentStatusLabels[$payment->status] ?? ucfirst($payment->status) }}
              </span>
              @if($payment->notes)
                <div class="muted" style="font-size: 11px; max-width: 130px; margin-top: 4px; overflow-wrap: anywhere;">
                  {{ $payment->notes }}
                </div>
              @endif
            </td>

            {{-- AKSI --}}
            <td>
              <div class="actions-cell">
                @if($payment->gateway === 'midtrans')
                  <span class="muted" style="font-size: 12px;">Midtrans Otomatis</span>
                @elseif($payment->status === 'pending')
                  <div class="btn-action-group">
                    <button type="button" class="btn btn-sm" onclick="openVerifyModal('{{ $payment->id }}', '{{ $payment->order?->order_code }}', '{{ $payment->order?->total_price }}')" style="padding: 5px 10px; font-size: 12px;">
                      ✓ Verifikasi
                    </button>
                    <button type="button" class="btn sec btn-sm" onclick="openRejectModal('{{ $payment->id }}', '{{ $payment->order?->order_code }}')" style="padding: 5px 10px; font-size: 12px; color: var(--bad); border-color: var(--bad);">
                      ✕ Tolak
                    </button>
                  </div>
                @else
                  <span class="muted" style="font-size: 12px;">Selesai</span>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center; padding: 36px;" class="muted">
              Tidak ada data pembayaran yang ditemukan.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="pagination">{{ $payments->links() }}</div>

{{-- MODAL UPLOAD / SET FOTO QRIS --}}
<div id="qrisModal" class="modal-backdrop-custom" style="display: none;">
  <div class="modal-card-custom" role="dialog" aria-modal="true" aria-labelledby="qrisModalTitle">
    <div class="modal-card-custom-head">
      <h3 id="qrisModalTitle">Pengaturan QRIS Pembayaran</h3>
      <button type="button" class="filter-modal-close" onclick="closeQrisModal()">&times;</button>
    </div>
    <form method="post" action="{{ Route::has('admin.payments.qris.update') ? route('admin.payments.qris.update') : url('/admin/payments/qris') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-card-custom-body">
        <div>
          <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Gambar QRIS Saat Ini:</label>
          @if($qrisExists)
            <div style="text-align: center; background: #fff; padding: 12px; border: 1px solid var(--line); border-radius: 8px;">
              <img src="{{ (Route::has('qris.image') ? route('qris.image') : url('/qris-image')) }}?v={{ time() }}" alt="QRIS Statis" style="max-height: 180px; max-width: 100%; object-fit: contain;">
            </div>
          @else
            <div class="note warn" style="margin: 0; padding: 10px 14px; font-size: 12px;">
              Belum ada gambar QRIS. Silakan unggah gambar barcode QRIS toko/sekolah di bawah ini.
            </div>
          @endif
        </div>

        <div>
          <label for="qris_image_input" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Unggah Foto QRIS Baru (PNG, JPG, maks. 5 MB):</label>
          <input class="input" id="qris_image_input" name="qris_image" type="file" accept=".png,.jpg,.jpeg" required>
        </div>
      </div>
      <div class="modal-card-custom-footer">
        <button type="button" class="btn sec btn-sm" onclick="closeQrisModal()">Batal</button>
        <button type="submit" class="btn btn-sm">Simpan Gambar QRIS</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL VERIFIKASI PEMBAYARAN --}}
<div id="verifyModal" class="modal-backdrop-custom" style="display: none;">
  <div class="modal-card-custom" role="dialog" aria-modal="true" aria-labelledby="verifyModalTitle">
    <div class="modal-card-custom-head">
      <h3 id="verifyModalTitle">Verifikasi Pembayaran</h3>
      <button type="button" class="filter-modal-close" onclick="closeVerifyModal()">&times;</button>
    </div>
    <form id="verifyForm" method="post" action="">
      @csrf
      <div class="modal-card-custom-body">
        <p style="margin: 0; font-size: 13.5px;" id="verifyPromptText">Konfirmasi verifikasi pembayaran untuk pesanan ini?</p>
        <div>
          <label for="received_amount_input" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Nominal Masuk Mutasi (Opsional):</label>
          <input class="input" id="received_amount_input" name="received_amount" type="number" min="0" placeholder="Biarkan kosong jika nominal pas">
        </div>
        <div>
          <label for="verify_notes_input" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Catatan Admin (Opsional):</label>
          <input class="input" id="verify_notes_input" name="notes" maxlength="255" placeholder="Contoh: Transfer atas nama Budi">
        </div>
      </div>
      <div class="modal-card-custom-footer">
        <button type="button" class="btn sec btn-sm" onclick="closeVerifyModal()">Batal</button>
        <button type="submit" class="btn btn-sm">Ya, Verifikasi Pembayaran</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL TOLAK PEMBAYARAN --}}
<div id="rejectModal" class="modal-backdrop-custom" style="display: none;">
  <div class="modal-card-custom" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle">
    <div class="modal-card-custom-head">
      <h3 id="rejectModalTitle">Tolak Pembayaran</h3>
      <button type="button" class="filter-modal-close" onclick="closeRejectModal()">&times;</button>
    </div>
    <form id="rejectForm" method="post" action="">
      @csrf
      <div class="modal-card-custom-body">
        <p style="margin: 0; font-size: 13.5px;" id="rejectPromptText">Tolak pembayaran pesanan ini? Pembeli dapat mengunggah bukti baru.</p>
        <div>
          <label for="reject_reason_input" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Alasan Penolakan <span style="color:var(--bad)">*</span>:</label>
          <input class="input" id="reject_reason_input" name="notes" maxlength="255" placeholder="Contoh: Bukti transfer tidak jelas / nominal tidak sesuai" required>
        </div>
      </div>
      <div class="modal-card-custom-footer">
        <button type="button" class="btn sec btn-sm" onclick="closeRejectModal()">Batal</button>
        <button type="submit" class="btn btn-sm" style="background: var(--bad); border-color: var(--bad);">Tolak Pembayaran</button>
      </div>
    </form>
  </div>
</div>

{{-- LIGHTBOX PREVIEW BUKTI TRANSFER --}}
<div id="lightboxModal" class="lightbox-modal" style="display: none;" onclick="closeLightbox(event)">
  <button type="button" class="lightbox-close" onclick="closeLightbox(event)">&times;</button>
  <img id="lightboxImg" class="lightbox-content" src="" alt="Bukti Transfer Penuh">
  <div style="margin-top: 14px;">
    <a id="lightboxNewTabBtn" class="btn sec btn-sm" href="" target="_blank" style="color: #fff; border-color: #fff;">Buka Gambar di Tab Baru ↗</a>
  </div>
</div>

<script>
  function openQrisModal() {
    document.getElementById('qrisModal').style.display = 'flex';
  }
  function closeQrisModal() {
    document.getElementById('qrisModal').style.display = 'none';
  }

  function openVerifyModal(paymentId, orderCode, totalPrice) {
    const form = document.getElementById('verifyForm');
    form.action = `/admin/payments/${paymentId}/verify`;
    document.getElementById('verifyPromptText').textContent = `Verifikasi pembayaran untuk pesanan ${orderCode}? Tiket QR pickup akan otomatis terbit.`;
    document.getElementById('received_amount_input').value = totalPrice || '';
    document.getElementById('verifyModal').style.display = 'flex';
  }
  function closeVerifyModal() {
    document.getElementById('verifyModal').style.display = 'none';
  }

  function openRejectModal(paymentId, orderCode) {
    const form = document.getElementById('rejectForm');
    form.action = `/admin/payments/${paymentId}/reject`;
    document.getElementById('rejectPromptText').textContent = `Tolak bukti pembayaran pesanan ${orderCode}? Pembeli akan diberitahu alasan dan dapat upload bukti baru.`;
    document.getElementById('reject_reason_input').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
  }
  function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
  }

  function openLightbox(imgUrl) {
    document.getElementById('lightboxImg').src = imgUrl;
    document.getElementById('lightboxNewTabBtn').href = imgUrl;
    document.getElementById('lightboxModal').style.display = 'flex';
  }
  function closeLightbox(e) {
    if (e.target.id === 'lightboxModal' || e.target.classList.contains('lightbox-close')) {
      document.getElementById('lightboxModal').style.display = 'none';
      document.getElementById('lightboxImg').src = '';
    }
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeQrisModal();
      closeVerifyModal();
      closeRejectModal();
      document.getElementById('lightboxModal').style.display = 'none';
    }
  });
</script>
@endsection

