@extends('layouts.app')

@section('title', 'Pesanan saya — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Pesanan saya</span></nav>
  <div class="pgh"><span class="eyebrow">RIWAYAT BUYER</span><h1>Pesanan saya</h1><p>Pantau pembayaran dan status pickup tiap grup kelas.</p></div>
  @if($orders->isEmpty())
    <div class="empty">
      <h3>Belum ada pesanan</h3>
      <p>Produk pilihanmu akan muncul di sini setelah checkout.</p>
      <a class="btn" href="{{ route('catalog.index') }}">Jelajahi katalog</a>
    </div>
  @else
    <div class="table-wrap">
      <table>
        <thead><tr><th>Pesanan</th><th>Dibuat</th><th>Tanggal pickup</th><th>Grup kelas</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($orders as $order)
          <tr>
            <td>{{ $order->order_code }}</td>
            <td>{{ $order->created_at->format('d/m/Y') }}</td>
            <td>{{ $order->pickup_date->format('d/m/Y') }}</td>
            <td>{{ $order->fulfillments_count }}</td>
            <td>{{ \App\Support\Money::format($order->total_price) }}</td>
            <td><span class="badge {{ $order->status }}">{{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$order->status] ?? $order->status }}</span></td>
            <td><a href="{{ route('orders.show', $order) }}">Lihat detail</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="pagination">{{ $orders->links() }}</div>
  @endif
</div>
@endsection
