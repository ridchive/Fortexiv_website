@extends('layouts.app')

@section('title', 'Order '.$order->order_code.' — FORTEXIV')

@section('content')
<a href="{{ route('orders.index') }}">&larr; Back to orders</a>
<div class="section-head"><h1>Order {{ $order->order_code }}</h1><span class="badge {{ $order->status }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></div>
<section class="panel">
  <p>Pickup date: <strong>{{ $order->pickup_date->format('d M Y') }}</strong></p>
  <div class="table-wrap"><table><thead><tr><th>Product</th><th>Seller</th><th>Quantity</th><th>Unit price</th><th>Subtotal</th></tr></thead><tbody>
    @foreach($order->items as $item)
      <tr><td>{{ $item->product_name }}</td><td>{{ $item->seller->store_name }}</td><td>{{ $item->quantity }}</td><td>{{ \App\Support\Money::format($item->price_at_order) }}</td><td>{{ \App\Support\Money::format($item->subtotal) }}</td></tr>
    @endforeach
  </tbody><tfoot><tr><td colspan="4"><strong>Total</strong></td><td><strong>{{ \App\Support\Money::format($order->total_price) }}</strong></td></tr></tfoot></table></div>
</section>
@if($order->status === 'pending_payment')
  <section class="panel">
    <h2>Manual bank transfer</h2>
    @if(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
      <p>Transfer the exact total, then upload a JPG, PNG, or PDF transfer receipt (maximum 5 MB).</p>
      <p><strong>{{ config('marketplace.bank_name') }}</strong><br>Account name: {{ config('marketplace.account_name') }}<br>Account number: {{ config('marketplace.account_number') }}</p>
      <form method="post" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data">
        @csrf
        <div class="field"><label for="method">Transfer method / bank</label><input class="input" id="method" name="method" placeholder="e.g. Transfer BCA" maxlength="50" required></div>
        <div class="field"><label for="proof">Proof of transfer</label><input class="input" id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required></div>
        <button class="btn" type="submit">Send proof for verification</button>
      </form>
    @else
      <p class="muted">Bank transfer details have not been configured yet. Contact the marketplace administrator before making a transfer.</p>
    @endif
  </section>
@elseif($order->payment)
  <section class="panel"><h2>Payment status</h2><p><span class="badge {{ $order->payment->status }}">{{ ucfirst($order->payment->status) }}</span></p>@if($order->payment->notes)<p>Admin note: {{ $order->payment->notes }}</p>@endif</section>
@endif
@if($order->ticket)
  <a class="btn" href="{{ route('tickets.show', $order) }}">View pickup ticket</a>
@endif
@endsection
