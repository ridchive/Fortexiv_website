@extends('layouts.app')

@section('title', 'FORTEXIV — Konsumsi Bijak, Bumi Lestari')

@section('content')
@php
  $stats = $impactStats ?? [];
  $co2Reduced = $stats['co2_reduced'] ?? '24,5';
  $poLabel = match ($window['status']) {
      'open' => 'PO aktif',
      'upcoming' => 'PO belum dibuka',
      'closed' => 'PO ditutup',
      default => 'Jadwal PO belum tersedia',
  };
  $quotes = [
      ['“Kami hitung ulang kebutuhan daun pisang supaya tidak ada yang terbuang.”', 'Tim XI Agribisnis 1'],
      ['“Dua belas botol kaca pertama berasal dari kantin sekolah kami sendiri.”', 'Tim XI Tata Boga 2'],
      ['“Setiap buku catatan sedikit berbeda, dan itu justru yang kami suka.”', 'Tim X Desain Grafis'],
  ];
@endphp

@php
  $hasHeroImage = !empty($eventSettings?->impact_image);
  $heroBgStyle = $hasHeroImage
    ? "background-image: linear-gradient(to right, rgba(13,31,18,0.92) 0%, rgba(20,46,27,0.80) 55%, rgba(20,46,27,0.38) 100%), url('" . Storage::disk('public')->url($eventSettings->impact_image) . "'); background-size: cover; background-position: center;"
    : "";
@endphp

<div class="wrap" style="padding-top:6px">
  <section class="hero hero-impact {{ $hasHeroImage ? 'has-bg-image' : '' }}" @if($hasHeroImage) style="{{ $heroBgStyle }}" @endif aria-label="Green Ecosystem Impact Tracker">
    <div class="tx">
      <span class="kick">Green Ecosystem · Impact Tracker</span>
      <h1>Konsumsi Bijak, Bumi Lestari.</h1>
      <p>Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO terpadu.</p>

      <a href="{{ route('impact.index') }}" class="hero-impact-card" role="region" aria-label="Impact Tracker Green Ecosystem: Klik untuk melihat artikel lengkap">
        <div class="impact-header-row">
          <span class="impact-label">Berhasil mengurangi</span>
          <span class="impact-link-hint">Buka artikel &rarr;</span>
        </div>
        <div class="impact-value">
          <span class="impact-number">{{ $co2Reduced }}</span>
          <span class="impact-unit">kgCO₂e</span>
        </div>
      </a>

      <div class="btns">
        <a class="btn dk" href="{{ route('projects.index') }}">Jelajahi Proyek</a>
        <a class="btn dk" href="{{ route('catalog.index') }}">Lihat Katalog</a>
      </div>
    </div>
    <div class="pic" aria-label="Ilustrasi karya Green Ecosystem">
      @if(!$hasHeroImage)
        @include('marketplace.partials.garden-scene', ['class' => 'main-scene'])
      @else
        <div class="hero-photo-badge">
          <span style="font-size: 1.4rem;">🌱</span>
          <strong>Proyek Nyata Sekolah</strong>
          <p>Mendokumentasikan jejak dekarbonisasi dari kebun, kriya, dan dapur kelas.</p>
          <a href="{{ route('impact.index') }}">Baca artikel lengkap &rarr;</a>
        </div>
      @endif
    </div>
  </section>
  <div class="band">
    <span>{{ $window['banner'] }}</span>
    <span class="chip {{ $window['status'] === 'open' ? 'good' : ($window['status'] === 'upcoming' ? 'warn' : 'bad') }}">{{ $poLabel }}</span>
    @if($window['opensAt'] && $window['closesAt'] && $window['eventAt'])
      <span class="small">PO {{ $window['opensAt']->format('d/m/Y') }} – {{ $window['closesAt']->format('d/m/Y') }} · Pengambilan {{ $window['eventAt']->format('d/m/Y') }}</span>
    @endif
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
          <span class="chip">{{ $project->class_name }}</span>
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
  <div class="wrap split">
    <div>
      <h2>Kelas yang berpartisipasi</h2>
      <div class="subs">
        @forelse($projects as $project)
          <div>
            <h3>{{ $project->class_name }}</h3>
            <p>{{ $project->title }}. {{ $project->published_products_count }} produk dalam katalog.</p>
          </div>
        @empty
          <p class="muted">Informasi kelas akan ditampilkan setelah proyek diterbitkan.</p>
        @endforelse
      </div>
      <div class="btns">
        <a class="btn" href="{{ route('projects.index') }}">Baca proyek</a>
        <a class="btn sec" href="{{ route('catalog.index') }}">Buka katalog</a>
      </div>
    </div>
    <div class="big">@include('marketplace.partials.garden-scene', ['class' => ''])</div>
  </div>
</section>

<section class="sec-b">
  <div class="wrap">
    <div class="sec-h"><h2>Cara pre-order dan pengambilan</h2></div>
    <ol class="steps">
      <li><h3>Pilih produk</h3><p>Boleh dari beberapa kelas sekaligus dalam satu pesanan.</p></li>
      <li><h3>Bayar sebelum tenggat</h3><p>@if($window['paymentDeadline'])Batas pembayaran {{ $window['paymentDeadline']->format('d/m/Y H:i') }}.@endif Pembayaran di prototype ini simulasi.</p></li>
      <li><h3>Terima QR per kelas</h3><p>Tiap kelas yang kamu pesan punya QR pengambilan sendiri.</p></li>
      <li><h3>Ambil di acara</h3><p>@if($window['eventAt']){{ $window['eventAt']->format('d/m/Y') }}@endif{{ $window['location'] ? ', '.Str::before($window['location'], ',') : '' }}. Tanpa ekspedisi.</p></li>
    </ol>
  </div>
</section>

<section class="sec-b">
  <div class="wrap">
    <div class="sec-h"><h2>Catatan dari tim kelas</h2></div>
    <div class="grid3">
      @foreach($quotes as [$quote, $team])
        <figure class="qcard">
          <q>{{ $quote }}</q>
          <figcaption><b>{{ $team }}</b><div class="muted small">Catatan lapangan (contoh)</div></figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>

<section class="ctab">
  <div class="wrap">
    <h2>Siap ikut PO bersama?</h2>
    <div class="btns"><a class="btn" href="{{ route('catalog.index') }}">Lihat katalog</a><a class="btn sec" href="{{ route('help') }}">Baca cara pemesanan</a></div>
  </div>
</section>
@endsection
