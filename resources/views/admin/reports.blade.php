@extends('layouts.app')

@section('title', 'Laporan Penjualan & Performa Kelas — FORTEXIV')

@section('content')
<div class="crumb">
  <a href="{{ route('admin.dashboard') }}">Dashboard</a> / <span>Laporan Penjualan</span>
</div>

<div class="section-head" style="margin-bottom: 20px; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
  <div>
    <div class="eyebrow">ADMINISTRASI PLATFORM</div>
    <h1>Laporan Penjualan & Performa Kelas</h1>
    <p class="muted" style="margin-top: 4px; font-size: 14px;">
      @if($preset === 'today')
        Periode: <strong>Hari Ini ({{ \Illuminate\Support\Carbon::now()->locale('id')->translatedFormat('d F Y') }})</strong>
      @elseif($preset === 'event')
        Periode: <strong>23 – 27 Nov 2026 (Pekan Acara & Bazar)</strong>
      @elseif($preset === 'all')
        Periode: <strong>Semua Waktu (Akumulasi Total)</strong>
      @else
        Periode: <strong>{{ $startDate ? \Illuminate\Support\Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y') : 'Awal' }} s/d {{ $endDate ? \Illuminate\Support\Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y') : 'Sekarang' }}</strong>
      @endif
      &middot; Pendapatan hanya menghitung pesanan yang telah diverifikasi (lunas).
    </p>
  </div>
</div>

{{-- FILTER PERIODE --}}
<div class="panel" style="margin-bottom: 24px; padding: 16px 20px;">
  <div style="display: flex; flex-wrap: wrap; gap: 18px; justify-content: space-between; align-items: center;">
    <div>
      <div style="font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
        Pilih Preset Cepat
      </div>
      <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <a href="{{ route('admin.reports', array_merge(request()->except(['date', 'start_date', 'end_date']), ['preset' => 'today'])) }}"
           class="btn {{ $preset === 'today' ? '' : 'sec' }} btn-small" style="font-size: 13px; text-decoration: none;">
          Hari Ini
        </a>
        <a href="{{ route('admin.reports', array_merge(request()->except(['date', 'start_date', 'end_date']), ['preset' => 'event'])) }}"
           class="btn {{ $preset === 'event' ? '' : 'sec' }} btn-small" style="font-size: 13px; text-decoration: none;">
          23 – 27 Nov (Acara)
        </a>
        <a href="{{ route('admin.reports', array_merge(request()->except(['date', 'start_date', 'end_date']), ['preset' => 'all'])) }}"
           class="btn {{ $preset === 'all' ? '' : 'sec' }} btn-small" style="font-size: 13px; text-decoration: none;">
          Semua Waktu
        </a>
      </div>
    </div>

    <div>
      <div style="font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
        Rentang Tanggal Kustom
      </div>
      <form method="get" action="{{ route('admin.reports') }}" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="preset" value="custom">
        @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
        @if($sort)<input type="hidden" name="sort" value="{{ $sort }}">@endif

        <label for="start_date" style="font-size: 12px; font-weight: 600; color: var(--muted);">Dari:</label>
        <input class="input" id="start_date" name="start_date" type="date" value="{{ $startDate }}" style="padding: 6px 10px; font-size: 13px; max-width: 155px;">

        <label for="end_date" style="font-size: 12px; font-weight: 600; color: var(--muted);">s/d</label>
        <input class="input" id="end_date" name="end_date" type="date" value="{{ $endDate }}" style="padding: 6px 10px; font-size: 13px; max-width: 155px;">

        <button class="btn btn-small" type="submit" style="padding: 6px 14px; font-size: 13px;">Terapkan</button>
      </form>
    </div>
  </div>
</div>

{{-- SECTION 1: KPI SUMMARY CARDS (PERIODE TERPILIH) --}}
<div class="stats" style="margin-bottom: 28px;">
  <div class="stat">
    <strong style="color: var(--good);">{{ \App\Support\Money::format($revenue) }}</strong>
    <span>Omzet (Periode Terpilih)</span>
  </div>
  <div class="stat">
    <strong>{{ $productsSold }} Item</strong>
    <span>Produk Terjual</span>
  </div>
  <div class="stat">
    <strong>{{ $paidOrders }} Pesanan</strong>
    <span>Pesanan Lunas ({{ $totalOrders }} Total)</span>
  </div>
  <div class="stat">
    @php
      $pickupRate = $totalUnits > 0 ? round(($deliveredUnits / $totalUnits) * 100) : 0;
    @endphp
    <strong>{{ $deliveredUnits }} / {{ $totalUnits }} Diambil</strong>
    <span>Tingkat Pickup ({{ $pickupRate }}%)</span>
  </div>
</div>

