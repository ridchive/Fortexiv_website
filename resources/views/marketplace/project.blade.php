@extends('layouts.app')

@section('title', $project->title.' — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home', absolute: false) }}">Beranda</a> / <a href="{{ route('projects.index') }}">Proyek</a> / <span>{{ $project->title }}</span></nav>
  <article class="detail-card project-detail-card">
    <div class="detail-image">
      @if($project->image)
        <img src="{{ Storage::disk('public')->url($project->image) }}" alt="{{ $project->title }}">
      @else
        @include('marketplace.partials.project-photo', ['project' => $project])
      @endif
    </div>
    <div class="detail-copy">
      <div class="row">
        <span class="chip">{{ $project->class_name }}</span>
      </div>
      <h1>{{ $project->title }}</h1>
      <p>{{ $project->introduction }}</p>
      <div class="section-actions">
        <a class="btn" href="{{ route('catalog.index', ['class' => $project->id]) }}">Lihat produk kelas ini</a>
        <a class="btn sec" href="{{ route('preview.laptop') }}" target="_blank" rel="noopener">Pratinjau laptop</a>
        <a class="btn sec" href="{{ route('preview.mobile') }}" target="_blank" rel="noopener">Pratinjau HP</a>
        <a class="btn sec" href="{{ route('projects.index') }}">Kembali ke proyek</a>
      </div>
    </div>
  </article>

  <article class="panel project-story full-width">
    <div class="article-prose">
      {!! Str::markdown($project->contentAsMarkdown(), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
    </div>
  </article>

  <section aria-labelledby="related-products-heading">
    <div class="section-head">
      <div><span class="eyebrow">{{ $project->class_name }}</span><h2 id="related-products-heading">Produk dari proyek ini</h2></div>
      <a href="{{ route('catalog.index', ['class' => $project->id]) }}">Buka katalog kelas →</a>
    </div>
    <div class="grid catalog-grid">
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
