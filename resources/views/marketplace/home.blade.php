@extends('layouts.app')

@section('title', 'FORTEXIV — Konsumsi Bijak, Bumi Lestari')

@section('content')
@php
  $heroSlides = collect([[
      'kicker' => 'Green Ecosystem · PO bersama',
      'title' => 'Konsumsi Bijak, Bumi Lestari.',
      'introduction' => 'Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO.',
      'project' => null,
  ]])->concat($projects->take(2)->map(fn ($project) => [
      'kicker' => $project->class_name,
      'title' => $project->title,
      'introduction' => $project->introduction,
      'project' => $project,
  ]));
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

<div class="wrap" style="padding-top:6px">
  <div class="hero-carousel" data-hero-carousel>
    @foreach($heroSlides as $index => $slide)
      <section class="hero" data-hero-slide @if($index > 0) hidden @endif aria-roledescription="slide" aria-label="Slide {{ $index + 1 }} dari {{ $heroSlides->count() }}">
        <div class="tx">
          <span class="kick">{{ $slide['kicker'] }}</span>
          <h1>{{ $slide['title'] }}</h1>
          <p>{{ $slide['introduction'] }}</p>
          <div class="btns">
            @if($slide['project'])
              <a class="btn dk" href="{{ route('projects.show', $slide['project']) }}">Baca Proyek</a>
              <a class="btn dk" href="{{ route('catalog.index', ['class' => $slide['project']->id]) }}">Lihat Produk Kelas</a>
            @else
              <a class="btn dk" href="{{ route('projects.index') }}">Jelajahi Proyek</a>
              <a class="btn dk" href="{{ route('catalog.index') }}">Lihat Katalog</a>
            @endif
          </div>
        </div>
        <div class="pic" aria-label="Ilustrasi karya kelas">
          @if($slide['project'])
            @include('marketplace.partials.project-art', ['project' => $slide['project'], 'class' => 'main-scene'])
          @else
            @include('marketplace.partials.garden-scene', ['class' => 'main-scene'])
          @endif
          <div class="feature-art" aria-hidden="true">@include('marketplace.partials.product-art', ['category' => 'Makanan'])</div>
          <div class="feature-art secondary" aria-hidden="true">@include('marketplace.partials.product-art', ['category' => 'Dekorasi'])</div>
        </div>
      </section>
    @endforeach
    @if($heroSlides->count() > 1)
      <div class="hero-dots" role="tablist" aria-label="Pilih proyek unggulan">
        @foreach($heroSlides as $index => $slide)
          <button type="button" data-hero-index="{{ $index }}" aria-label="Tampilkan slide {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"></button>
        @endforeach
      </div>
    @endif
  </div>
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
    <div class="grid3">
      @forelse($projects->take(3) as $project)
        <a class="story" href="{{ route('projects.show', $project) }}">
          <div class="im">@include('marketplace.partials.project-art', ['project' => $project])</div>
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

@push('scripts')
<script>
(() => {
  const carousel = document.querySelector('[data-hero-carousel]');
  if (!carousel) {
    return;
  }

  const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
  const controls = [...carousel.querySelectorAll('[data-hero-index]')];

  controls.forEach((control) => {
    control.addEventListener('click', () => {
      const activeIndex = Number(control.dataset.heroIndex);

      slides.forEach((slide, index) => {
        slide.hidden = index !== activeIndex;
      });
      controls.forEach((button, index) => {
        button.setAttribute('aria-pressed', index === activeIndex ? 'true' : 'false');
      });
    });
  });
})();
</script>
@endpush
