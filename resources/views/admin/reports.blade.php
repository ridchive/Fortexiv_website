@extends('layouts.app')

@section('title', 'Laporan harian — FORTEXIV')

@section('content')
<div class="section-head"><h1>Laporan harian</h1>
  <form class="inline-form" method="get" action="{{ route('admin.reports') }}"><label for="date">Tanggal</label><input class="input" id="date" name="date" type="date" value="{{ $date }}"><button class="btn btn-small">Tampilkan</button></form>
</div>
<p class="muted">Laporan tanggal {{ \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d M Y') }}. Pendapatan hanya menghitung pembayaran yang telah diverifikasi.</p>
<div class="stats">
  <div class="stat"><strong>{{ $totalOrders }}</strong><span>Total pesanan</span></div>
  <div class="stat"><strong>{{ $paidOrders }}</strong><span>Pesanan lunas</span></div>
  <div class="stat"><strong>{{ $cancelledOrders }}</strong><span>Pesanan dibatalkan</span></div>
  <div class="stat"><strong>{{ $productsSold }}</strong><span>Produk terjual</span></div>
  <div class="stat"><strong>{{ \App\Support\Money::format($revenue) }}</strong><span>Pendapatan</span></div>
  <div class="stat"><strong>{{ $pickedUpOrders }}</strong><span>Pesanan selesai diambil</span></div>
</div>
@endsection
