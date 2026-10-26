@extends('layouts.app')

@section('title', 'Bantuan & FAQ — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Bantuan</span></nav>
  <div class="pgh">
    <h1>Bantuan dan pertanyaan umum</h1>
    <p>Informasi singkat tentang PO bersama, pembayaran, dan pengambilan pesanan FORTEXIV.</p>
  </div>

  <section class="panel">
    <h2>Bagaimana cara memesan?</h2>
    <p>Jelajahi cerita proyek kelas atau katalog produk. Tambahkan beberapa produk ke keranjang untuk membuat satu pesanan lintas kelas, atau pilih Beli Sekarang untuk checkout satu produk.</p>
  </section>
  <section class="panel">
    <h2>Apakah ada pengiriman?</h2>
    <p>Tidak. Pesanan diambil langsung di acara. Jika pesanan berisi produk beberapa kelas, setiap kelas menyerahkan grup barangnya secara terpisah.</p>
  </section>
  <section class="panel">
    <h2>Bagaimana cara pembayaran dikonfirmasi?</h2>
    <p>Ikuti instruksi transfer pada detail pesanan dan unggah bukti pembayaran. Pesanan baru siap diambil setelah pembayaran diverifikasi admin.</p>
  </section>
  <section class="panel">
    <h2>Bagaimana QR pickup digunakan?</h2>
    <p>Setiap grup kelas memiliki kode pickup tersendiri. Tunjukkan tiket pesanan kepada petugas kelas, cocokkan barang yang diterima, lalu petugas mencatat penyerahan. Kode teks pada halaman tiket tersedia sebagai fallback demo.</p>
  </section>
  <section class="panel">
    <h2>Di mana membaca Terms of Service?</h2>
    <p><a href="{{ route('terms') }}">Baca Terms of Service FORTEXIV</a>. Persetujuan wajib dicatat sebelum checkout.</p>
  </section>
  @if(config('marketplace.pickup_instructions'))
    <section class="panel">
      <h2>Petunjuk pengambilan</h2>
      <p>{{ config('marketplace.pickup_instructions') }}</p>
    </section>
  @endif
  <div class="section-actions">
    <a class="btn" href="{{ route('projects.index') }}">Jelajahi proyek kelas</a>
    <a class="btn sec" href="{{ route('catalog.index') }}">Lihat katalog</a>
  </div>
</div>
@endsection
