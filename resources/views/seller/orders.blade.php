@extends('layouts.app')

@section('title', 'Pesanan kelas — FORTEXIV')

@section('content')
<div class="wrap">
  <div class="pgh">
    <span class="eyebrow">PORTAL KELAS</span>
    <h1>Pesanan untuk kelasmu</h1>
    <p>Pantau seluruh pesanan masuk dan verifikasi serah terima produk di booth kelas.</p>
    <a class="btn" href="{{ route('seller.pickup.scan') }}">📷 Buka Scanner QR</a>
  </div>

  {{-- FORM FILTER --}}
  <div class="panel" style="margin-bottom: 20px; padding: 16px 20px;">
    <form method="get" action="{{ route('seller.orders') }}" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
      <div style="flex: 2; min-width: 220px;">
        <label for="filter_search" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Cari Pembeli / Pesanan</label>
        <input class="input" id="filter_search" name="search" type="text" value="{{ request('search') }}" placeholder="Nama, no. kontak, atau kode FX-...">
      </div>

      <div style="flex: 1; min-width: 170px;">
        <label for="filter_status" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Status Pesanan</label>
        <select class="input" id="filter_status" name="status">
          <option value="">Semua Status Pesanan</option>
          <option value="paid" @selected(request('status') === 'paid')>Siap diambil</option>
          <option value="partially_picked_up" @selected(request('status') === 'partially_picked_up')>Sebagian diserahkan</option>
          <option value="picked_up" @selected(request('status') === 'picked_up')>Selesai diambil</option>
          <option value="waiting_verification" @selected(request('status') === 'waiting_verification')>Menunggu verifikasi</option>
          <option value="pending_payment" @selected(request('status') === 'pending_payment')>Menunggu pembayaran</option>
          <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 170px;">
        <label for="filter_product_status" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Status Produk</label>
        <select class="input" id="filter_product_status" name="product_status">
          <option value="">Semua Status Produk</option>
          <option value="pending" @selected(request('product_status') === 'pending')>Belum Diambil (Pending)</option>
          <option value="delivered" @selected(request('product_status') === 'delivered')>Sudah Diserahkan (Delivered)</option>
        </select>
      </div>

      <div style="display: flex; gap: 8px;">
        <button class="btn" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'status', 'product_status']))
          <a class="btn sec" href="{{ route('seller.orders') }}">Reset</a>
        @endif
      </div>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Pembeli</th>
          <th>Nomor Pembeli</th>
          <th>Produk</th>
          <th>Jumlah</th>
          <th>Pesanan</th>
          <th>Pembayaran</th>
          <th>Status pesanan</th>
          <th>Status produk</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          @php
            $deliveredCount = $item->units->where('status', 'delivered')->count();
            $buyerPhone = $item->order->pickup_contact_phone ?: ($item->order->buyer?->phone ?: '—');
            $cleanPhone = preg_replace('/[^0-9]/', '', $buyerPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $waPhone = '62' . substr($cleanPhone, 1);
            } else {
                $waPhone = $cleanPhone;
            }
          @endphp
          <tr>
            <td>
              <strong>{{ $item->order->pickup_contact_name ?: ($item->order->buyer?->name ?? 'Pembeli') }}</strong>
              @if($item->order->pickup_contact_name && $item->order->buyer && $item->order->pickup_contact_name !== $item->order->buyer->name)
                <div class="muted small">Akun: {{ $item->order->buyer->name }}</div>
              @endif
            </td>
            <td>
              @if($buyerPhone !== '—' && filled($cleanPhone))
                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" style="text-decoration: underline; color: inherit;">
                  {{ $buyerPhone }} ↗
                </a>
              @else
                <span class="muted">{{ $buyerPhone }}</span>
              @endif
            </td>
            <td><strong>{{ $item->product_name }}</strong></td>
            <td>{{ $item->quantity }}</td>
            <td><strong>{{ $item->order->order_code }}</strong></td>
            <td>
              <span class="badge {{ $item->order->payment?->status ?? 'pending' }}">
                {{ ['pending' => 'Menunggu bukti', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$item->order->payment?->status ?? 'pending'] ?? 'Belum dikirim' }}
              </span>
            </td>
            <td>
              <span class="badge {{ $item->order->status }}">
                {{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$item->order->status] ?? $item->order->status }}
              </span>
            </td>
            <td>
              <span class="badge {{ $deliveredCount === $item->quantity ? 'delivered' : ($deliveredCount > 0 ? 'partially_delivered' : 'pending') }}">
                {{ $deliveredCount }} / {{ $item->quantity }} Delivered
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center; padding: 24px; color: var(--muted);">
              @if(request()->hasAny(['search', 'status', 'product_status']))
                Tidak ada pesanan yang cocok dengan kriteria filter.
              @else
                Pesanan untuk produk kelasmu akan muncul di sini.
              @endif
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $items->links() }}</div>
</div>
@endsection
