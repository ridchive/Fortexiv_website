@extends('layouts.app')

@section('title', 'Panel penjual — FORTEXIV')

@section('content')
@php
    $orderLabels = [
        'pending_payment' => 'Menunggu pembayaran',
        'waiting_verification' => 'Menunggu verifikasi',
        'paid' => 'Siap diambil',
        'partially_picked_up' => 'Sebagian diserahkan',
        'picked_up' => 'Sudah diambil',
        'cancelled' => 'Dibatalkan',
    ];

    $window = app(\App\Support\PurchaseWindow::class)->details();
    $statusClasses = [
        'open' => 'good',
        'closed' => 'bad',
        'upcoming' => 'warn',
        'not_configured' => 'info',
    ];
    $statusLabels = [
        'open' => 'PO Sedang Dibuka',
        'closed' => 'PO Telah Ditutup',
        'upcoming' => 'PO Akan Datang',
        'not_configured' => 'Belum Dikonfigurasi',
    ];
@endphp

<div class="section-head" style="margin-bottom: 16px;">
  <div>
    <div class="eyebrow">PORTAL PENJUAL & KELAS</div>
    <h1>Dashboard Penjual — {{ $seller->store_name }}</h1>
    <p class="muted" style="margin-top: 4px; font-size: 14px;">Kelola katalog produk kelas, pantau pesanan pre-order, dan serah terima karya siswa di Hari H.</p>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a class="btn" href="{{ route('seller.products.create') }}">+ Tambah Produk</a>
  </div>
</div>

@if(session('status'))
  <div class="flash" role="status" style="margin-bottom: 20px;">{{ session('status') }}</div>
@endif

@if($errors->any())
  <div class="note bad" style="margin-bottom: 20px;">
    <strong>Terjadi kesalahan:</strong>
    <ul style="margin: 6px 0 0; padding-left: 20px;">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="panel" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
  <div>
    <div class="eyebrow">STATUS JENDELA PRE-ORDER (PO)</div>
    <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
      <span class="badge {{ $statusClasses[$window['status']] ?? 'info' }}">{{ $statusLabels[$window['status']] ?? ucfirst($window['status']) }}</span>
      <span style="font-weight: 600;">
        @if($window['opensAt'] && $window['closesAt'])
          {{ $window['opensAt']->format('d M Y H:i') }} — {{ $window['closesAt']->format('d M Y H:i') }}
        @else
          Jadwal belum disetel oleh admin
        @endif
      </span>
    </div>
    <div class="muted" style="font-size: 13px; margin-top: 6px;">
      Jadwal Pengambilan: {{ $window['eventAt'] ? $window['eventAt']->format('d M Y H:i') : '—' }} &middot; Lokasi: {{ $window['location'] ?? 'Belum disetel' }}
    </div>
  </div>
  <div>
    @if($seller->project)
      <a class="btn sec" href="{{ route('projects.show', $seller->project) }}" target="_blank" rel="noopener">Lihat Halaman Publik Kelas ↗</a>
    @endif
  </div>
</div>

<div class="panel" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
  <div>
    <div class="eyebrow">KATALOG & KARYA SISWA</div>
    <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Kelola Produk & Karya Kelas</div>
    <div class="muted" style="font-size: 13px; margin-top: 4px;">Atur nama produk, foto, harga, stok, dan ajukan draf ke admin untuk ditinjau sebelum terbit di katalog.</div>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a class="btn sec" href="{{ route('seller.products.status') }}">📊 Status & Rekap Produk</a>
    <a class="btn sec" href="{{ route('seller.products.index') }}">Daftar Produk ({{ $productsCount }})</a>
    <a class="btn" href="{{ route('seller.products.create') }}">+ Tambah Produk</a>
  </div>
</div>

<div class="panel" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
  <div>
    <div class="eyebrow">OPERASIONAL SERAH TERIMA (HARI H)</div>
    <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Scan QR & Penyerahan Pesanan Pembeli</div>
    <div class="muted" style="font-size: 13px; margin-top: 4px;">Pindai QR pembeli (Class QR per kelompok pesanan kelas atau Product QR per unit) saat serah terima di stan kelas.</div>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a class="btn sec" href="{{ route('seller.orders') }}">Pesanan & Pembeli</a>
    <a class="btn lime" href="{{ route('seller.pickup.scan') }}">📷 Buka Scanner QR ➔</a>
  </div>
</div>

<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total pesanan masuk</span></div>
  <div class="stat"><strong>{{ $items->where('order.status', 'waiting_verification')->count() }}</strong><span>Menunggu verifikasi pembayaran</span></div>
  <div class="stat"><strong>{{ $productsCount }}</strong><span>Total produk kelas</span></div>
  <div class="stat"><strong>{{ $pendingCount }}</strong><span>Produk menunggu review admin</span></div>
</div>

<h2 style="margin-top: 32px; margin-bottom: 14px;">Pesanan terbaru untuk kelas ini</h2>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Nomor pesanan</th>
        <th>Pembeli</th>
        <th>Produk</th>
        <th>Jumlah</th>
        <th>Waktu</th>
        <th>Status pesanan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $item)
        <tr>
          <td><strong>{{ $item->order->order_code }}</strong></td>
          <td>{{ $item->order->buyer?->name ?? '—' }}</td>
          <td>{{ $item->product_name }}</td>
          <td>{{ $item->quantity }} unit</td>
          <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '—' }}</td>
          <td><span class="badge {{ $item->order->status }}">{{ $orderLabels[$item->order->status] ?? $item->order->status }}</span></td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center; padding: 24px; color: var(--muted);">Pesanan untuk produk kelas Anda akan ditampilkan di sini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
