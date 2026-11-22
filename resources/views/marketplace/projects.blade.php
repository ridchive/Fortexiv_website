@extends('layouts.app')

@section('title', 'Proyek kelas — FORTEXIV')

@section('content')
<style>
  .filters-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin: 16px 0 24px;
  }
  .pill-select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .pill-select {
    display: inline-flex;
    align-items: center;
    padding: 6px 32px 6px 14px;
    border: 1px solid var(--line);
    border-radius: 999px;
    background-color: var(--surface);
    color: var(--ink);
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23587050' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    max-width: 280px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  :root[data-theme="dark"] .pill-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23A3BB99' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  }
  .pill-select.on {
    border-color: var(--dark);
    background-color: var(--dark);
    color: var(--dark-ink);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23F2F5F3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  }
  .story .chip {
    align-self: flex-start;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
</style>
<div class="wrap">
  <div class="pgh">
    <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Proyek</span></nav>
    <h1>Proyek kelas</h1>
    <p>Setiap kelas menceritakan latar belakang, proses, dan hasil proyeknya. Temukan produk terkait di tiap cerita.</p>
  </div>

  <div class="filters-row" role="group" aria-label="Filter kelas">
    <a class="pill {{ request('class') ? '' : 'on' }}" href="{{ route('projects.index') }}">Semua kelas</a>
    <div class="pill-select-wrap">
      <select class="pill-select {{ request('class') ? 'on' : '' }}" onchange="if(this.value){window.location.href=this.value}else{window.location.href='{{ route('projects.index') }}'}" aria-label="Pilih kelas">
        <option value="{{ route('projects.index') }}" {{ !request('class') ? 'selected' : '' }}>Pilih kelas</option>
        @foreach($classes as $class)
          <option value="{{ route('projects.index', ['class' => $class->id]) }}" @selected((string) request('class') === (string) $class->id)>
            {{ $class->class_name }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid3 project-list-grid">
    @forelse($projects as $project)
      <a class="story" href="{{ route('projects.show', $project) }}">
        <div class="im">@include('marketplace.partials.project-photo', ['project' => $project])</div>
        <span class="chip" title="{{ $project->class_name }}">{{ Str::limit($project->class_name, 28) }}</span>
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
