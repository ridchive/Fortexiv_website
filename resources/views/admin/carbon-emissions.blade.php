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

    <div class="section-head" style="margin: 20px 0 10px;">
      <div>
        <div class="eyebrow">FAKTOR EMISI PRODUK</div>
        <h3 style="margin: 4px 0 0;">Edit tabel kontribusi per produk</h3>
      </div>
    </div>
    <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Isi pengurangan karbon per produk dalam kg CO2e. Perubahan ini digunakan untuk snapshot transaksi baru dan tidak mengubah hasil pesanan lama.</p>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Karya / Produk</th>
            <th>Kelas</th>
            <th>Unit terverifikasi</th>
            <th>Dihindari</th>
            <th>Tambahan</th>
            <th>Net</th>
            <th>Pengurangan karbon / produk</th>
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
            @endphp
            <tr>
              <td><strong>{{ $carbonProduct->name }}</strong></td>
              <td>{{ $carbonProduct->seller->project->class_name ?? ($carbonProduct->project->class_name ?? '-') }}</td>
              <td>{{ $impact ? $impact->eligible_sold_units : 0 }} unit</td>
              <td>{{ $impact ? number_format($impact->total_avoided_emissions, 6, ',', '.') . ' kg CO2e' : '—' }}</td>
              <td>{{ $impact ? number_format($impact->total_additional_emissions, 6, ',', '.') . ' kg CO2e' : '—' }}</td>
              <td>{{ $impact ? number_format($impact->net_avoided_emissions, 6, ',', '.') . ' kg CO2e' : '—' }}</td>
              <td>
                <form method="post" action="{{ route('admin.carbon-factor.update', $carbonProduct) }}" style="display: flex; align-items: center; gap: 8px; min-width: 210px;">
                  @csrf
                  @method('PUT')
                  <input class="input" name="carbon_reduction_value" type="number" step="0.000001" min="0" max="999999999999.999999" value="{{ $factor }}" aria-label="Pengurangan karbon untuk {{ $carbonProduct->name }}">
                  <button class="btn sm" type="submit">Simpan</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted">Belum ada produk.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

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
@endsection
