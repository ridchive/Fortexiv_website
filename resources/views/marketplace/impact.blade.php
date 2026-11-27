@extends('layouts.app')

@section('title', ($eventSettings->impact_title ?? 'Green Ecosystem Impact Tracker') . ' — FORTEXIV')

@section('content')
@php
  $isAdmin = auth()->check() && auth()->user()->role === 'admin';
  $title = $eventSettings->impact_title ?: 'Jejak Dampak Nyata Green Ecosystem: Konsumsi Bijak, Bumi Lestari';
  $intro = $eventSettings->impact_intro ?: 'Dokumentasi transparansi pengurangan jejak emisi karbon melalui proyek nyata dari kebun, dapur, dan studio karya sekolah.';
  $body = $eventSettings->impactBodyAsMarkdown();
  $hasCoverImage = !empty($eventSettings->impact_image);
  $hasHeroImage = !empty($eventSettings->hero_image);
  $heroTitle = $eventSettings->hero_title ?: 'Konsumsi Bijak, Bumi Lestari.';
  $heroDescription = $eventSettings->hero_description ?: 'Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO terpadu.';
@endphp

<div class="wrap impact-page-container" style="padding-top: 14px; padding-bottom: 48px;">

  <!-- Breadcrumb & Admin Action Bar -->
  <div class="impact-topbar">
    <div class="impact-crumbs">
      <a href="{{ route('home') }}" class="crumb-back">&larr; Kembali ke Beranda</a>
      <span class="crumb-sep">/</span>
      <span class="crumb-current">Green Ecosystem Impact Tracker</span>
    </div>

    @if($isAdmin)
      <div class="impact-admin-trigger">
        <button type="button" onclick="toggleInPageEditor()" id="btn-toggle-editor" class="btn-admin-edit">
          <span>✏️</span> <span id="btn-editor-label">Edit Hero & Artikel GEIT (Admin)</span>
        </button>
      </div>
    @endif
  </div>

  @if(session('status'))
    <div class="alert good" style="margin: 16px 0;">
      <span>✓</span> {{ session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert bad" style="margin: 16px 0;">
      <strong>Terjadi kesalahan validasi:</strong>
      <ul style="margin: 6px 0 0 16px;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($isAdmin)
    <!-- IN-PAGE ADMIN EDITOR FORM -->
    <div id="inpage-admin-editor" class="inpage-editor-card" style="display: {{ $errors->any() ? 'block' : 'none' }};">
      <div class="editor-header">
        <div>
          <span class="editor-badge">PANEL EDITOR ADMIN (IN-PAGE)</span>
          <h2 class="editor-title">Edit Hero Beranda & Konten Artikel GEIT</h2>
        </div>
        <button type="button" onclick="toggleInPageEditor()" class="editor-close-btn">Tutup Editor ✕</button>
      </div>

      <form action="{{ route('admin.impact.update') }}" method="POST" enctype="multipart/form-data" class="editor-form">
        @csrf
        @method('PUT')

        <div class="editor-grid">
          <!-- Left Column: Content, Photo, & MD Upload -->
          <div class="editor-col-main">

            <!-- BAGIAN 1: PENGATURAN HERO BERANDA -->
            <div class="editor-section-header">
              <span class="editor-section-pill">HERO BERANDA</span>
              <strong>Pengaturan Hero Main Page (Beranda)</strong>
            </div>

            <!-- PHOTO UPLOAD SECTION FOR HERO -->
            <div class="upload-box photo-upload-box">
              <div class="upload-box-head">
                <span class="upload-icon">🖼️</span>
                <div>
                  <strong>Foto Background Hero (Beranda)</strong>
                  <p>Pilih foto khusus untuk background banner Hero di halaman utama (Beranda).</p>
                </div>
              </div>

              <div class="upload-box-actions">
                <input type="file" name="hero_image" id="hero-image-input" accept="image/*" class="visually-hidden" onchange="previewSelectedHeroPhoto(event)">
                <button type="button" onclick="document.getElementById('hero-image-input').click()" class="btn btn-sm dk">
                  📁 Pilih Foto Hero Beranda
                </button>
                <span id="selected-hero-photo-name" class="upload-filename-text">
                  {{ $hasHeroImage ? basename($eventSettings->hero_image) : 'Belum ada foto yang dipilih' }}
                </span>
              </div>

              @if($hasHeroImage)
                <div class="current-photo-preview" id="current-hero-photo-box">
                  <img src="{{ $eventSettings->heroImageUrl() ?? Storage::disk('public')->url($eventSettings->hero_image) }}" alt="Preview Foto Hero Aktif" id="img-hero-preview-tag" class="thumb-preview">
                  <div class="photo-meta">
                    <span class="photo-status">✓ Foto aktif sebagai Background Hero Beranda</span>
                    <label class="photo-remove-label">
                      <input type="checkbox" name="remove_hero_image" value="1"> Hapus foto hero (kembali ke background hijau default)
                    </label>
                  </div>
                </div>
              @else
                <div class="current-photo-preview" id="current-hero-photo-box" style="display: none;">
                  <img src="" alt="Preview Foto Hero Baru" id="img-hero-preview-tag" class="thumb-preview">
                  <div class="photo-meta">
                    <span class="photo-status">✓ Foto baru terpilih (akan disimpan)</span>
                  </div>
                </div>
              @endif
            </div>

            <!-- Hero Title & Description -->
            <div class="fld">
              <label for="hero-title-input"><strong>Judul Hero Beranda</strong></label>
              <input type="text" name="hero_title" id="hero-title-input" value="{{ old('hero_title', $heroTitle) }}" class="in" placeholder="Konsumsi Bijak, Bumi Lestari.">
            </div>

            <div class="fld">
              <label for="hero-description-input"><strong>Deskripsi Hero Beranda</strong></label>
              <textarea name="hero_description" id="hero-description-input" rows="2" class="in" placeholder="Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah...">{{ old('hero_description', $heroDescription) }}</textarea>
            </div>

            <!-- BAGIAN 2: PENGATURAN ARTIKEL GEIT -->
            <div class="editor-section-header" style="margin-top: 10px;">
              <span class="editor-section-pill">ARTIKEL GEIT</span>
              <strong>Pengaturan Artikel Green Ecosystem Impact Tracker</strong>
            </div>

            <!-- PHOTO UPLOAD SECTION FOR ARTICLE -->
            <div class="upload-box photo-upload-box">
              <div class="upload-box-head">
                <span class="upload-icon">📸</span>
                <div>
                  <strong>Foto Cover Artikel GEIT</strong>
                  <p>Pilih foto khusus untuk cover di dalam artikel GEIT ini.</p>
                </div>
              </div>

              <div class="upload-box-actions">
                <input type="file" name="impact_image" id="impact-image-input" accept="image/*" class="visually-hidden" onchange="previewSelectedPhoto(event)">
                <button type="button" onclick="document.getElementById('impact-image-input').click()" class="btn btn-sm dk">
                  📁 Pilih Foto Cover Artikel
                </button>
                <span id="selected-photo-name" class="upload-filename-text">
                  {{ $hasCoverImage ? basename($eventSettings->impact_image) : 'Belum ada foto yang dipilih' }}
                </span>
              </div>

              @if($hasCoverImage)
                <div class="current-photo-preview" id="current-photo-box">
                  <img src="{{ $eventSettings->impactImageUrl() ?? Storage::disk('public')->url($eventSettings->impact_image) }}" alt="Preview Foto Artikel Aktif" id="img-preview-tag" class="thumb-preview">
                  <div class="photo-meta">
                    <span class="photo-status">✓ Foto aktif sebagai Cover Artikel GEIT</span>
                    <label class="photo-remove-label">
                      <input type="checkbox" name="remove_image" value="1"> Hapus foto cover artikel
                    </label>
                  </div>
                </div>
              @else
                <div class="current-photo-preview" id="current-photo-box" style="display: none;">
                  <img src="" alt="Preview Foto Baru" id="img-preview-tag" class="thumb-preview">
                  <div class="photo-meta">
                    <span class="photo-status">✓ Foto baru terpilih (akan disimpan)</span>
                  </div>
                </div>
              @endif
            </div>

            <!-- Title & Lead Intro -->
            <div class="fld">
              <label for="impact-title-input"><strong>Judul Artikel GEIT</strong></label>
              <input type="text" name="impact_title" id="impact-title-input" value="{{ old('impact_title', $title) }}" class="in" required>
            </div>

            <div class="fld">
              <label for="impact-intro-input"><strong>Ringkasan / Lead Intro</strong></label>
              <textarea name="impact_intro" id="impact-intro-input" rows="2" class="in" required>{{ old('impact_intro', $intro) }}</textarea>
            </div>

            <!-- MARKDOWN FILE (.md) UPLOAD SECTION -->
            <div class="upload-box md-upload-box">
              <div class="upload-box-head">
                <span class="upload-icon">📄</span>
                <div>
                  <strong>Opsi Unggah File Markdown (.md)</strong>
                  <p>Pilih file .md dari komputer Anda untuk langsung mengisi area teks artikel di bawah.</p>
                </div>
              </div>

              <div class="upload-box-actions">
                <input type="file" name="md_file" id="md-file-input" accept=".md,.markdown,.txt" class="visually-hidden" onchange="handleMdFileUpload(event)">
                <button type="button" onclick="document.getElementById('md-file-input').click()" class="btn btn-sm sec">
                  📂 Pilih File .md
                </button>
                <span id="md-file-status" class="upload-filename-text"></span>
              </div>
            </div>

            <!-- Markdown Content Textarea -->
            <div class="fld">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                <label for="impact-body-input"><strong>Isi Artikel (Mendukung Markdown)</strong></label>
                <span class="hint-small">Gunakan format ## Judul, - poin, > kutipan</span>
              </div>
              <textarea name="impact_body" id="impact-body-input" rows="12" class="in font-mono" style="font-family: monospace; font-size: 0.88rem; line-height: 1.6;">{{ old('impact_body', $body) }}</textarea>
            </div>

          </div>

          <!-- Right Column: Emission Baseline & Product Emission Factors -->
          <div class="editor-col-side">
            <button type="submit" class="btn dk btn-full" style="padding: 12px 18px; font-weight: 700;">
              💾 Simpan & Terapkan ke Artikel & Hero
            </button>
          </div>
        </div>

      </form>
    </div>
  @endif

  <!-- ARTICLE DISPLAY SECTION -->
  <article class="impact-article-card">
    
    <!-- Cover Header Image -->
    <div class="article-cover-wrap" data-reveal>
      @if($hasCoverImage)
        <img src="{{ $eventSettings->impactImageUrl() ?? Storage::disk('public')->url($eventSettings->impact_image) }}" alt="{{ $title }}" class="article-cover-img">
      @else
        <div class="article-cover-fallback">
          @include('marketplace.partials.garden-scene', ['class' => 'cover-fallback-scene'])
        </div>
      @endif
      <div class="article-cover-caption">
        <span>🌱 Dokumentasi Proyek Lingkungan & Kebun Sekolah FORTEXIV</span>
      </div>
    </div>

    <!-- Article Prose Body (Rendered Markdown) -->
    <div class="article-content-body">
      <div class="article-prose">
        {!! Str::markdown($body) !!}
      </div>

      <!-- Product Contribution Table -->
      <div class="product-contribution-section">
        <h2 class="contribution-title">Carbon Emission Avoided per Produk</h2>
        <p class="contribution-desc">CEA dihitung dari snapshot faktor saat checkout untuk item dengan pembayaran terverifikasi. Nilai negatif menunjukkan emisi tambahan. Sumber dan batas fungsional ditetapkan admin; hasil bukan pengukuran emisi aktual.</p>

        <div class="table-responsive">
          <table class="table-impact">
            <tfoot>
              <tr class="total-row"><th colspan="4" style="text-align: right;">Total emisi dihindari:</th><th colspan="2" class="total-cell">{{ $co2Reduced }} @if($co2Reduced !== 'Belum tersedia') kg CO2e @endif</th></tr>
              <tr class="total-row"><th colspan="4" style="text-align: right;">Total emisi tambahan:</th><th colspan="2" class="total-cell">{{ number_format($carbonSummary['total_additional_emissions'], 6, ',', '.') }} kg CO2e</th></tr>
              <tr class="total-row"><th colspan="4" style="text-align: right;">Net emisi dihindari:</th><th colspan="2" class="total-cell">{{ number_format($carbonSummary['net_avoided_emissions'], 6, ',', '.') }} kg CO2e</th></tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Bottom Call To Action -->
      <div class="article-bottom-cta" data-reveal>
        <div>
          <h3>Dukung Gerakan Hijau Sekolah</h3>
          <p>Pesan karya siswa melalui satu periode PO bersama. Seluruh pesanan diambil langsung tanpa emisi kurir.</p>
        </div>
        <div class="cta-btns">
          <a href="{{ route('catalog.index') }}" class="btn dk">Lihat Katalog PO</a>
          <a href="{{ route('projects.index') }}" class="btn sec">Jelajahi Proyek</a>
        </div>
      </div>

    </div>

  </article>

</div>

<style>
  .impact-topbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--line);
    font-size: 0.88rem;
  }
  .impact-crumbs {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .crumb-back {
    color: var(--good);
    font-weight: 700;
    text-decoration: none;
  }
  .crumb-back:hover {
    text-decoration: underline;
  }
  .crumb-sep {
    color: var(--muted);
  }
  .crumb-current {
    font-weight: 600;
    color: var(--ink);
  }
  .btn-admin-edit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    background: #D97706;
    color: #FFFFFF;
    font-weight: 700;
    font-size: 0.82rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
    transition: background .2s ease;
  }
  .btn-admin-edit:hover {
    background: #B45309;
  }

  /* In-Page Editor Card */
  .inpage-editor-card {
    margin: 18px 0 26px;
    padding: 22px;
    border-radius: 14px;
    border: 2px solid rgba(217, 119, 6, 0.5);
    background: var(--surface);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  }
  .editor-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--line);
    margin-bottom: 18px;
  }
  .editor-badge {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    color: #D97706;
    text-transform: uppercase;
  }
  .editor-title {
    font-family: var(--fd);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--ink);
    margin-top: 2px;
  }
  .editor-close-btn {
    padding: 5px 10px;
    font-size: 0.8rem;
    border-radius: 6px;
    border: 1px solid var(--line);
    background: var(--soft);
    cursor: pointer;
    font-weight: 600;
  }
  .editor-close-btn:hover {
    background: #FEE2E2;
    color: #DC2626;
  }
  .editor-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 22px;
  }
  .editor-col-main {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .editor-col-side {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .editor-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    background: rgba(44, 110, 49, 0.08);
    border: 1px solid rgba(44, 110, 49, 0.18);
  }
  .editor-section-pill {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    padding: 2px 8px;
    border-radius: 999px;
    background: var(--dark);
    color: var(--dark-ink);
  }
  .upload-box {
    padding: 14px 16px;
    border-radius: 10px;
    border: 1.5px dashed var(--line);
    background: var(--soft);
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .photo-upload-box {
    border-color: rgba(217, 119, 6, 0.4);
    background: rgba(217, 119, 6, 0.04);
  }
  .md-upload-box {
    border-color: rgba(44, 110, 49, 0.35);
    background: rgba(44, 110, 49, 0.04);
  }
  .upload-box-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }
  .upload-icon {
    font-size: 1.4rem;
    line-height: 1;
  }
  .upload-box-head strong {
    font-size: 0.92rem;
    display: block;
    color: var(--ink);
  }
  .upload-box-head p {
    font-size: 0.8rem;
    color: var(--muted);
    margin-top: 2px;
    line-height: 1.4;
  }
  .upload-box-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }
  .upload-filename-text {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--muted);
  }
  .current-photo-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 8px;
    background: var(--surface);
    border: 1px solid var(--line);
  }
  .thumb-preview {
    width: 80px;
    height: 52px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid var(--line);
  }
  .photo-meta {
    font-size: 0.8rem;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .photo-status {
    color: var(--good);
    font-weight: 700;
  }
  .photo-remove-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #DC2626;
    font-size: 0.78rem;
    cursor: pointer;
  }
  .side-card {
    padding: 14px 16px;
    border-radius: 10px;
    background: var(--soft);
    border: 1px solid var(--line);
  }
  .side-card-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--ink);
  }
  .side-card-desc {
    font-size: 0.78rem;
    color: var(--muted);
    margin-top: 2px;
    margin-bottom: 10px;
  }
  .product-factors-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 320px;
    overflow-y: auto;
    padding-right: 4px;
  }
  .product-factor-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 6px;
    background: var(--surface);
    border: 1px solid var(--line);
  }
  .product-factor-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  .product-factor-name {
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .product-factor-class {
    font-size: 0.72rem;
    color: var(--muted);
  }
  .product-factor-input-wrap {
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .in-number {
    font-family: monospace;
    font-weight: 700;
    text-align: right;
  }
  .in-sm {
    width: 68px;
    padding: 4px 6px;
    font-size: 0.84rem;
  }
  .unit-tag {
    font-size: 0.78rem;
    color: var(--muted);
  }
  .hint-small {
    font-size: 0.76rem;
    color: var(--muted);
  }
  .btn-full {
    width: 100%;
    text-align: center;
  }
  .visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
  }

  /* Article Layout Card */
  .impact-article-card {
    margin-top: 18px;
    border-radius: 16px;
    border: 1px solid var(--line);
    background: var(--surface);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
  }
  .article-header {
    padding: 32px 32px 28px;
    border-bottom: 1px solid var(--line);
    background: linear-gradient(180deg, rgba(238, 242, 232, 0.6) 0%, transparent 100%);
  }
  .article-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
  }
  .article-author {
    font-size: 0.8rem;
    color: var(--muted);
  }
  .article-title {
    font-family: var(--fd);
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 800;
    line-height: 1.15;
    color: var(--ink);
    margin: 0 0 10px;
  }
  .article-lead {
    font-size: clamp(0.96rem, 1.8vw, 1.1rem);
    line-height: 1.6;
    color: var(--muted);
    max-width: 820px;
    margin: 0 0 20px;
  }

  /* Metric Highlight Banner */
  .impact-metric-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 18px 24px;
    border-radius: 12px;
    background: var(--lime);
    color: var(--lime-ink);
    border: 1px solid #8FB846;
  }
  .metric-eyebrow {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.9;
    display: block;
  }
  .metric-number-wrap {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-top: 2px;
  }
  .metric-big-num {
    font-family: var(--fd);
    font-size: clamp(2.4rem, 5vw, 3.2rem);
    font-weight: 800;
    line-height: 1;
  }
  .metric-unit-text {
    font-family: var(--fb);
    font-size: 1.25rem;
    font-weight: 700;
  }
  .metric-footnote {
    font-size: 0.78rem;
    opacity: 0.88;
    display: block;
    margin-top: 4px;
  }

  /* Article Cover Wrap */
  .article-cover-wrap {
    position: relative;
    width: 100%;
    height: clamp(220px, 40vw, 380px);
    background: #0F140F;
    overflow: hidden;
  }
  .article-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .article-cover-fallback {
    width: 100%;
    height: 100%;
    position: relative;
  }
  .cover-fallback-scene {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .article-cover-caption {
    position: absolute;
    bottom: 12px;
    right: 16px;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    color: #FFFFFF;
    font-size: 0.76rem;
    font-weight: 600;
  }

  /* Article Content Body */
  .article-content-body {
    padding: 36px 32px 44px;
    max-width: 860px;
    margin: 0 auto;
  }
  .article-prose {
    font-size: 1rem;
    line-height: 1.75;
    color: var(--ink);
  }
  .article-prose h2 {
    font-family: var(--fd);
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--ink);
    margin-top: 2rem;
    margin-bottom: 0.8rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--line);
  }
  .article-prose h3 {
    font-family: var(--fd);
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ink);
    margin-top: 1.5rem;
    margin-bottom: 0.6rem;
  }
  .article-prose p {
    margin-bottom: 1.15rem;
  }
  .article-prose ul, .article-prose ol {
    margin-left: 1.5rem;
    margin-bottom: 1.2rem;
  }
  .article-prose li {
    margin-bottom: 0.4rem;
  }
  .article-prose blockquote {
    border-left: 3.5px solid var(--good);
    padding: 10px 16px;
    margin: 1.4rem 0;
    font-style: italic;
    color: var(--muted);
    background: rgba(44, 110, 49, 0.05);
    border-radius: 0 8px 8px 0;
  }
  .article-prose strong {
    font-weight: 700;
    color: var(--ink);
  }

  /* Product Contribution Table */
  .product-contribution-section {
    margin-top: 40px;
    padding-top: 28px;
    border-top: 1px solid var(--line);
  }
  .contribution-title {
    font-family: var(--fd);
    font-size: 1.25rem;
    font-weight: 800;
    margin-bottom: 6px;
  }
  .contribution-desc {
    font-size: 0.86rem;
    color: var(--muted);
    margin-bottom: 14px;
  }
  .table-responsive {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--line);
  }
  .table-impact {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
    text-align: left;
  }
  .table-impact th, .table-impact td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--line);
  }
  .table-impact thead th {
    background: var(--soft);
    font-weight: 700;
    color: var(--ink);
  }
  .table-impact tbody tr:hover {
    background: rgba(0, 0, 0, 0.02);
  }
  .factor-cell {
    font-family: monospace;
    font-weight: 700;
    color: var(--good);
  }
  .subtotal-cell {
    font-family: monospace;
    font-weight: 700;
    color: var(--ink);
  }
  .total-row th {
    background: rgba(169, 203, 91, 0.2);
    border-top: 2px solid #8FB846;
  }
  .total-cell {
    font-family: var(--fd);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--good);
  }

  /* Bottom CTA */
  .article-bottom-cta {
    margin-top: 36px;
    padding: 24px;
    border-radius: 12px;
    background: var(--soft);
    border: 1px solid var(--line);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .article-bottom-cta h3 {
    font-family: var(--fd);
    font-size: 1.15rem;
    font-weight: 800;
    margin-bottom: 4px;
  }
  .article-bottom-cta p {
    font-size: 0.85rem;
    color: var(--muted);
  }
  .cta-btns {
    display: flex;
    gap: 8px;
  }

  @media (max-width: 820px) {
    .editor-grid {
      grid-template-columns: minmax(0, 1fr);
    }
    .article-header {
      padding: 22px 18px;
    }
    .article-content-body {
      padding: 24px 18px;
    }
  }
</style>

<script>
  function toggleInPageEditor() {
    const editor = document.getElementById('inpage-admin-editor');
    const isHidden = editor.style.display === 'none' || editor.style.display === '';
    editor.style.display = isHidden ? 'block' : 'none';
    
    const label = document.getElementById('btn-editor-label');
    if (label) {
      label.innerText = isHidden ? 'Tutup Editor' : 'Edit Hero & Artikel GEIT (Admin)';
    }

    if (isHidden) {
      editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function previewSelectedHeroPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    document.getElementById('selected-hero-photo-name').innerText = file.name;
    const photoBox = document.getElementById('current-hero-photo-box');
    const imgTag = document.getElementById('img-hero-preview-tag');

    const reader = new FileReader();
    reader.onload = function(e) {
      imgTag.src = e.target.result;
      photoBox.style.display = 'flex';
    };
    reader.readAsDataURL(file);
  }

  function handleMdFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
      const content = e.target.result;
      document.getElementById('impact-body-input').value = content;
      document.getElementById('md-file-status').innerText = `✓ File "${file.name}" dimuat ke editor!`;
    };
    reader.readAsText(file);
  }

  function previewSelectedPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    document.getElementById('selected-photo-name').innerText = file.name;
    const photoBox = document.getElementById('current-photo-box');
    const imgTag = document.getElementById('img-preview-tag');

    const reader = new FileReader();
    reader.onload = function(e) {
      imgTag.src = e.target.result;
      photoBox.style.display = 'flex';
    };
    reader.readAsDataURL(file);
  }
</script>
@endsection
