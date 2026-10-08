@extends('layouts.app')

@section('title', 'Orders & buyers — FORTEXIV')

@section('content')
<h1>Orders &amp; buyers</h1>
<div class="table-wrap"><table><thead><tr><th>Buyer</th><th>Product</th><th>Qty</th><th>Order</th><th>Payment</th><th>Pickup</th><th>Status</th></tr></thead><tbody>
  @forelse($items as $item)
    <tr><td>{{ $item->order->buyer->name }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->order->order_code }}</td><td><span class="badge {{ $item->order->payment?->status ?? 'pending' }}">{{ ucfirst($item->order->payment?->status ?? 'not submitted') }}</span></td><td>{{ $item->order->pickup_date->format('d M Y') }}</td><td><span class="badge {{ $item->order->status }}">{{ str_replace('_', ' ', ucfirst($item->order->status)) }}</span></td></tr>
  @empty
    <tr><td colspan="7">Orders for your products will appear here.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $items->links() }}</div>
@endsection
