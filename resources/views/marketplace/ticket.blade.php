@extends('layouts.app')

@section('title', 'Pickup ticket — FORTEXIV')

@section('content')
<a href="{{ route('orders.show', $order) }}">&larr; Back to order</a>
<article class="ticket">
  <div class="eyebrow">FORTEXIV · PICKUP TICKET</div>
  <h1>{{ $order->order_code }}</h1>
  <div class="ticket-code">{{ $order->ticket->ticket_code }}</div>
  <p>Show this ticket at pickup.</p>
  <p>Buyer: <strong>{{ auth()->user()->name }}</strong><br>Pickup date: <strong>{{ $order->pickup_date->format('d M Y') }}</strong></p>
  @if($order->ticket->picked_up_at)
    <p><span class="badge picked_up">Picked up {{ $order->ticket->picked_up_at->format('d M Y H:i') }}</span></p>
  @else
    <p><span class="badge paid">Payment verified</span></p>
  @endif
  <div class="table-wrap"><table><thead><tr><th>Product</th><th>Qty</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td></tr>@endforeach</tbody></table></div>
</article>
@endsection
