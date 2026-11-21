@extends('layouts.app')

@section('title', 'Tiket pickup — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('orders.show', $order) }}">Detail pesanan</a> / <span>Tiket pickup</span></nav>
  <article class="ticket">
    <header class="ticket-header">
      <div class="ticket-brand" aria-label="FORTEXIV">
        <span class="brand-logo-frame ticket-logo-frame"><x-fortexiv-logo /></span>
      </div>
      <span class="eyebrow">Digital Pickup Ticket</span>
      <h1 class="ticket-order-code">{{ $order->order_code }}</h1>
      <span class="badge {{ $order->status }}">{{ ['paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil'][$order->status] ?? $order->status }}</span>
      <dl class="ticket-meta">
        <div>
          <dt>Buyer</dt>
          <dd>{{ auth()->user()->name }}</dd>
        </div>
        <div>
          <dt>Pengambil</dt>
          <dd>{{ $order->pickup_contact_name ?? auth()->user()->name }}</dd>
        </div>
        <div>
          <dt>Tanggal pickup</dt>
          <dd>{{ $order->pickup_date->format('d/m/Y') }}</dd>
        </div>
      </dl>
    </header>

    <p class="ticket-instructions">QR kelas digunakan untuk mengambil seluruh produk dari satu kelas. Gunakan QR produk untuk mengambil unit satu per satu. Pemindaian QR belum mengonfirmasi serah-terima.</p>
    @foreach($order->fulfillments as $fulfillment)
      @php($className = $fulfillment->class_name ?? $fulfillment->project?->class_name ?? $fulfillment->seller->store_name)
      <section class="panel ticket-group" aria-label="Pickup {{ $className }}">
        <div class="ticket-group-heading">
          <div>
            <span class="eyebrow">Grup kelas</span>
            <h2>{{ $className }}</h2>
            <span class="ticket-order-id">Order ID · {{ $order->order_code }}</span>
          </div>
          <span class="badge {{ $fulfillment->status }}">{{ ['pending' => 'Menunggu pengambilan', 'partially_delivered' => 'Sebagian diserahkan', 'delivered' => 'Sudah diserahkan'][$fulfillment->status] ?? $fulfillment->status }}</span>
        </div>
        <div class="qrbox ticket-class-qr" data-qr-card>
          <div class="ticket-qr-image ticket-class-qr-image">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($fulfillment->pickup_code) }}" alt="QR kelas {{ $className }}" loading="lazy">
            <canvas width="180" height="180" data-qr-value="{{ $fulfillment->pickup_code }}" aria-label="QR kelas {{ $className }}" hidden></canvas>
          </div>
          <div class="ticket-class-qr-details">
            <span class="eyebrow">QR KELAS · SEMUA PRODUK</span>
            <strong class="ticket-token">{{ $fulfillment->pickup_code }}</strong>
            <p>Pindai QR ini untuk memproses seluruh produk kelas sebagai satu grup. Petugas tetap mengonfirmasi penyerahan setelah barang diberikan.</p>
            <div class="ticket-downloads">
              <button class="btn" type="button" data-qr-download disabled>Unduh QR (PNG)</button>
              <button class="btn sec" type="button" data-token-download="{{ $fulfillment->pickup_code }}" data-order-code="{{ $order->order_code }}" data-class-name="{{ $className }}">Unduh token (TXT)</button>
            </div>
          </div>
        </div>

        <section class="ticket-products" aria-labelledby="products-{{ $fulfillment->id }}">
          <div class="ticket-products-heading">
            <h3 id="products-{{ $fulfillment->id }}">QR produk per unit</h3>
            <p>Setiap QR berlaku untuk satu unit.</p>
          </div>
          <div class="ticket-unit-list">
            @foreach($fulfillment->items as $item)
              @foreach($item->units as $unit)
                <article class="ticket-unit">
                  <div class="ticket-unit-details">
                    <strong class="ticket-product-name">{{ $item->product_name }}@if($item->quantity > 1) · unit {{ $loop->iteration }}@endif</strong>
                    <span class="badge {{ $unit->status }}">{{ $unit->status === 'delivered' ? 'Sudah diserahkan' : 'Menunggu pengambilan' }}</span>
                    <span class="ticket-unit-token">{{ $unit->qr_token }}</span>
                  </div>
                  <div class="ticket-qr-image ticket-unit-qr-image">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($unit->qr_token) }}" alt="QR produk {{ $item->product_name }}" loading="lazy">
                    <canvas width="110" height="110" data-qr-value="{{ $unit->qr_token }}" aria-label="QR produk {{ $item->product_name }}" hidden></canvas>
                  </div>
                </article>
              @endforeach
            @endforeach
          </div>
        </section>
        @if($fulfillment->handed_over_at)
          <p class="ticket-handover-note">Grup kelas diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
        @endif
      </section>
    @endforeach
    <a class="btn sec ticket-back-link" href="{{ route('orders.show', $order) }}">Kembali ke detail pesanan</a>
  </article>
</div>
@endsection
