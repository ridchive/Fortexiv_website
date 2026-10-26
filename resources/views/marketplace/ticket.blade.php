@extends('layouts.app')

@section('title', 'Tiket pickup — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('orders.show', $order) }}">Detail pesanan</a> / <span>Tiket pickup</span></nav>
  <article class="ticket">
    <div class="eyebrow">FORTEXIV · TIKET PICKUP</div>
    <h1>{{ $order->order_code }}</h1>
    <p>Buyer: <strong>{{ auth()->user()->name }}</strong><br>Pengambil: <strong>{{ $order->pickup_contact_name ?? auth()->user()->name }}</strong><br>Tanggal: <strong>{{ $order->pickup_date->format('d/m/Y') }}</strong></p>
    <p><span class="badge {{ $order->status }}">{{ ['paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil'][$order->status] ?? $order->status }}</span></p>

    <div class="note info"><strong>Perhatian:</strong> Setiap grup kelas memiliki kode pickup sendiri dan diserahkan terpisah. QR di bawah adalah ilustrasi demo dan tidak dapat dipindai; gunakan kode teks yang tampil.</div>
    @foreach($order->fulfillments as $fulfillment)
      <section class="panel" style="text-align:left">
        <div class="row sp">
          <div><span class="eyebrow">GRUP PICKUP</span><h2>{{ $fulfillment->seller->project?->class_name ?? $fulfillment->seller->store_name }}</h2></div>
          <span class="badge {{ $fulfillment->status === 'handed_over' ? 'handed_over' : 'pending' }}">{{ $fulfillment->status === 'handed_over' ? 'Sudah diserahkan' : 'Menunggu pengambilan' }}</span>
        </div>
        <div class="qrbox">
          @include('marketplace.partials.pickup-qr-demo')
          <div>
            <p class="eyebrow">KODE PICKUP · FALLBACK TEKS</p>
            <div class="ticket-code">{{ $fulfillment->pickup_code }}</div>
            <p>Tunjukkan kode ini kepada petugas kelas yang sesuai.</p>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Produk</th><th>Jumlah</th></tr></thead>
            <tbody>
              @foreach($order->items->where('seller_id', $fulfillment->seller_id) as $item)
                <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if($fulfillment->handed_over_at)
          <p class="muted">Diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
        @endif
      </section>
    @endforeach
    <a class="btn sec" href="{{ route('orders.show', $order) }}">Kembali ke detail pesanan</a>
  </article>
</div>
@endsection
