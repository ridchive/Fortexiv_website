@extends('layouts.app')

@section('title', 'Admin dashboard — FORTEXIV')

@section('content')
@php
    $paymentLabels = [
        'pending' => 'Menunggu verifikasi',
        'verified' => 'Lunas',
        'rejected' => 'Ditolak',
    ];
    $orderLabels = [
        'pending_payment' => 'Belum bayar',
        'waiting_verification' => 'Menunggu verifikasi',
        'paid' => 'Lunas',
        'partially_picked_up' => 'Sebagian diambil',
        'picked_up' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $activeTab = request('tab') === 'classes' ? 'classes' : 'orders';
@endphp

<div class="section-head" style="margin-bottom: 16px;">
  <div>
    <div class="eyebrow">ADMINISTRASI PLATFORM</div>
    <h1>Dashboard admin</h1>
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


  @php
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

  <div class="dashboard-actions-grid">
  <div class="panel" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
      <div class="eyebrow">STATUS JENDELA PRE-ORDER (PO)</div>
      <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
        <span class="badge {{ $statusClasses[$window['status']] ?? 'info' }}">{{ $statusLabels[$window['status']] ?? ucfirst($window['status']) }}</span>
        <span style="font-weight: 600;">
          @if($window['opensAt'] && $window['closesAt'])
            {{ $window['opensAt']->format('d M Y H:i') }} — {{ $window['closesAt']->format('d M Y H:i') }}
          @else
            Jadwal belum disetel
          @endif
        </span>
      </div>
      <div class="muted" style="font-size: 13px; margin-top: 6px;">
        Pengambilan: {{ $window['eventAt'] ? $window['eventAt']->format('d M Y') : '—' }} &middot; Lokasi: {{ $window['location'] ?? 'Belum disetel' }}
      </div>
    </div>
    <div>
      <a class="btn" href="{{ route('admin.event.edit') }}">Kelola Jadwal PO</a>
    </div>
  </div>

  <div class="panel" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
      <div class="eyebrow">STRUKTUR TINGKAT & AKUN KELAS</div>
      <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Kelola Level Kelas & Akun Seller</div>
      <div class="muted" style="font-size: 13px; margin-top: 4px;">Atur 11 level kelas, nama kelas, serta pembuatan akun login seller (username & password) untuk akses portal seller.</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <button class="btn" type="button" onclick="switchAdminTab('classes')">Buka Tab Kelola Kelas ➔</button>
    </div>
  </div>

  <div class="panel" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
      <div class="eyebrow">KONTEN EDITORIAL PROYEK KELAS</div>
      <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Kelola Cerita & Artikel Proyek Siswa</div>
      <div class="muted" style="font-size: 13px; margin-top: 4px;">Tulis latar belakang, proses berkarya, dan hubungkan dengan produk kelas yang dapat dipesan pembeli.</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a class="btn sec" href="{{ route('admin.articles.index') }}">Daftar Artikel</a>
      <a class="btn" href="{{ route('admin.articles.create') }}">+ Buat Artikel Baru</a>
    </div>
  </div>

  <div class="panel" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
      <div class="eyebrow">KONTEN STATIS</div>
      <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Terms of Service</div>
      <div class="muted" style="font-size: 13px; margin-top: 4px;">Kelola draft, pratinjau, publikasi, dan riwayat versi ketentuan.</div>
    </div>
    <a class="btn" href="{{ route('admin.terms.edit') }}">Kelola Terms of Service</a>
  </div>

  <div class="panel" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
      <div class="eyebrow">GREEN ECOSYSTEM IMPACT TRACKER (GEIT)</div>
      <div style="font-weight: 700; font-size: 1.15rem; margin-top: 4px;">Artikel Dampak Lingkungan & Banner Hero Beranda</div>
      <div class="muted" style="font-size: 13px; margin-top: 4px;">Atur foto header & background Hero serta narasi artikel (.md). Kontribusi aktual dihitung dari jejak karbon produk pada pesanan berbayar.</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a class="btn" href="{{ route('impact.index') }}">Buka Halaman & Editor GEIT ↗</a>
    </div>
  </div>
  </div>

  <div class="stats">
    <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total pesanan</span></div>
    <div class="stat"><strong>{{ $pendingPayments }}</strong><span>Pembayaran menunggu verifikasi</span></div>
    <div class="stat"><strong>{{ $buyersCount }}</strong><span>Akun pembeli</span></div>
    <div class="stat"><strong>{{ $productsSold }}</strong><span>Produk terjual</span></div>
  </div>

  <section class="panel" style="margin-top: 24px;">
    <div class="section-head" style="margin-bottom: 16px;">
      <div>
        <div class="eyebrow">CARBON EMISSION AVOIDED DASHBOARD</div>
        <h2>Dampak emisi dari transaksi terverifikasi</h2>
      </div>
    </div>

    <div class="stats">
      <div class="stat"><strong>{{ number_format($carbonSummary['total_avoided_emissions'], 6, ',', '.') }} kg CO2e</strong><span>Total emisi dihindari</span></div>
      <div class="stat"><strong>{{ number_format($carbonSummary['total_additional_emissions'], 6, ',', '.') }} kg CO2e</strong><span>Total emisi tambahan</span></div>
      <div class="stat"><strong>{{ number_format($carbonSummary['net_avoided_emissions'], 6, ',', '.') }} kg CO2e</strong><span>Net emisi dihindari</span></div>
      <div class="stat"><strong>{{ $carbonSummary['eligible_item_count'] }}</strong><span>Item pesanan eligible</span></div>
      <div class="stat"><strong>{{ $carbonSummary['missing_snapshot_count'] }}</strong><span>Snapshot data bermasalah</span></div>
      <div class="stat"><strong>{{ $carbonSummary['adjustment_count'] }}</strong><span>Total koreksi tercatat</span></div>
    </div>

    @if($carbonSummary['missing_factor_count'] > 0 || $carbonSummary['legacy_snapshot_count'] > 0 || $carbonSummary['unit_mismatch_count'] > 0)
      <div class="note warn" style="margin-top: 12px;">
        Ditemukan
        {{ $carbonSummary['missing_factor_count'] }} item pembayaran terverifikasi dengan faktor belum lengkap,
        {{ $carbonSummary['legacy_snapshot_count'] }} item legacy tanpa snapshot CEA,
        dan {{ $carbonSummary['unit_mismatch_count'] }} snapshot unit tidak sesuai.
        Periksa data sumber dan rekonsiliasi sebelum memublikasikan dampak.
      </div>
    @endif

    @if($carbonSummary['incomplete_items']->isNotEmpty())
      <div class="table-wrap" style="margin-top: 16px;">
        <table>
          <thead><tr><th>Pesanan</th><th>Pembeli</th><th>Item</th><th>Snapshot</th><th>Status perhitungan</th></tr></thead>
          <tbody>
            @foreach($carbonSummary['incomplete_items'] as $item)
              <tr>
                <td>{{ $item->order_code }}</td>
                <td>{{ $item->buyer_name }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->carbon_snapshot_source ?? 'Tidak tersedia' }}</td>
                <td>Faktor, sumber, unit fungsional, atau hasil belum lengkap</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        @if($carbonSummary['missing_snapshot_count'] > $carbonSummary['incomplete_items']->count())
          <p class="muted">Menampilkan 50 catatan terbaru dari {{ $carbonSummary['missing_snapshot_count'] }} item yang belum lengkap.</p>
        @endif
      </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-top: 16px;">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Produk</th><th>Dihindari</th><th>Tambahan</th><th>Net</th></tr></thead>
          <tbody>
            @forelse($carbonSummary['product_breakdown'] as $row)
              <tr><td>{{ $row->product_name }}</td><td>{{ number_format($row->total_avoided_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->total_additional_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->net_avoided_emissions, 6, ',', '.') }} kg CO2e</td></tr>
            @empty
              <tr><td colspan="4" class="muted">Belum ada perhitungan terverifikasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Kelas</th><th>Dihindari</th><th>Tambahan</th><th>Net</th></tr></thead>
          <tbody>
            @forelse($carbonSummary['project_breakdown'] as $row)
              <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_avoided_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->total_additional_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->net_avoided_emissions, 6, ',', '.') }} kg CO2e</td></tr>
            @empty
              <tr><td colspan="4" class="muted">Belum ada perhitungan terverifikasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-wrap">
        <table>
          <thead><tr><th>Seller / Kelas</th><th>Dihindari</th><th>Tambahan</th><th>Net</th></tr></thead>
          <tbody>
            @forelse($carbonSummary['seller_breakdown'] as $row)
              <tr>
                <td>{{ $row->name }}@if($row->class_name)<br><span class="muted">{{ $row->class_name }}</span>@endif</td>
                <td>{{ number_format($row->total_avoided_emissions, 6, ',', '.') }}</td>
                <td>{{ number_format($row->total_additional_emissions, 6, ',', '.') }}</td>
                <td>{{ number_format($row->net_avoided_emissions, 6, ',', '.') }} kg CO2e</td>
              </tr>
            @empty
              <tr><td colspan="4" class="muted">Belum ada perhitungan terverifikasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-wrap">
        <table>
          <thead><tr><th>Pembeli</th><th>Dihindari</th><th>Tambahan</th><th>Net</th></tr></thead>
          <tbody>
            @forelse($carbonSummary['buyer_breakdown'] as $row)
              <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_avoided_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->total_additional_emissions, 6, ',', '.') }}</td><td>{{ number_format($row->net_avoided_emissions, 6, ',', '.') }} kg CO2e</td></tr>
            @empty
              <tr><td colspan="4" class="muted">Belum ada perhitungan terverifikasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <h2 id="all-orders" style="margin-top: 32px; margin-bottom: 14px;">Daftar pesanan</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nomor pesanan</th>
          <th>Nama pembeli</th>
          <th>Total</th>
          <th>Status Pembayaran</th>
          <th>Status pesanan pembeli</th>
          <th>Grup Kelas</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td>{{ $order->order_code }}</td>
            <td>{{ $order->buyer?->name ?? '—' }}</td>
            <td>{{ \App\Support\Money::format($order->total_price) }}</td>
            <td><span class="badge {{ $order->payment?->status ?? 'pending' }}">{{ $paymentLabels[$order->payment?->status ?? 'pending'] ?? ucfirst(str_replace('_', ' ', $order->payment?->status ?? 'pending')) }}</span></td>
            <td><span class="badge {{ $order->status }}">{{ $orderLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
            <td>
              @forelse($order->items->groupBy(fn ($item) => $item->seller->project?->class_name ?? $item->seller->store_name ?? 'Umum') as $groupName => $groupItems)
                <div class="group-status">
                  <strong>{{ $groupName }}</strong>
                  <span>{{ $paymentLabels[$order->payment?->status ?? 'pending'] ?? 'Menunggu verif' }}</span>
                </div>
              @empty
                <span>—</span>
              @endforelse
            </td>
          </tr>
        @empty
          <tr><td colspan="6">Belum ada order.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <h2 style="margin-top: 32px; margin-bottom: 14px;">Pembayaran menunggu verifikasi</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Pesanan</th>
          <th>Pembeli</th>
          <th>Total</th>
          <th>Dikirim</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          <tr>
            <td>{{ $payment->order->order_code }}</td>
            <td>{{ $payment->order->buyer->name }}</td>
            <td>{{ \App\Support\Money::format($payment->order->total_price) }}</td>
            <td>{{ $payment->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td>
            <td><a href="{{ route('admin.payments') }}">Tinjau</a></td>
          </tr>
        @empty
          <tr><td colspan="5">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
