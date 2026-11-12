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
@endphp
<div class="section-head"><div><div class="eyebrow">DASHBOARD PENJUAL</div><h1>{{ $seller->store_name }}</h1></div><a class="btn" href="{{ route('seller.products.create') }}">Tambah produk</a></div>
<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total pesanan</span></div>
  <div class="stat"><strong>{{ $items->where('order.status', 'waiting_verification')->count() }}</strong><span>Menunggu verifikasi pembayaran</span></div>
  <div class="stat"><strong>{{ $productsCount }}</strong><span>Produk Anda</span></div>
  <div class="stat"><strong>{{ $pendingCount }}</strong><span>Produk menunggu review admin</span></div>
</div>
<div class="chip-row"><a class="chip" href="{{ route('seller.products.index') }}">Kelola produk</a><a class="chip" href="{{ route('seller.orders') }}">Pesanan dan pembeli</a></div>
<h2>Pesanan terbaru</h2>
<div class="table-wrap"><table><thead><tr><th>Pembeli</th><th>Produk</th><th>Jumlah</th><th>Pesanan</th><th>Status pesanan</th></tr></thead><tbody>
  @forelse($items as $item)
    <tr><td>{{ $item->order->buyer->name }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->order->order_code }}</td><td><span class="badge {{ $item->order->status }}">{{ $orderLabels[$item->order->status] ?? $item->order->status }}</span></td></tr>
  @empty
    <tr><td colspan="5">Pesanan untuk produk Anda akan ditampilkan di sini.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
