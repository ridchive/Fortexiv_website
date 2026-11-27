@extends('layouts.app')

@section('title', $project->title.' — FORTEXIV')

@section('content')
<style>
  .detail-image-col {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
  }
  .project-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--surface, #ffffff);
    border: 1px solid var(--line, #D5DDCE);
    color: var(--ink, #14291A);
    text-decoration: none;
    margin-bottom: 12px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: background .15s ease, border-color .15s ease, transform .15s ease, color .15s ease;
  }
  .project-back-btn:hover {
    background: var(--soft, #EEF2E8);
    border-color: var(--dark, #0F140F);
    color: var(--dark, #0F140F);
    transform: translateX(-3px);
  }
  .project-story .article-prose img {
    display: block;
    max-width: min(100%, 460px);
    max-height: 420px;
    width: auto;
    height: auto;
    object-fit: cover;
    border-radius: 12px;
    margin: 1.8rem auto;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  }
</style>
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home', absolute: false) }}">Beranda</a> / <a href="{{ route('projects.index') }}">Proyek</a> / <span>{{ $project->title }}</span></nav>
  <article class="detail-card project-detail-card" data-reveal>
    <div class="detail-image-col">
      <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('projects.index') }}" onclick="if (window.history.length > 1) { window.history.back(); return false; }" class="project-back-btn" aria-label="Kembali ke halaman sebelumnya" title="Kembali ke halaman sebelumnya">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </a>
      <div class="detail-image">
        @include('marketplace.partials.project-photo', ['project' => $project])
      </div>
    </div>
    <div class="detail-copy">
      <div class="row">
        <span class="chip">{{ $project->class_name }}</span>
      </div>
      <h1>{{ $project->title }}</h1>
      <p>{{ $project->introduction }}</p>
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
