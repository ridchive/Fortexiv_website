@extends('layouts.app')

@section('title', 'Emisi Karbon — Admin FORTEXIV')

@section('content')
  <div class="section-head" style="margin-bottom: 16px;">
    <div>
      <div class="eyebrow">CARBON EMISSION AVOIDED DASHBOARD</div>
      <h1>Emisi Karbon</h1>
    </div>
  </div>

  <section class="panel">
    <div class="section-head" style="margin-bottom: 16px;">
      <div>
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

    @php
      $carbonProductBreakdown = $carbonSummary['product_breakdown']->keyBy('product_id');
    @endphp

    <div class="section-head" style="margin: 24px 0 12px; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
      <div>
        <div class="eyebrow">FAKTOR EMISI PRODUK</div>
        <h3 style="margin: 4px 0 0;">Pengaturan Faktor Emisi per Produk</h3>
        <p class="muted" style="font-size: 13px; margin: 4px 0 0;">Isi pengurangan karbon per produk dalam kg CO2e. Nilai ini otomatis disinkronkan ke seluruh pesanan yang sudah lunas/terverifikasi.</p>
      </div>
    </div>

    {{-- TOOLBAR FILTER & SEARCH --}}
    <div style="background: var(--soft); border: 1px solid var(--line); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 16px;">
      {{-- QUICK FILTER TABS --}}
      <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--line);">
        <span style="font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-right: 4px;">Status:</span>
        <a href="{{ route('admin.carbon-emissions', array_merge(request()->except(['status_filter']), ['status_filter' => 'all'])) }}"
           class="btn {{ $statusFilter === 'all' ? '' : 'sec' }} sm" style="font-size: 12px; text-decoration: none; padding: 4px 10px;">
          Semua ({{ $totalCount }})
        </a>
        <a href="{{ route('admin.carbon-emissions', array_merge(request()->except(['status_filter']), ['status_filter' => 'unset'])) }}"
           class="btn {{ $statusFilter === 'unset' ? '' : 'sec' }} sm" style="font-size: 12px; text-decoration: none; padding: 4px 10px; color: {{ $statusFilter === 'unset' ? '' : '#f59e0b' }};">
          ⚠️ Belum Di-set ({{ $unsetCount }})
        </a>
        <a href="{{ route('admin.carbon-emissions', array_merge(request()->except(['status_filter']), ['status_filter' => 'set'])) }}"
           class="btn {{ $statusFilter === 'set' ? '' : 'sec' }} sm" style="font-size: 12px; text-decoration: none; padding: 4px 10px; color: {{ $statusFilter === 'set' ? '' : 'var(--good)' }};">
          ✓ Sudah Di-set ({{ $setCount }})
        </a>
        <a href="{{ route('admin.carbon-emissions', array_merge(request()->except(['status_filter']), ['status_filter' => 'sold'])) }}"
           class="btn {{ $statusFilter === 'sold' ? '' : 'sec' }} sm" style="font-size: 12px; text-decoration: none; padding: 4px 10px;">
          📦 Ada Penjualan ({{ $soldCount }})
        </a>
      </div>

      {{-- FORM FILTER SEARCH, KELAS & SORT --}}
      <form method="get" action="{{ route('admin.carbon-emissions') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <input type="hidden" name="status_filter" value="{{ $statusFilter }}">

        <div style="flex: 1; min-width: 180px;">
          <input class="input" type="text" name="search" value="{{ $search }}" placeholder="Cari karya / ID / kelas..." style="width: 100%; font-size: 13px; padding: 6px 12px;">
        </div>

        <div>
          <select class="input" name="project_id" style="font-size: 13px; padding: 6px 10px;">
            <option value="">Semua Kelas</option>
            @foreach($allProjects as $proj)
              <option value="{{ $proj->id }}" @selected((string)$selectedProjectId === (string)$proj->id)>
                No. {{ $proj->grade }} - {{ $proj->class_name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <select class="input" name="sort" style="font-size: 13px; padding: 6px 10px;">
            <option value="name_asc" @selected($sort === 'name_asc')>Nama Produk (A–Z)</option>
            <option value="name_desc" @selected($sort === 'name_desc')>Nama Produk (Z–A)</option>
            <option value="class_asc" @selected($sort === 'class_asc')>Urutan Kelas</option>
            <option value="sold_desc" @selected($sort === 'sold_desc')>Unit Terjual Terbanyak</option>
            <option value="factor_desc" @selected($sort === 'factor_desc')>Reduksi Karbon Tertinggi</option>
            <option value="factor_asc" @selected($sort === 'factor_asc')>Reduksi Karbon Terendah</option>
          </select>
        </div>

        <button class="btn sm" type="submit" style="padding: 6px 14px; font-size: 13px;">Filter</button>
        @if($search || $selectedProjectId || $statusFilter !== 'all' || $sort !== 'name_asc')
          <a href="{{ route('admin.carbon-emissions') }}" class="btn sec sm" style="padding: 6px 10px; font-size: 13px; text-decoration: none;" title="Reset Filter">✕ Reset</a>
        @endif
      </form>
    </div>

    <div style="margin-bottom: 8px; font-size: 12px; color: var(--muted);">
      Menampilkan <strong>{{ $carbonProducts->count() }}</strong> produk
      @if($selectedProjectId)
        &middot; Filter Kelas: <strong>{{ $allProjects->firstWhere('id', $selectedProjectId)?->class_name }}</strong>
      @endif
      @if($statusFilter !== 'all')
        &middot; Filter Status: <strong>{{ ['unset' => 'Belum Di-set', 'set' => 'Sudah Di-set', 'sold' => 'Ada Penjualan'][$statusFilter] ?? $statusFilter }}</strong>
      @endif
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Karya / Produk</th>
            <th>Kelas</th>
            <th style="text-align: center;">Unit Terjual</th>
            <th style="text-align: right;">Dihindari</th>
            <th style="text-align: right;">Tambahan</th>
            <th style="text-align: right;">Net</th>
            <th style="min-width: 220px;">Pengurangan Karbon / Produk</th>
          </tr>
        </thead>
        <tbody>
          @forelse($carbonProducts as $carbonProduct)
            @php
              $impact = $carbonProductBreakdown->get($carbonProduct->id);
              $factor = old('carbon_reduction_value', $carbonProduct->carbon_reduction_value
                ?? (($carbonProduct->carbon_baseline_emission_factor !== null && $carbonProduct->carbon_footprint_value !== null)
                  ? (float) $carbonProduct->carbon_baseline_emission_factor - (float) $carbonProduct->carbon_footprint_value
                  : null));
              $isConfigured = $factor !== null && $factor !== '';
            @endphp
            <tr>
              <td>
                <div style="font-weight: 700;">{{ $carbonProduct->name }}</div>
                <div class="muted" style="font-size: 11px;">ID: #{{ $carbonProduct->id }}</div>
              </td>
              <td>
                @php
                  $pClass = $carbonProduct->seller?->project ?? $carbonProduct->project;
                @endphp
                @if($pClass)
                  <div>{{ $pClass->class_name }}</div>
                  <div class="muted" style="font-size: 11px;">id ({{ $pClass->grade }})</div>
                @else
                  <span class="muted">—</span>
                @endif
              </td>
              <td style="text-align: center;">
                @php
                  $actualSold = $actualSoldPerProduct[$carbonProduct->id] ?? 0;
                @endphp
                @if($actualSold > 0)
                  <strong style="color: var(--good);">{{ $actualSold }} unit</strong>
                @else
                  <span class="muted">0 unit</span>
                @endif
              </td>
              <td style="text-align: right;">{{ $impact ? number_format($impact->total_avoided_emissions, 6, ',', '.') . ' kg' : '—' }}</td>
              <td style="text-align: right;">{{ $impact ? number_format($impact->total_additional_emissions, 6, ',', '.') . ' kg' : '—' }}</td>
              <td style="text-align: right; font-weight: 600;">{{ $impact ? number_format($impact->net_avoided_emissions, 6, ',', '.') . ' kg' : '—' }}</td>
              <td>
                <form method="post" action="{{ route('admin.carbon-factor.update', $carbonProduct) }}" style="display: flex; align-items: center; gap: 8px;">
                  @csrf
                  @method('PUT')
                  <div style="position: relative; flex: 1;">
                    <input class="input" name="carbon_reduction_value" type="number" step="0.000001" min="0" max="999999999999.999999" value="{{ $factor }}" placeholder="0.000000" aria-label="Pengurangan karbon untuk {{ $carbonProduct->name }}" style="padding-right: 50px; font-size: 13px; width: 100%;">
                    <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--muted); pointer-events: none;">kg CO2e</span>
                  </div>
                  <button class="btn sm" type="submit">Simpan</button>
                </form>
                @if(! $isConfigured)
                  <div style="font-size: 11px; color: #f59e0b; margin-top: 3px;">⚠️ Belum diatur</div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted" style="text-align: center; padding: 24px;">Tidak ada produk yang cocok dengan filter.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

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
@endsection
