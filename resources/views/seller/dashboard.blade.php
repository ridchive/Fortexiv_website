@extends('layouts.app')

@section('title', 'Seller dashboard — FORTEXIV')

@section('content')
<div class="section-head"><div><div class="eyebrow">SELLER DASHBOARD</div><h1>{{ $seller->store_name }}</h1></div><a class="btn" href="{{ route('seller.products.create') }}">Add a product</a></div>
<div class="stats">
  <div class="stat"><strong>{{ $ordersCount }}</strong><span>Total orders</span></div>
  <div class="stat"><strong>{{ $items->where('order.status', 'waiting_verification')->count() }}</strong><span>Payment review</span></div>
  <div class="stat"><strong>{{ $productsCount }}</strong><span>Your products</span></div>
  <div class="stat"><strong>{{ $pendingCount }}</strong><span>Draft products</span></div>
</div>
<div class="chip-row"><a class="chip" href="{{ route('seller.products.index') }}">Manage products</a><a class="chip" href="{{ route('seller.orders') }}">Orders &amp; buyers</a></div>
<h2>Recent orders</h2>
<div class="table-wrap"><table><thead><tr><th>Buyer</th><th>Product</th><th>Qty</th><th>Order</th><th>Order status</th></tr></thead><tbody>
  @forelse($items as $item)
    <tr><td>{{ $item->order->buyer->name }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->order->order_code }}</td><td><span class="badge {{ $item->order->status }}">{{ str_replace('_', ' ', ucfirst($item->order->status)) }}</span></td></tr>
  @empty
    <tr><td colspan="5">Orders for your products will appear here.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
