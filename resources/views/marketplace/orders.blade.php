@extends('layouts.app')

@section('title', 'My orders — FORTEXIV')

@section('content')
<h1>My orders</h1>
<div class="table-wrap"><table>
  <thead><tr><th>Order</th><th>Placed</th><th>Pickup date</th><th>Total</th><th>Status</th><th></th></tr></thead>
  <tbody>
  @forelse($orders as $order)
    <tr><td>{{ $order->order_code }}</td><td>{{ $order->created_at->format('d M Y') }}</td><td>{{ $order->pickup_date->format('d M Y') }}</td><td>{{ \App\Support\Money::format($order->total_price) }}</td><td><span class="badge {{ $order->status }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></td><td><a href="{{ route('orders.show', $order) }}">Details</a></td></tr>
  @empty
    <tr><td colspan="6">You haven't placed an order yet.</td></tr>
  @endforelse
  </tbody>
</table></div>
<div class="pagination">{{ $orders->links() }}</div>
@endsection
