@extends('layouts.app')

@section('title', 'Scan QR pesanan — FORTEXIV')

@section('content')
<div class="wrap" data-qr-scanner data-preview-url="{{ route('seller.pickup.qr.preview') }}" data-confirm-url="{{ route('seller.pickup.qr.confirm') }}">
  <nav class="crumb"><a href="{{ route('seller.orders') }}">Pesanan kelas</a> / <span>Scan QR</span></nav>
  <div class="pgh">
    <span class="eyebrow">SERAH-TERIMA PESANAN</span>
    <h1>Verifikasi QR</h1>
    <p>Scan QR kelas untuk menandai seluruh produk atau QR produk untuk menandai satu unit. Periksa detail sebelum mengonfirmasi.</p>
  </div>
  <section class="panel">
    <div class="btns">
      <button class="btn" type="button" data-qr-mode="class">Scan Class QR</button>
      <button class="btn sec" type="button" data-qr-mode="product">Scan Product QR</button>
    </div>
    <div id="seller-qr-reader" style="max-width:520px;margin:20px auto"></div>
    <p class="note info" role="status" aria-live="polite" data-qr-message>Pilih jenis QR untuk memulai kamera.</p>
    <form class="inline-form" data-qr-manual-form>
      <div class="field">
        <label for="seller-qr-token">Atau masukkan token QR</label>
        <input class="input" id="seller-qr-token" name="token" maxlength="80" autocomplete="off">
      </div>
      <button class="btn sec" type="submit">Periksa token</button>
    </form>
  </section>
  <section class="panel" data-qr-preview hidden>
    <span class="eyebrow">DETAIL SEBELUM KONFIRMASI</span>
    <h2 data-qr-order></h2>
    <p><strong>Kelas:</strong> <span data-qr-class></span></p>
    <p><strong>Produk belum diserahkan:</strong> <span data-qr-pending></span></p>
    <ul data-qr-products></ul>
    <button class="btn" type="button" data-qr-confirm>Konfirmasi Delivered</button>
  </section>
  <a class="btn sec" href="{{ route('seller.orders') }}">Kembali ke pesanan</a>
</div>
@endsection
