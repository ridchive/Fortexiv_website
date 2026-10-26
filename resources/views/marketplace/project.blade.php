@extends('layouts.app')

@section('title', $project->title.' — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home', absolute: false) }}">Beranda</a> / <a href="{{ route('projects.index') }}">Proyek</a> / <span>{{ $project->title }}</span></nav>
  <article class="detail-card">
    <div class="detail-image">
      @if($project->image)
        <img src="{{ Storage::disk('public')->url($project->image) }}" alt="{{ $project->title }}">
      @else
        @include('marketplace.partials.project-art', ['project' => $project])
      @endif
    </div>
    <div class="detail-copy">
      <div class="row">
        <span class="chip">{{ $project->class_name }}</span>
        <span class="muted">KELAS {{ $project->grade }}</span>
      </div>
      <h1>{{ $project->title }}</h1>
      <p>{{ $project->introduction }}</p>
      <div class="section-actions">
        <a class="btn" href="{{ route('catalog.index', ['class' => $project->id]) }}">Lihat produk kelas ini</a>
        <a class="btn sec" href="{{ route('projects.index') }}">Kembali ke proyek</a>
      </div>
    </div>
  </article>

  <div class="two">
    <article class="panel project-story">
      <h2>Latar belakang</h2>
      <p>{{ $project->story }}</p>
      @if($project->goal)
        <h2>Tujuan</h2>
        <p>{{ $project->goal }}</p>
      @endif
      @if($project->process)
        <h2>Proses</h2>
        <ol>@foreach($project->process as $step)<li>{{ $step }}</li>@endforeach</ol>
      @endif
      <h2>Hasil dan target</h2>
      <p>{{ $project->outcome }}</p>
    </article>
    <aside>
      @include('marketplace.partials.po-notice', ['window' => $window])
      <section class="panel">
        <h2>PO dan pengambilan</h2>
        <p>Semua kelas memakai satu periode PO. Tidak ada pengiriman; produk diambil langsung saat acara.</p>
        @if($window['eventAt'])
          <p><strong>Hari H:</strong> {{ $window['eventAt']->format('d/m/Y H:i') }}</p>
        @endif
        @if($window['location'])
          <p><strong>Lokasi:</strong> {{ $window['location'] }}</p>
        @endif
        <a href="{{ route('help') }}">Petunjuk pengambilan →</a>
      </section>
    </aside>
  </div>

  <section aria-labelledby="related-products-heading">
    <div class="section-head">
      <div><span class="eyebrow">{{ $project->class_name }}</span><h2 id="related-products-heading">Produk dari proyek ini</h2></div>
      <a href="{{ route('catalog.index', ['class' => $project->id]) }}">Buka katalog kelas →</a>
    </div>
    <div class="grid">
      @forelse($project->products as $product)
        @include('marketplace.partials.product-card', ['product' => $product, 'window' => $window])
      @empty
        <div class="empty" style="grid-column:1/-1">
          <h3>Produk belum tersedia</h3>
          <p>Cerita proyek tetap dapat dibaca. Produk kelas akan ditampilkan setelah diterbitkan.</p>
          <a class="btn sec" href="{{ route('catalog.index', ['class' => $project->id]) }}">Lihat katalog kelas</a>
        </div>
      @endforelse
    </div>
  </section>
</div>
@endsection
