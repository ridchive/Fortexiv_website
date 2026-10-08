@extends('layouts.app')

@section('title', 'Validate pickup — FORTEXIV')

@section('content')
<section class="auth-card">
  <div class="eyebrow">ORDER FULFILMENT</div>
  <h1>Validate pickup ticket</h1>
  <p class="muted">Each ticket can be used once. Check the displayed order before handing over products.</p>
  <form method="post" action="{{ route('admin.pickup.validate') }}">@csrf
    <div class="field"><label for="ticket_code">Ticket code</label><input class="input" id="ticket_code" name="ticket_code" placeholder="FXT-XXXXXXXXXXXX" maxlength="32" required autofocus></div>
    <button class="btn" type="submit">Validate ticket</button>
  </form>
  @if(session('pickup_order'))
    @php($order = session('pickup_order'))
    <div class="panel" style="margin-top:20px"><strong>Pickup confirmed</strong><p>Order {{ $order->order_code }} for {{ $order->buyer->name }}.</p></div>
  @endif
</section>
@endsection
