@extends('layouts.app')

@section('title', 'Your cart — FORTEXIV')

@section('content')
<h1>Your cart</h1>
<p class="muted">Confirm your items and choose a pickup date.</p>
@if($items->isEmpty())
  <div class="empty">Your cart is empty. <a href="{{ route('home') }}">Explore the marketplace</a>.</div>
@else
  <div class="table-wrap"><table>
    <thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead>
    <tbody>
    @foreach($items as $item)
      <tr>
        <td><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></td>
        <td>{{ $item->product->seller->store_name }}</td>
        <td>{{ \App\Support\Money::format($item->product->price) }}</td>
        <td><form class="inline-form" method="post" action="{{ route('cart.update', $item) }}">@csrf @method('PATCH')<input class="input" style="width:82px" name="quantity" type="number" min="1" max="100" value="{{ $item->quantity }}" aria-label="Quantity"><button class="btn btn-outline btn-small">Update</button></form></td>
        <td>{{ \App\Support\Money::format($item->product->price * $item->quantity) }}</td>
        <td><form method="post" action="{{ route('cart.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-small">Remove</button></form></td>
      </tr>
    @endforeach
    </tbody>
    <tfoot><tr><td colspan="4"><strong>Total</strong></td><td colspan="2"><strong>{{ \App\Support\Money::format($items->sum(fn ($item) => $item->product->price * $item->quantity)) }}</strong></td></tr></tfoot>
  </table></div>
  <section class="panel">
    <h2>Checkout &amp; pickup</h2>
    @if(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
      <form method="post" action="{{ route('checkout') }}" class="stack" style="margin-top:14px">@csrf
        <label for="pickup_date">Pickup date</label><input class="input" style="max-width:220px" id="pickup_date" name="pickup_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('pickup_date', now()->addDay()->toDateString()) }}" required>
        <button class="btn" type="submit">Place order</button>
      </form>
    @else
      <p class="muted">Checkout is temporarily unavailable while the marketplace admin configures manual transfer details.</p>
    @endif
  </section>
@endif
@endsection
