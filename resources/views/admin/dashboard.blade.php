@extends('layouts.app')

@section('title', 'Admin dashboard — FORTEXIV')

@section('content')
@php
    $paymentLabels = [
        'pending' => 'Menunggu verif',
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

<div class="section-head"><div class="eyebrow">PLATFORM ADMINISTRATION</div><h1>Admin dashboard</h1></div>
<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total orders</span></div>
  <div class="stat"><strong>{{ $pendingPayments }}</strong><span>Payments to review</span></div>
  <div class="stat"><strong>{{ $buyersCount }}</strong><span>Buyer accounts</span></div>
  <div class="stat"><strong>{{ $productsSold }}</strong><span>Items sold</span></div>
</div>
<div class="chip-row">
  <a class="chip" href="#all-orders">Daftar Order</a>
  <a class="chip" href="{{ route('admin.payments') }}">Payment review</a>
  <a class="chip" href="{{ route('admin.users') }}">Manage accounts</a>
  <a class="chip" href="{{ route('admin.pickup') }}">Validate pickup</a>
  <a class="chip" href="{{ route('admin.reports') }}">Daily reports</a>
</div>

<h2 id="all-orders">Daftar Order</h2>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Nomor Order</th>
        <th>Nama Buyer</th>
        <th>Total</th>
        <th>Status Pembayaran</th>
        <th>Status Pesanan Buyer</th>
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

<h2>Payments awaiting verification</h2>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Buyer</th><th>Total</th><th>Submitted</th><th></th></tr></thead><tbody>
  @forelse($payments as $payment)
    <tr><td>{{ $payment->order->order_code }}</td><td>{{ $payment->order->buyer->name }}</td><td>{{ \App\Support\Money::format($payment->order->total_price) }}</td><td>{{ $payment->created_at->format('d M Y H:i') }}</td><td><a href="{{ route('admin.payments') }}">Review</a></td></tr>
  @empty
    <tr><td colspan="5">No payments waiting for review.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
