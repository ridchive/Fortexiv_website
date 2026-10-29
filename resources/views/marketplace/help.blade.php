@extends('layouts.app')

@section('title', 'Bantuan & FAQ — FORTEXIV')

@section('content')
@php
    $activeSection = in_array($activeSection ?? request()->query('section', 'cara-memesan'), ['cara-memesan', 'pengambilan-barang'], true)
        ? ($activeSection ?? request()->query('section', 'cara-memesan'))
        : 'cara-memesan';
@endphp

<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Bantuan</span></nav>
  <div class="pgh">
    <h1>Bantuan dan pertanyaan umum</h1>
    <p>Informasi singkat tentang PO bersama, pembayaran, dan pengambilan pesanan FORTEXIV.</p>
  </div>

  <div class="chip-row" role="tablist" aria-label="Menu bantuan">
    <a href="{{ route('help', ['section' => 'cara-memesan']) }}#cara-memesan" class="chip {{ $activeSection === 'cara-memesan' ? 'good' : '' }}">Cara memesan</a>
    <a href="{{ route('help', ['section' => 'pengambilan-barang']) }}#pengambilan-barang" class="chip {{ $activeSection === 'pengambilan-barang' ? 'good' : '' }}">Pengambilan barang</a>
    <a href="{{ route('terms') }}" class="chip">Terms of Service</a>
  </div>

  <section id="cara-memesan" class="panel">
    <h2>Cara memesan</h2>
    <p>Jelajahi cerita proyek kelas atau katalog produk. Tambahkan beberapa produk ke keranjang untuk membuat satu pesanan lintas kelas, atau pilih Beli Sekarang untuk checkout satu produk.</p>
    <ol class="steps help-steps">
      <li><h3>Jelajahi artikel & katalog</h3><p>Baca proyek kelas dan pilih produk dari salah satu atau beberapa kelas yang ingin kamu beli.</p></li>
      <li><h3>Tambah ke keranjang</h3><p>Masukkan item dari kelas berbeda dalam satu pesanan, lalu lanjutkan ke checkout saat PO dibuka.</p></li>
      <li><h3>Checkout & setujui ToS</h3><p>Pastikan kamu menyetujui syarat dan ketentuan serta menyiapkan data pickup dan kontak yang valid.</p></li>
      <li><h3>Pembayaran saat jendela PO buka</h3><p>Bayar dalam jendela aktif sesuai jadwal yang tersedia. Pembayaran baru dianggap valid setelah admin memverifikasi.</p></li>
    </ol>
    <div class="section-actions">
      <a class="btn" href="{{ route('projects.index') }}">Jelajahi proyek kelas</a>
      <a class="btn sec" href="{{ route('catalog.index') }}">Lihat katalog</a>
    </div>
  </section>

  <section id="pengambilan-barang" class="panel">
    <h2>Pengambilan barang</h2>
    <p>Tidak ada pengiriman kurir. Pesanan diambil langsung di Hari H acara sesuai grup kelas. Gunakan QR pickup yang muncul setelah pembayaran valid untuk mengecek dan menandai barang yang sudah diterima.</p>
    <ol class="steps help-steps">
      <li><h3>Pastikan pembayaran valid</h3><p>Setelah bukti transfer diverifikasi, sistem akan menyiapkan QR pickup untuk setiap grup kelas yang ikut dalam satu pesanan.</p></li>
      <li><h3>Datangi stand/zona kelas</h3><p>Datang ke acara dan temukan zona pickup sesuai grup kelas yang kamu pesan, tidak berlaku untuk pengiriman kurir.</p></li>
      <li><h3>Verifikasi & scan QR</h3><p>Petugas kelas akan memverifikasi identitas pembeli dan memindai QR pickup untuk mencatat serah-terima barang.</p></li>
      <li><h3>Wakil / masalah item</h3><p>Jika diwakilkan, siapkan identitas yang jelas dan informasikan detail barang. Jika ada masalah, lapor ke petugas kelas agar status serah-terima dicatat.</p></li>
    </ol>
    <div class="section-actions">
      <a class="btn" href="{{ route('orders.index') }}">Lihat pesanan saya</a>
      <a class="btn sec" href="{{ route('terms') }}">Baca ToS</a>
    </div>
  </section>

  @if(config('marketplace.pickup_instructions'))
    <section class="panel">
      <h2>Petunjuk pengambilan</h2>
      <p>{{ config('marketplace.pickup_instructions') }}</p>
    </section>
  @endif
</div>
@endsection
