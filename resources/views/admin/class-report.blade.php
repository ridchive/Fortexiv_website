@extends('layouts.app')

@section('title', 'Rincian Penjualan ' . $project->class_name . ' — FORTEXIV')

@section('content')
<div class="crumb">
  <a href="{{ route('admin.dashboard') }}">Dashboard</a> / 
  <a href="{{ route('admin.reports') }}">Laporan Penjualan</a> / 
  <span>{{ $project->class_name }}</span>
</div>

<div class="section-head" style="margin-bottom: 24px; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
  <div>
    <div class="eyebrow">AUDIT PENJUALAN & PENCAIRAN DANA KELAS</div>
    <h1>Rincian Penjualan: {{ $project->class_name }}</h1>
    <p class="muted" style="margin-top: 4px; font-size: 14px;">
      Stan: <strong>{{ $seller?->store_name ?? '—' }}</strong> &middot; 
      PIC: <strong>{{ $seller?->user?->name ?? '—' }}</strong> 
      @if($seller?->user?->username)
        ({{ '@' . $seller->user->username }})
      @endif
      &middot; Kontak: {{ $seller?->user?->email ?? '—' }}
    </p>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a class="btn sec" href="{{ route('admin.reports') }}">&larr; Kembali ke Laporan</a>
    <button class="btn" type="button" onclick="window.print()">🖨️ Cetak Rekap</button>
  </div>
</div>

{{-- KPI SUMMARY KELAS --}}
<div class="stats" style="margin-bottom: 24px;">
  <div class="stat" style="border-left: 4px solid var(--good);">
    <strong style="color: var(--good); font-size: 1.6rem;">{{ \App\Support\Money::format($totalRevenue) }}</strong>
    <span>Hak Pencairan Dana (Total Omzet)</span>
  </div>
  <div class="stat">
    <strong>{{ $totalSold }} unit</strong>
    <span>Total Karya Terjual</span>
  </div>
  <div class="stat">
    <strong style="color: var(--good);">{{ $totalDelivered }} unit</strong>
    <span>Sudah Diambil (Delivered)</span>
  </div>
  <div class="stat">
    <strong style="color: {{ $totalPending > 0 ? '#f59e0b' : 'var(--text)' }};">{{ $totalPending }} unit</strong>
    <span>Belum Diambil (Pending)</span>
  </div>
</div>

{{-- PANEL AUDIT PENCAIRAN --}}
<div class="panel" style="margin-bottom: 24px; padding: 18px 20px; background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2);">
  <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap; justify-content: space-between;">
    <div>
      <div style="font-weight: 700; font-size: 14px; color: var(--good);">
        ✓ Catatan Pencairan Dana untuk {{ $project->class_name }}
      </div>
      <div class="muted" style="font-size: 13px; margin-top: 2px;">
        Total dana yang wajib diserahterimakan ke seller kelas ini adalah <strong>{{ \App\Support\Money::format($totalRevenue) }}</strong> dari <strong>{{ $totalSold }} unit</strong> produk yang telah lunas diverifikasi.
      </div>
    </div>
    <div style="font-size: 12px; color: var(--muted);">
      {{ $productsWithStats->count() }} jenis produk terdaftar
    </div>
  </div>
</div>

{{-- TABEL RINCIAN PRODUK (PERSIS SELLER PRODUCT STATUS TAB TOTALS) --}}
<div class="table-wrap" style="margin-bottom: 30px;">
  <table>
    <thead>
      <tr>
        <th>Produk</th>
        <th>Kategori</th>
        <th style="text-align: right;">Harga Satuan</th>
        <th style="text-align: center;">Sisa Stok</th>
        <th style="text-align: center;">Terjual</th>
        <th style="text-align: center;">Delivered</th>
        <th style="text-align: center;">Belum Diambil</th>
        <th style="text-align: right;">Total Omzet</th>
        <th style="text-align: center;">Status Publikasi</th>
        <th style="text-align: center;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($productsWithStats as $item)
        @php
          $p = $item['product'];
        @endphp
        <tr>
          <td>
            <div style="display: flex; align-items: center; gap: 12px;">
              @if($p->image)
                <img src="{{ Storage::disk('public')->url($p->image) }}" alt="{{ $p->name }}" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;">
              @else
                <div style="width: 44px; height: 44px; border-radius: 6px; background: var(--soft); display: flex; align-items: center; justify-content: center; font-size: 18px;">📦</div>
              @endif
              <div>
                <div style="font-weight: 700;">{{ $p->name }}</div>
                <div class="muted" style="font-size: 12px;">ID: #{{ $p->id }}</div>
              </div>
            </div>
          </td>
          <td>{{ $p->category?->name ?? '—' }}</td>
          <td style="text-align: right;">{{ \App\Support\Money::format($p->price) }}</td>
          <td style="text-align: center;">
            @if($p->stock > 0)
              <span class="badge open">{{ $p->stock }} unit</span>
            @else
              <span class="badge closed">Habis</span>
            @endif
          </td>
          <td style="text-align: center;"><strong>{{ $item['sold_units'] }} unit</strong></td>
          <td style="text-align: center;"><span class="badge delivered">{{ $item['delivered_units'] }} unit</span></td>
          <td style="text-align: center;"><span class="badge {{ $item['pending_units'] > 0 ? 'waiting_verification' : 'paid' }}">{{ $item['pending_units'] }} unit</span></td>
          <td style="text-align: right;"><strong style="color: var(--good);">{{ \App\Support\Money::format($item['revenue']) }}</strong></td>
          <td style="text-align: center;">
            @if($p->status === 'published')
              <span class="badge open">Tayang</span>
            @else
              <span class="badge {{ $p->review_status === 'pending_review' ? 'waiting_verification' : 'closed' }}">
                {{ ['draft' => 'Draf', 'pending_review' => 'Menunggu Review', 'revision_requested' => 'Revisi', 'rejected' => 'Ditolak'][$p->review_status] ?? $p->status }}
              </span>
            @endif
          </td>
          <td style="text-align: center;">
            <a class="btn sec btn-small" href="{{ route('products.show', $p->slug) }}" target="_blank" style="font-size: 12px; padding: 4px 10px; text-decoration: none;">
              Lihat ↗
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="10" style="text-align: center; padding: 28px; color: var(--muted);">
            Belum ada produk yang didaftarkan untuk kelas ini.
          </td>
        </tr>
      @endforelse
    </tbody>
    @if($productsWithStats->isNotEmpty())
      <tfoot>
        <tr style="font-weight: 700; border-top: 2px solid var(--line); background: var(--soft);">
          <td colspan="4" style="text-align: right;">TOTAL:</td>
          <td style="text-align: center;">{{ $totalSold }} unit</td>
          <td style="text-align: center;">{{ $totalDelivered }} unit</td>
          <td style="text-align: center;">{{ $totalPending }} unit</td>
          <td style="text-align: right; color: var(--good); font-size: 15px;">{{ \App\Support\Money::format($totalRevenue) }}</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    @endif
  </table>
</div>
@endsection
