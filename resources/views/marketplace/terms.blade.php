@extends('layouts.app')

@section('title', 'Terms of Service — FORTEXIV')

@section('content')
<article class="wrap project-story">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('help') }}">Bantuan</a> / <span>Terms of Service</span></nav>
  <div class="pgh">
    <span class="eyebrow">VERSI {{ $termsVersion }}</span>
    <h1>Terms of Service</h1>
    <p>Ketentuan penggunaan prototipe marketplace FORTEXIV.</p>
  </div>
  <section class="panel">
    <h2>Pre-order dan pembayaran</h2>
    <p>Pesanan hanya dapat dibuat selama jendela PO aktif. Buyer bertanggung jawab mengikuti instruksi pembayaran pada detail pesanan dan mengunggah bukti transfer yang benar. Pesanan berstatus menunggu sampai pembayaran diverifikasi admin.</p>
  </section>
  <section class="panel">
    <h2>Pengambilan pesanan</h2>
    <p>FORTEXIV tidak menyediakan pengiriman. Buyer mengambil produk di acara sesuai tanggal dan petunjuk yang ditampilkan pada detail pesanan. Item dari kelas yang berbeda dapat diserahkan pada waktu terpisah, dan setiap grup kelas memiliki status pengambilan sendiri.</p>
  </section>
  <section class="panel">
    <h2>Informasi produk dan stok</h2>
    <p>Ketersediaan dan jumlah pesanan diperiksa kembali saat checkout. Jika pembayaran ditolak admin, pesanan dibatalkan dan stok yang dipesan dikembalikan ke ketersediaan.</p>
  </section>
  <section class="panel">
    <h2>Catatan prototipe</h2>
    <p>Versi ini merupakan prototipe operasional. Ketentuan rinci mengenai pembatalan, pengembalian dana, no-show, dan penyelesaian kendala harus ditetapkan penyelenggara sebelum transaksi produksi dijalankan.</p>
  </section>
  <a class="btn sec" href="{{ url()->previous() }}">Kembali</a>
</article>
@endsection
