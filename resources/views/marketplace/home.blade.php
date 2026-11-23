@extends('layouts.app')

@section('title', 'FORTEXIV')

@section('content')
@php
  $stats = $impactStats ?? [];
  $co2Reduced = $stats['co2_reduced'] ?? '0,00';
  $poLabel = match ($window['status']) {
      'open' => 'PO aktif',
      'upcoming' => 'PO belum dibuka',
      'closed' => 'PO ditutup',
      default => 'Jadwal PO belum tersedia',
  };
@endphp

@php
  $heroImagePath = $eventSettings?->hero_image ?: $eventSettings?->impact_image;
  $heroImageUrl = $eventSettings?->heroImageUrl() ?: $eventSettings?->impactImageUrl() ?: ($heroImagePath ? Storage::disk('public')->url($heroImagePath) : null);
  $hasHeroImage = !empty($heroImageUrl);
  $heroBgStyle = $hasHeroImage
    ? "background-image: linear-gradient(to right, rgba(13,31,18,0.92) 0%, rgba(20,46,27,0.80) 55%, rgba(20,46,27,0.38) 100%), url('" . $heroImageUrl . "'); background-size: cover; background-position: center;"
    : "";
  $heroTitle = $eventSettings?->hero_title ?: 'Konsumsi Bijak, Bumi Lestari.';
  $heroDescription = $eventSettings?->hero_description ?: 'Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO terpadu.';
@endphp

<div class="wrap" style="padding-top:6px">
  <a href="{{ route('impact.index') }}" class="hero hero-impact {{ $hasHeroImage ? 'has-bg-image' : '' }}" @if($hasHeroImage) style="{{ $heroBgStyle }}" @endif aria-label="Green Ecosystem Impact Tracker">
    <div class="tx">
      <h1>{{ $heroTitle }}</h1>
      <p>{{ $heroDescription }}</p>
    </div>
    <div class="pic" aria-label="Ilustrasi karya Green Ecosystem">
      @if(!$hasHeroImage)
        @include('marketplace.partials.garden-scene', ['class' => 'main-scene'])
      @endif

      <div class="hero-impact-card" role="region" aria-label="Impact Tracker Green Ecosystem: Klik untuk melihat artikel lengkap">
        <div class="impact-header-row">
          <span class="impact-label">Emisi dihindari terverifikasi</span>
          <span class="impact-link-hint">Buka artikel &rarr;</span>
        </div>
        <div class="impact-value">
          <span class="impact-number">{{ $co2Reduced }}</span>
          @if($co2Reduced !== 'Belum tersedia')
            <span class="impact-unit">kg CO2e</span>
          @endif
        </div>
      </div>
    </div>
  </a>
  <div class="band">
    <span style="flex-basis:100%"><strong>Informasi Pre-Order (PO) – Berlaku untuk Seluruh Kelas</strong></span>
    <span>{{ $window['banner'] ?: 'Seluruh pesanan hanya dapat diambil langsung di lokasi acara (tidak melayani pengiriman).' }}</span>
    <span class="chip {{ $window['status'] === 'open' ? 'good' : ($window['status'] === 'upcoming' ? 'warn' : 'bad') }}">{{ $poLabel }}</span>
    <span class="small" style="flex-basis:100%">
      @if($window['opensAt'] && $window['closesAt'])
        Periode PO: {{ $window['opensAt']->format('Y-m') === $window['closesAt']->format('Y-m') ? $window['opensAt']->translatedFormat('d') . ' – ' . $window['closesAt']->translatedFormat('d F Y') : $window['opensAt']->translatedFormat('d F Y') . ' – ' . $window['closesAt']->translatedFormat('d F Y') }}
        @if($window['eventAt'])
          | Jadwal Pengambilan: {{ $window['eventAt']->translatedFormat('d F Y') }}
        @endif
      @else
        Jadwal PO belum tersedia
      @endif
    </span>
  </div>
</div>

<section class="sec-b" style="margin-top:28px">
  <div class="wrap">
    <div class="sec-h"><h2>Cerita dari kelas</h2><a class="btn sec sm" href="{{ route('projects.index') }}">Semua proyek</a></div>
    <p class="story-scroll-hint">Geser ke samping untuk melihat cerita lainnya</p>
    <div class="story-strip" role="region" aria-label="Cerita proyek kelas" tabindex="0">
      @forelse($projects as $project)
        <a class="story" href="{{ route('projects.show', $project) }}">
          <div class="im">@include('marketplace.partials.project-photo', ['project' => $project])</div>
          <span class="chip" title="{{ $project->class_name }}">{{ Str::limit($project->class_name, 28) }}</span>
          <h3>{{ $project->title }}</h3>
          <p>{{ $project->introduction }}</p>
          <span class="btn sec sm" style="align-self:flex-start">Baca proyek</span>
        </a>
      @empty
        <div class="empty"><h3>Cerita kelas sedang disiapkan</h3><p>Proyek yang diterbitkan akan muncul di sini.</p></div>
      @endforelse
    </div>
  </div>
</section>

<section class="sec-b">
  <div class="wrap">
    <div class="sec-h"><h2>Cara Pre-Order dan Pengambilan Pesanan</h2></div>
    <ol class="steps">
      <li><h3>Pilih Produk</h3><p>Anda dapat memesan produk dari berbagai kategori kelas secara bersamaan dalam satu transaksi.</p></li>
      <li><h3>Selesaikan Pembayaran</h3><p>Lakukan pembayaran sebelum batas waktu yang ditentukan agar pesanan dapat diproses.</p></li>
      <li><h3>Dapatkan Kode QR per Kelas</h3><p>Setiap kategori kelas yang dipesan akan memiliki kode QR pengambilan yang terpisah.</p></li>
      <li><h3>Ambil di Lokasi Acara</h3><p>Tunjukkan masing-masing kode QR kepada panitia di stan kelas terkait saat acara berlangsung.</p></li>
    </ol>
  </div>
</section>

<section class="ctab">
  <div class="wrap">
    <h2>Siap ikut PO bersama?</h2>
    <div class="btns"><a class="btn" href="{{ route('catalog.index') }}">Lihat katalog</a><a class="btn sec" href="{{ route('help') }}">Baca cara pemesanan</a></div>
  </div>
</section>
@endsection
