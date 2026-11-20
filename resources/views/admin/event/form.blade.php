@extends('layouts.app')

@section('title', 'Kelola Jadwal PO & Acara — Admin FORTEXIV')

@section('content')
<style>
  .input.is-invalid, textarea.is-invalid {
    border: 2px solid var(--bad, #A8261F) !important;
    background-color: var(--bad-bg, #FADCD8) !important;
  }
  .field-error {
    color: var(--bad, #A8261F);
    font-size: 12px;
    font-weight: 700;
    display: block;
    margin-top: 4px;
  }
</style>

<div class="wrap">
  <div class="section-head">
    <div>
      <div class="eyebrow">PENGATURAN ACARA & JENDELA PRE-ORDER</div>
      <h1>Kelola Jadwal PO & Acara</h1>
      <p class="muted">Atur jendela PO bersama untuk semua kelas, batas waktu pembayaran, serta informasi pengambilan barang pada Hari H.</p>
    </div>
    <div>
      @php
        $statusBadge = match($window['status']) {
            'open' => ['class' => 'good', 'text' => 'PO Sedang Dibuka'],
            'closed' => ['class' => 'bad', 'text' => 'PO Telah Ditutup'],
            'upcoming' => ['class' => 'warn', 'text' => 'PO Akan Datang'],
            default => ['class' => 'info', 'text' => 'Belum Dikonfigurasi'],
        };
      @endphp
      <span class="badge {{ $statusBadge['class'] }}" style="font-size:14px;padding:6px 12px">{{ $statusBadge['text'] }}</span>
    </div>
  </div>

  @if($activeOrdersCount > 0)
    <div class="panel" style="margin-bottom:20px;border-left:4px solid var(--warn);background:var(--warn-bg);color:var(--warn)">
      <strong>Perhatian Operasional:</strong> Terdapat <strong>{{ $activeOrdersCount }} pesanan aktif</strong> di dalam sistem.
      Perubahan pada penutupan PO atau tanggal pengambilan Hari H akan mempengaruhi informasi yang tampil pada tiket pembeli dan tenggat produksi kelas.
    </div>
  @endif

  <form method="post" action="{{ route('admin.event.update') }}" id="event-form">
    @csrf
    @method('PUT')

    <section class="panel" style="margin-bottom:24px">
      <h2>1. Jendela Pre-Order (PO) Bersama</h2>
      <p class="muted">Semua kelas peserta memakai satu jendela PO bersama. Di luar waktu ini, sistem otomatis menolak pembuatan pesanan baru.</p>

      <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:16px">
        <div class="field">
          <label for="po_start"><strong>Waktu Pembukaan PO</strong></label>
          <input
            class="input @error('po_start') is-invalid @enderror"
            id="po_start"
            name="po_start"
            type="datetime-local"
            value="{{ old('po_start', $setting?->po_start ? $setting->po_start->format('Y-m-d\TH:i') : ($window['opensAt'] ? $window['opensAt']->format('Y-m-d\TH:i') : '')) }}"
            required
          >
          @error('po_start')
            <span class="field-error">{{ $message }}</span>
          @enderror
          <small class="muted">Format: Tanggal dan jam dimulainya transaksi baru.</small>
        </div>

        <div class="field">
          <label for="po_end"><strong>Waktu Penutupan PO</strong></label>
          <input
            class="input @error('po_end') is-invalid @enderror"
            id="po_end"
            name="po_end"
            type="datetime-local"
            value="{{ old('po_end', $setting?->po_end ? $setting->po_end->format('Y-m-d\TH:i') : ($window['closesAt'] ? $window['closesAt']->format('Y-m-d\TH:i') : '')) }}"
            required
          >
          @error('po_end')
            <span class="field-error">{{ $message }}</span>
          @enderror
          <small class="muted">Format: Batas akhir pembeli dapat checkout.</small>
        </div>
      </div>

      <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:8px">
        <span class="muted" style="align-self:center;font-size:13px">Aksi Cepat Waktu:</span>
        <button class="btn sm sec" type="button" onclick="quickSet('close')">Tutup PO Sekarang</button>
        <button class="btn sm sec" type="button" onclick="quickSet('open7')">Buka PO Mulai Sekarang (7 Hari)</button>
        <button class="btn sm sec" type="button" onclick="quickSet('extend3')">Perpanjang Penutupan (+3 Hari)</button>
      </div>
    </section>

    <section class="panel" style="margin-bottom:24px">
      <h2>2. Batas Bayar, Hari H, dan Informasi Pengambilan</h2>
      <p class="muted">Jadwal acara fisik dan petunjuk bagi pembeli saat mengambil pesanan di lokasi.</p>

      <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:16px">
        <div class="field">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <label for="payment_deadline" style="margin-bottom:0"><strong>Batas Waktu Pembayaran (Opsional)</strong></label>
            <button
              type="button"
              class="link-button"
              style="font-size:12px;color:var(--bad, #A8261F);font-weight:700"
              onclick="document.getElementById('payment_deadline').value = ''"
            >Kosongkan</button>
          </div>
          <input
            class="input @error('payment_deadline') is-invalid @enderror"
            id="payment_deadline"
            name="payment_deadline"
            type="datetime-local"
            value="{{ old('payment_deadline', $setting?->payment_deadline ? $setting->payment_deadline->format('Y-m-d\TH:i') : '') }}"
          >
          @error('payment_deadline')
            <span class="field-error">{{ $message }}</span>
          @enderror
          <small class="muted">Batas konfirmasi atau upload bukti transfer manual. Kosongkan jika tidak ada batas khusus.</small>
        </div>

        <div class="field">
          <label for="event_at"><strong>Waktu / Tanggal Hari H Acara</strong></label>
          <input
            class="input @error('event_at') is-invalid @enderror"
            id="event_at"
            name="event_at"
            type="datetime-local"
            value="{{ old('event_at', $setting?->event_at ? $setting->event_at->format('Y-m-d\TH:i') : ($window['eventAt'] ? $window['eventAt']->format('Y-m-d\TH:i') : '')) }}"
            required
          >
          @error('event_at')
            <span class="field-error">{{ $message }}</span>
          @enderror
          <small class="muted">Tanggal pengambilan fisik barang oleh pembeli.</small>
        </div>
      </div>

      <div class="field" style="margin-top:16px">
        <label for="event_location"><strong>Lokasi Acara / Pengambilan</strong></label>
        <input
          class="input @error('event_location') is-invalid @enderror"
          id="event_location"
          name="event_location"
          type="text"
          value="{{ old('event_location', $setting?->event_location ?? $window['location']) }}"
          placeholder="Contoh: Komplek Sekolah Alam Indonesia, Mampang, Pancoran Mas, Depok City, West Java 16433"
          maxlength="255"
          required
        >
        @error('event_location')
          <span class="field-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="field" style="margin-top:16px">
        <label for="pickup_instructions"><strong>Petunjuk Pengambilan Barang untuk Pembeli</strong></label>
        <textarea
          class="input @error('pickup_instructions') is-invalid @enderror"
          id="pickup_instructions"
          name="pickup_instructions"
          rows="3"
          placeholder="Tuliskan panduan alur antrean, verifikasi QR per stan kelas, dsb."
          maxlength="2000"
        >{{ old('pickup_instructions', $setting?->pickup_instructions ?? $window['pickupInstructions']) }}</textarea>
        @error('pickup_instructions')
          <span class="field-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="field" style="margin-top:16px">
        <label for="banner"><strong>Teks Pengumuman / Banner Beranda</strong></label>
        <input
          class="input @error('banner') is-invalid @enderror"
          id="banner"
          name="banner"
          type="text"
          value="{{ old('banner', $setting?->banner ?? $window['banner']) }}"
          placeholder="Satu periode PO untuk semua kelas. Semua pesanan diambil langsung di acara, tidak ada pengiriman."
          maxlength="255"
        >
        @error('banner')
          <span class="field-error">{{ $message }}</span>
        @enderror
      </div>
    </section>

    <section class="panel" style="margin-bottom:24px">
      <h2>3. Alasan Perubahan (Audit Trail)</h2>
      <p class="muted">Sesuai aturan bisnis FORTEXIV, setiap perubahan jadwal wajib menyertakan alasan yang dapat dipertanggungjawabkan.</p>

      <div class="field" style="margin-top:14px">
        <label for="change_reason"><strong>Alasan Perubahan Jadwal</strong> <span style="color:var(--bad)">*</span></label>
        <textarea
          class="input @error('change_reason') is-invalid @enderror"
          id="change_reason"
          name="change_reason"
          rows="2"
          placeholder="Contoh: Perpanjangan jadwal PO 3 hari atas keputusan musyawarah kelas & panitia."
          maxlength="500"
          required
        >{{ old('change_reason') }}</textarea>
        @error('change_reason')
          <span class="field-error">{{ $message }}</span>
        @enderror
        <small class="muted">Alasan ini akan tercatat permanen di riwayat audit admin.</small>
      </div>
    </section>

    <div style="display:flex;gap:12px;align-items:center;margin-bottom:36px">
      <button class="btn" type="submit">Simpan Pengaturan Jadwal PO</button>
      <a class="btn sec" href="{{ route('admin.dashboard') }}">Batal</a>
    </div>
  </form>

  <section class="panel" style="margin-top:32px">
    <h2>Riwayat Perubahan Jadwal PO (Audit Log)</h2>
    <p class="muted">Daftar rekam jejak pengaturan jadwal yang pernah diterapkan oleh tim admin.</p>

    <div class="table-wrap" style="margin-top:14px">
      <table>
        <thead>
          <tr>
            <th>Waktu Diubah</th>
            <th>Admin</th>
            <th>Periode PO</th>
            <th>Hari H</th>
            <th>Alasan Perubahan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($history as $item)
            <tr>
              <td>{{ $item->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td>
              <td><strong>{{ $item->updater?->name ?? 'Sistem / Setup Awal' }}</strong></td>
              <td>
                @if($item->po_start && $item->po_end)
                  {{ $item->po_start->format('d/m/y H:i') }} &ndash; {{ $item->po_end->format('d/m/y H:i') }}
                @else
                  <span class="muted">—</span>
                @endif
              </td>
              <td>{{ $item->event_at ? $item->event_at->format('d/m/Y') : '—' }}</td>
              <td style="max-width:320px;word-break:break-word">{{ $item->change_reason ?: '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5">Belum ada riwayat perubahan jadwal.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($history->hasPages())
      <div class="pagination" style="margin-top:14px">{{ $history->links() }}</div>
    @endif
  </section>
</div>

<script>
  function formatForInput(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
  }

  function syncDependentDates(newPoEndDate) {
    const paymentDeadlineInput = document.getElementById('payment_deadline');
    const eventAtInput = document.getElementById('event_at');

    if (paymentDeadlineInput && paymentDeadlineInput.value) {
      const currentDeadline = new Date(paymentDeadlineInput.value);
      if (!isNaN(currentDeadline.getTime()) && currentDeadline < newPoEndDate) {
        paymentDeadlineInput.value = formatForInput(newPoEndDate);
      }
    }

    if (eventAtInput && eventAtInput.value) {
      const currentEvent = new Date(eventAtInput.value);
      if (!isNaN(currentEvent.getTime()) && currentEvent < newPoEndDate) {
        const adjustedEvent = new Date(newPoEndDate.getTime() + 5 * 24 * 60 * 60 * 1000);
        eventAtInput.value = formatForInput(adjustedEvent);
      }
    }
  }

  function quickSet(action) {
    const now = new Date();
    const poStartInput = document.getElementById('po_start');
    const poEndInput = document.getElementById('po_end');
    const reasonInput = document.getElementById('change_reason');

    if (action === 'close') {
      const past = new Date(now.getTime() - 60000);
      poEndInput.value = formatForInput(past);
      reasonInput.value = 'Penutupan jendela PO oleh admin secara manual.';
    } else if (action === 'open7') {
      const future = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
      poStartInput.value = formatForInput(now);
      poEndInput.value = formatForInput(future);
      reasonInput.value = 'Pembukaan jendela PO selama 7 hari ke depan.';
      syncDependentDates(future);
    } else if (action === 'extend3') {
      let base = poEndInput.value ? new Date(poEndInput.value) : now;
      if (isNaN(base.getTime()) || base < now) {
        base = now;
      }
      const extended = new Date(base.getTime() + 3 * 24 * 60 * 60 * 1000);
      poEndInput.value = formatForInput(extended);
      reasonInput.value = 'Perpanjangan penutupan PO selama 3 hari tambahan.';
      syncDependentDates(extended);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
</script>
@endsection