{{-- SECTION 2: TABEL RINCI PENJUALAN PER KELAS & PENCAIRAN --}}
<div class="panel" style="padding: 20px; margin-bottom: 28px;">
  <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 16px;">
    <div>
      <div class="eyebrow">REKAPITULASI PENJUALAN STAND KELAS</div>
      <h2 style="font-size: 1.3rem; margin: 4px 0 2px; font-weight: 700;">Laporan Omzet & Hak Pencairan per Kelas</h2>
      <p class="muted" style="font-size: 13px; margin: 0;">
        Pantau performa penjualan per stan kelas dan gunakan total omzet untuk audit pencairan dana ke pihak seller.
      </p>
    </div>

    {{-- SEARCH & SORT FILTER --}}
    <form method="get" action="{{ route('admin.reports') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
      <input type="hidden" name="preset" value="{{ $preset }}">
      @if($preset === 'custom')
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
      @endif

      <div style="min-width: 180px;">
        <input class="input" type="text" name="search" value="{{ $search }}" placeholder="Cari nama kelas / PIC / akun..." style="padding: 6px 12px; font-size: 13px; width: 100%;">
      </div>

      <div>
        <select class="input" name="sort" style="padding: 6px 10px; font-size: 13px;">
          <option value="revenue_desc" @selected($sort === 'revenue_desc')>Omzet Periode Tertinggi</option>
          <option value="revenue_asc" @selected($sort === 'revenue_asc')>Omzet Periode Terendah</option>
          <option value="qty_desc" @selected($sort === 'qty_desc')>Unit Terjual Terbanyak</option>
          <option value="all_time_desc" @selected($sort === 'all_time_desc')>Hak Pencairan Terbesar (All-Time)</option>
          <option value="name_asc" @selected($sort === 'name_asc')>Nomor / Nama Kelas</option>
        </select>
      </div>

      <button class="btn btn-small" type="submit" style="padding: 6px 14px; font-size: 13px;">Filter</button>
      @if($search || $sort !== 'revenue_desc')
        <a href="{{ route('admin.reports', ['preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn sec btn-small" style="padding: 6px 10px; font-size: 13px; text-decoration: none;" title="Reset Filter">✕</a>
      @endif
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 50px; text-align: center;">#</th>
          <th>Kelas</th>
          <th style="text-align: center;">Jml Produk</th>
          <th style="text-align: center;">Terjual (Periode)</th>
          <th style="text-align: right;">Omzet (Periode)</th>
          <th style="text-align: right; background: rgba(34, 197, 94, 0.05);">Hak Pencairan (All-Time)</th>
          <th style="text-align: center;">Status Pickup</th>
          <th style="text-align: center; width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($classesData as $index => $item)
          @php
            $proj = $item['project'];
            $seller = $item['seller'];
            $sellerUser = $seller?->user;
          @endphp
          <tr>
            <td style="text-align: center; font-weight: 700; color: var(--muted);">
              {{ $loop->iteration }}
            </td>
            <td>
              <div style="font-weight: 700; font-size: 14px;">{{ $proj->class_name }}</div>
              <div class="muted" style="font-size: 12px; margin-top: 2px;">
                id ({{ $proj->grade }}) | @if($sellerUser && $sellerUser->username)({{ '@' . $sellerUser->username }})@elseif($sellerUser)({{ $sellerUser->name }})@else<span style="font-style: italic;">(Belum ada akun)</span>@endif
              </div>
            </td>
            <td style="text-align: center;">
              <span class="badge" style="font-size: 12px;">{{ $item['products_count'] }} jenis</span>
            </td>
            <td style="text-align: center;">
              <strong>{{ $item['period_sold_units'] }} unit</strong>
            </td>
            <td style="text-align: right; font-weight: 600;">
              {{ \App\Support\Money::format($item['period_revenue']) }}
            </td>
            <td style="text-align: right; background: rgba(34, 197, 94, 0.05);">
              <strong style="color: var(--good); font-size: 15px;">
                {{ \App\Support\Money::format($item['all_time_revenue']) }}
              </strong>
              <div class="muted" style="font-size: 11px;">
                {{ $item['all_time_sold_units'] }} unit total
              </div>
            </td>
            <td style="text-align: center;">
              <span class="badge delivered" style="font-size: 11px;">{{ $item['delivered_units'] }} diambil</span>
              @if($item['pending_units'] > 0)
                <span class="badge waiting_verification" style="font-size: 11px; margin-left: 2px;">{{ $item['pending_units'] }} pending</span>
              @endif
            </td>
            <td style="text-align: center;">
              <a href="{{ route('admin.reports.class', $proj) }}" class="btn sec btn-small" style="font-size: 12px; padding: 5px 12px; text-decoration: none;" title="Lihat rincian produk dan status pencairan">
                🔍 Rincian Produk
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center; padding: 32px; color: var(--muted);">
              Tidak ada data kelas yang cocok dengan filter.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
