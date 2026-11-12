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
@endphp

<div class="section-head"><div class="eyebrow">ADMINISTRASI PLATFORM</div><h1>Dashboard admin</h1></div>
<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total pesanan</span></div>
  <div class="stat"><strong>{{ $pendingPayments }}</strong><span>Pembayaran menunggu verifikasi</span></div>
  <div class="stat"><strong>{{ $buyersCount }}</strong><span>Akun pembeli</span></div>
  <div class="stat"><strong>{{ $productsSold }}</strong><span>Produk terjual</span></div>
</div>
<h2 id="all-orders">Daftar pesanan</h2>
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

<h2>Pembayaran menunggu verifikasi</h2>
<div class="table-wrap"><table><thead><tr><th>Pesanan</th><th>Pembeli</th><th>Total</th><th>Dikirim</th><th></th></tr></thead><tbody>
  @forelse($payments as $payment)
    <tr><td>{{ $payment->order->order_code }}</td><td>{{ $payment->order->buyer->name }}</td><td>{{ \App\Support\Money::format($payment->order->total_price) }}</td><td>{{ $payment->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td><td><a href="{{ route('admin.payments') }}">Tinjau</a></td></tr>
  @empty
    <tr><td colspan="5">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
