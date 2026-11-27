@extends('layouts.app')

@section('title', 'Katalog produk — FORTEXIV')

@section('content')
<style>
  .catalog-search-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin: 18px 0 20px;
  }
  .catalog-search-fld {
    flex: 1;
    min-width: 0;
    margin: 0;
  }
  .catalog-search-fld label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 700;
  }
  .catalog-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }
  .catalog-search-wrap input {
    width: 100%;
    min-height: 44px;
    padding: 10px 40px 10px 14px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--surface);
    color: var(--ink);
    font: inherit;
    font-size: 14px;
  }
  .catalog-search-wrap input:focus {
    outline: 2px solid var(--good);
    outline-offset: -1px;
  }
  .catalog-search-submit {
    position: absolute;
    right: 4px;
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border: 0;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    border-radius: 6px;
  }
  .catalog-search-submit:hover {
    color: var(--ink);
  }
  .catalog-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    padding: 0 16px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--surface);
    color: var(--ink);
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s, border-color 0.15s;
    flex-shrink: 0;
  }
  .catalog-filter-btn:hover {
    border-color: var(--good);
    background: var(--soft);
  }
  .catalog-filter-btn.has-filters {
    border-color: var(--good);
    color: var(--good);
    background: var(--good-bg);
  }
  .filter-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: var(--good);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
  }

  /* MODAL POPUP */
  .filter-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .filter-modal-card {
    background: var(--surface);
    color: var(--ink);
    border: 1px solid var(--line);
    border-radius: 14px;
    width: min(440px, 100%);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    overflow: hidden;
  }
  .filter-modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
  }
  .filter-modal-head h3 {
    margin: 0;
    font-size: 1.15rem;
  }
  .filter-modal-close {
    background: none;
    border: 0;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    color: var(--muted);
    padding: 4px 8px;
    border-radius: 6px;
  }
  .filter-modal-close:hover {
    color: var(--ink);
    background: var(--soft);
  }
  .filter-modal-body {
    padding: 18px 20px;
    display: grid;
    gap: 14px;
  }
  .filter-modal-body .fld {
    margin: 0;
  }
  .filter-modal-body label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 700;
  }
  .filter-modal-body select {
    width: 100%;
    min-height: 42px;
    padding: 9px 12px;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: var(--surface);
    color: var(--ink);
  }
  .filter-modal-footer {
    padding: 14px 20px 18px;
    border-top: 1px solid var(--line);
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
</style>

<div class="wrap">
  <div class="pgh">
    <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <span>Katalog</span></nav>
    <h1>Katalog produk</h1>
  </div>

  @if(($window['status'] ?? null) === 'closed')
    <div class="status-note closed" role="status">
      <strong>Pemesanan sudah ditutup</strong>
    </div>
  @else
    @include('marketplace.partials.po-notice', ['window' => $window])
  @endif

  @php
    $activeFilterCount = collect([request('class'), request('category'), request('stock')])->filter()->count();
  @endphp

  <form method="get" action="{{ route('catalog.index') }}">
    <div class="catalog-search-row">
      <div class="catalog-search-fld">
        <label for="search">Cari</label>
        <div class="catalog-search-wrap">
          <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Nama produk, kelas, atau proyek">
          <button type="submit" class="catalog-search-submit" aria-label="Cari">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
          </button>
        </div>
      </div>
      <div>
        <button type="button" class="catalog-filter-btn {{ $activeFilterCount > 0 ? 'has-filters' : '' }}" id="openFilterModalBtn">
          <span>Filter</span>
          @if($activeFilterCount > 0)
            <span class="filter-count-badge">{{ $activeFilterCount }}</span>
          @endif
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
      </div>
    </div>

    {{-- MODAL POPUP FILTER --}}
    <div id="filterModal" class="filter-modal-backdrop" style="display: none;">
      <div class="filter-modal-card" role="dialog" aria-modal="true" aria-labelledby="filterModalTitle">
        <div class="filter-modal-head">
          <h3 id="filterModalTitle">Filter Produk</h3>
          <button type="button" class="filter-modal-close" id="closeFilterModalBtn" aria-label="Tutup">&times;</button>
        </div>
        <div class="filter-modal-body">
          <div class="fld">
            <label for="modal-class">Kelas</label>
            <select id="modal-class" name="class">
              <option value="">Semua kelas</option>
              @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((string) request('class') === (string) $class->id)>{{ $class->class_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="fld">
            <label for="modal-category">Kategori</label>
            <select id="modal-category" name="category">
              <option value="">Semua kategori</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="fld">
            <label for="modal-stock">Ketersediaan</label>
            <select id="modal-stock" name="stock">
              <option value="">Semua stok</option>
              <option value="available" @selected(request('stock') === 'available')>Stok tersedia</option>
              <option value="sold_out" @selected(request('stock') === 'sold_out')>Stok habis</option>
            </select>
          </div>
        </div>
        <div class="filter-modal-footer">
          <button class="btn" type="submit" style="width: 100%;">Terapkan filter</button>
          @if(request()->hasAny(['search', 'class', 'category', 'stock']))
            <a class="btn sec" href="{{ route('catalog.index') }}" style="width: 100%; text-align: center;">Atur ulang</a>
          @endif
        </div>
      </div>
    </div>
  </form>

  <script>
    (() => {
      const openBtn = document.getElementById('openFilterModalBtn');
      const closeBtn = document.getElementById('closeFilterModalBtn');
      const modal = document.getElementById('filterModal');

      if (!openBtn || !modal) return;

      function openModal() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }

      openBtn.addEventListener('click', openModal);
      if (closeBtn) closeBtn.addEventListener('click', closeModal);

      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
          closeModal();
        }
      });
    })();
  </script>

  <div class="row sp">
    <p class="muted">Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk</p>
    @if(request()->hasAny(['search', 'class', 'category', 'stock']))
      <span class="chip">Filter aktif</span>
    @endif
  </div>

  <div class="grid catalog-grid" data-reveal>
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
