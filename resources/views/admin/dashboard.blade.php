@extends('layouts.app')

@section('title', 'Admin dashboard — FORTEXIV')

@section('content')
<div class="section-head"><div class="eyebrow">PLATFORM ADMINISTRATION</div><h1>Admin dashboard</h1></div>
<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total orders</span></div>
  <div class="stat"><strong>{{ $pendingPayments }}</strong><span>Payments to review</span></div>
  <div class="stat"><strong>{{ $buyersCount }}</strong><span>Buyer accounts</span></div>
  <div class="stat"><strong>{{ $productsSold }}</strong><span>Items sold</span></div>
</div>
<div class="chip-row"><a class="chip" href="{{ route('admin.payments') }}">Payment review</a><a class="chip" href="{{ route('admin.users') }}">Manage accounts</a><a class="chip" href="{{ route('admin.pickup') }}">Validate pickup</a><a class="chip" href="{{ route('admin.reports') }}">Daily reports</a></div>
<h2>Payments awaiting verification</h2>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Buyer</th><th>Total</th><th>Submitted</th><th></th></tr></thead><tbody>
  @forelse($payments as $payment)
    <tr><td>{{ $payment->order->order_code }}</td><td>{{ $payment->order->buyer->name }}</td><td>{{ \App\Support\Money::format($payment->order->total_price) }}</td><td>{{ $payment->created_at->format('d M Y H:i') }}</td><td><a href="{{ route('admin.payments') }}">Review</a></td></tr>
  @empty
    <tr><td colspan="5">No payments waiting for review.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
