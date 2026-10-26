@extends('layouts.app')

@section('title', 'Katalog produk — FORTEXIV')

@section('content')
<div class="wrap">
  <div class="pgh">
    <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Katalog</span></nav>
    <h1>Katalog produk</h1>
    <p>Produk dari proyek kelas peserta FORTEXIV.</p>
  </div>

  @include('marketplace.partials.po-notice', ['window' => $window])

  <form class="filters" method="get" action="{{ route('catalog.index') }}">
    <div class="fld">
      <label for="search">Cari</label>
      <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Nama produk, kelas, atau proyek">
    </div>
    <div class="fld">
      <label for="class">Kelas</label>
      <select id="class" name="class">
        <option value="">Semua kelas</option>
        @foreach($classes as $class)
          <option value="{{ $class->id }}" @selected((string) request('class') === (string) $class->id)>{{ $class->class_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="fld">
      <label for="category">Kategori</label>
      <select id="category" name="category">
        <option value="">Semua kategori</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="fld">
      <label for="stock">Ketersediaan</label>
      <select id="stock" name="stock">
        <option value="">Semua stok</option>
        <option value="available" @selected(request('stock') === 'available')>Stok tersedia</option>
        <option value="sold_out" @selected(request('stock') === 'sold_out')>Stok habis</option>
      </select>
    </div>
    <button class="btn" type="submit">Terapkan filter</button>
    @if(request()->hasAny(['search', 'class', 'category', 'stock']))
      <a class="btn sec" href="{{ route('catalog.index') }}">Atur ulang</a>
    @endif
  </form>

  <div class="row sp">
    <p class="muted">Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk</p>
    @if(request()->hasAny(['search', 'class', 'category', 'stock']))
      <span class="chip">Filter aktif</span>
    @endif
  </div>

  <div class="grid">
    @forelse($products as $product)
      @include('marketplace.partials.product-card', ['product' => $product, 'window' => $window])
    @empty
      <div class="empty" style="grid-column:1/-1">
        <h3>Tidak ada produk yang cocok</h3>
        <p>Produk mungkin belum tersedia atau filter pencarian terlalu spesifik.</p>
        <div class="section-actions" style="justify-content:center">
          <a class="btn sec" href="{{ route('catalog.index') }}">Atur ulang filter</a>
          <a class="btn" href="{{ route('projects.index') }}">Jelajahi proyek kelas</a>
        </div>
      </div>
    @endforelse
  </div>
  <div class="pagination">{{ $products->links() }}</div>
</div>
@endsection
