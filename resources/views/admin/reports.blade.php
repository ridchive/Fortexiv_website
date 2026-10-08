@extends('layouts.app')

@section('title', 'Daily reports — FORTEXIV')

@section('content')
<div class="section-head"><h1>Daily report</h1>
  <form class="inline-form" method="get" action="{{ route('admin.reports') }}"><label for="date">Date</label><input class="input" id="date" name="date" type="date" value="{{ $date }}"><button class="btn btn-small">View</button></form>
</div>
<p class="muted">Report for {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}. Revenue counts verified payments only.</p>
<div class="stats">
  <div class="stat"><strong>{{ $totalOrders }}</strong><span>Total orders</span></div>
  <div class="stat"><strong>{{ $paidOrders }}</strong><span>Paid orders</span></div>
  <div class="stat"><strong>{{ $cancelledOrders }}</strong><span>Cancelled orders</span></div>
  <div class="stat"><strong>{{ $productsSold }}</strong><span>Products sold</span></div>
  <div class="stat"><strong>{{ \App\Support\Money::format($revenue) }}</strong><span>Revenue</span></div>
  <div class="stat"><strong>{{ $pickedUpOrders }}</strong><span>Orders picked up</span></div>
</div>
@endsection
