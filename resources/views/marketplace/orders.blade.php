@extends('layouts.app')

@section('title', 'Pesanan saya — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Pesanan saya</span></nav>
  <div class="pgh"><span class="eyebrow">RIWAYAT BUYER</span><h1>Pesanan saya</h1><p>Pantau pembayaran dan status pickup tiap grup kelas.</p></div>

  @include('marketplace.partials.buyer-tabs', ['activeTab' => 'orders'])

  @if($orders->isEmpty())
    <div class="empty">
      <h3>Belum ada pesanan</h3>
      <p>Produk pilihanmu akan muncul di sini setelah checkout.</p>
      <a class="btn" href="{{ route('catalog.index') }}">Jelajahi katalog</a>
    </div>
  @else
    <div class="table-wrap">
      <table>
        <thead><tr><th>Pesanan</th><th>Dibuat</th><th>Grup kelas</th><th>Total</th><th>Kontribusi karbon</th><th>Status</th><th style="text-align: right;">Aksi</th></tr></thead>
        <tbody>
        @foreach($orders as $order)
          @php($carbonOrder = $carbonSummary['order_breakdown']->get($order->id))
          <tr>
            <td><strong>{{ $order->order_code }}</strong></td>
            <td>{{ $order->created_at->format('d/m/Y') }}</td>
            <td>{{ $order->fulfillments_count }}</td>
            <td><strong>{{ \App\Support\Money::format($order->total_price) }}</strong></td>
            <td>
              @if($carbonOrder)
                {{ number_format($carbonOrder->net_avoided_emissions, 3, ',', '.') }} kg CO2e net
              @elseif(in_array($order->status, ['cancelled', 'refunded', 'failed', 'rejected'], true))
                Tidak eligible
              @elseif($order->payment?->status === 'verified')
                Belum tersedia
              @else
                Menunggu verifikasi
              @endif
            </td>
            <td><span class="badge {{ $order->status }}">{{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$order->status] ?? $order->status }}</span></td>
            <td style="text-align: right;"><a class="btn sm sec" href="{{ route('orders.show', $order) }}">Lihat detail</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="pagination">{{ $orders->links() }}</div>
  @endif

  <section class="panel" style="margin-top: 28px; margin-bottom: 20px;">
    <div class="eyebrow">DAMPAK PEMBELIAN TERVERIFIKASI</div>
    <div class="stats">
      <div class="stat"><strong>{{ number_format($carbonSummary['total_avoided_emissions'], 3, ',', '.') }} kg CO2e</strong><span>Emisi dihindari</span></div>
      <div class="stat"><strong>{{ number_format($carbonSummary['total_additional_emissions'], 3, ',', '.') }} kg CO2e</strong><span>Emisi tambahan</span></div>
      <div class="stat"><strong>{{ number_format($carbonSummary['net_avoided_emissions'], 3, ',', '.') }} kg CO2e</strong><span>Net emisi dihindari</span></div>
    </div>
    <p class="muted">Dari {{ $carbonSummary['eligible_item_count'] }} item pesanan dengan pembayaran terverifikasi.</p>
    @if($carbonSummary['class_breakdown']->isNotEmpty())
      <div class="table-wrap">
        <table>
          <thead><tr><th>Kelas</th><th>Dihindari</th><th>Tambahan</th><th>Net (kg CO2e)</th></tr></thead>
          <tbody>
            @foreach($carbonSummary['class_breakdown'] as $row)
              <tr><td>{{ $row->class_name }}</td><td>{{ number_format($row->total_avoided_emissions, 3, ',', '.') }}</td><td>{{ number_format($row->total_additional_emissions, 3, ',', '.') }}</td><td>{{ number_format($row->net_avoided_emissions, 3, ',', '.') }} kg CO2e</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </section>
</div>
@endsection
