@extends('layouts.app')

@section('title', 'Proyek kelas — FORTEXIV')

@section('content')
<div class="wrap">
  <div class="pgh">
    <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Proyek</span></nav>
    <h1>Proyek kelas</h1>
    <p>Setiap kelas menceritakan latar belakang, proses, dan hasil proyeknya. Temukan produk terkait di tiap cerita.</p>
  </div>

  <div class="filters" role="group" aria-label="Filter kelas">
    <a class="pill {{ request('class') ? '' : 'on' }}" href="{{ route('projects.index') }}">Semua kelas</a>
    @foreach($classes as $class)
      <a class="pill {{ (string) request('class') === (string) $class->id ? 'on' : '' }}" href="{{ route('projects.index', ['class' => $class->id]) }}">{{ $class->class_name }}</a>
    @endforeach
  </div>

  <div class="grid3">
    @forelse($projects as $project)
      <a class="story" href="{{ route('projects.show', $project) }}">
        <div class="im">@include('marketplace.partials.project-art', ['project' => $project])</div>
        <span class="chip">{{ $project->class_name }}</span>
        <h3>{{ $project->title }}</h3>
        <p>{{ $project->introduction }}</p>
        <span class="btn sec sm" style="align-self:flex-start">Baca proyek · {{ $project->published_products_count }} produk</span>
      </a>
    @empty
      <div class="empty" style="grid-column:1/-1">
        <h3>Belum ada proyek yang cocok</h3>
        <p>Coba pilih kelas lain atau tampilkan semua proyek.</p>
        <a class="btn sec" href="{{ route('projects.index') }}">Tampilkan semua proyek</a>
      </div>
    @endforelse
  </div>
  <div class="pagination">{{ $projects->links() }}</div>
</div>
@endsection
