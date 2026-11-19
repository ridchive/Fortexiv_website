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

    <div class="note info">Tunjukkan QR kelas untuk menyerahkan seluruh produk kelas, atau QR produk untuk menyerahkan produk satu per satu.</div>
    @foreach($order->fulfillments as $fulfillment)
      @php($className = $fulfillment->class_name ?? $fulfillment->project?->class_name ?? $fulfillment->seller->store_name)
      <section class="panel" style="text-align:left">
        <div class="row sp">
          <div>
            <span class="eyebrow">ORDER ID · {{ $order->order_code }}</span>
            <h2>{{ $className }}</h2>
          </div>
          <span class="badge {{ $fulfillment->status }}">{{ ['pending' => 'Menunggu pengambilan', 'partially_delivered' => 'Partially Delivered', 'delivered' => 'Delivered'][$fulfillment->status] ?? $fulfillment->status }}</span>
        </div>
        <div class="qrbox" data-qr-card>
          <canvas width="180" height="180" data-qr-value="{{ $fulfillment->pickup_code }}" aria-label="QR kelas {{ $className }}"></canvas>
          <div>
            <p class="eyebrow">QR KELAS</p>
            <strong>{{ $fulfillment->pickup_code }}</strong>
            <p>Memuat semua produk dalam kelas ini.</p>
            <div class="btns">
              <button class="btn sm" type="button" data-qr-download disabled>Unduh QR (PNG)</button>
              <button class="btn sm sec" type="button" data-token-download="{{ $fulfillment->pickup_code }}" data-order-code="{{ $order->order_code }}" data-class-name="{{ $className }}">Unduh token (TXT)</button>
            </div>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nama produk</th><th>Status</th><th>QR produk</th></tr></thead>
            <tbody>
              @foreach($fulfillment->items as $item)
                @foreach($item->units as $unit)
                  <tr>
                    <td>{{ $item->product_name }}@if($item->quantity > 1) · unit {{ $loop->iteration }}@endif</td>
                    <td><span class="badge {{ $unit->status }}">{{ $unit->status === 'delivered' ? 'Delivered' : 'Menunggu pengambilan' }}</span></td>
                    <td>
                      <canvas width="120" height="120" data-qr-value="{{ $unit->qr_token }}" aria-label="QR produk {{ $item->product_name }}"></canvas>
                      <div class="muted">{{ $unit->qr_token }}</div>
                    </td>
                  </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
        @if($fulfillment->handed_over_at)
          <p class="muted">Semua produk kelas diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
        @endif
      </section>
    @endforeach
    <a class="btn sec" href="{{ route('orders.show', $order) }}">Kembali ke detail pesanan</a>
  </article>
</div>
@endsection
