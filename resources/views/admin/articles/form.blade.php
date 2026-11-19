@extends('layouts.app')

@section('title', ($project->exists ? 'Kelola' : 'Buat').' artikel proyek — Admin FORTEXIV')

@section('content')
<section class="form-card article-form-card">
  <div class="eyebrow">EDITOR ADMIN</div><h1>{{ $project->exists ? 'Kelola artikel proyek' : 'Buat artikel proyek' }}</h1>
  <p class="muted">Konten artikel, cerita, publikasi, dan relasi produk hanya dapat diubah oleh Admin.</p>
  <form method="post" enctype="multipart/form-data" action="{{ $project->exists ? route('admin.articles.update', $project) : route('admin.articles.store') }}">
    @csrf @if($project->exists) @method('PUT') @endif
    <div class="field">
      <label for="class_name">Nama kelas</label>
      <select class="input" id="class_name" name="class_name" required>
        <option value="">-- Pilih Nama Kelas --</option>
        @foreach($availableClasses as $c)
          <option value="{{ $c->class_name }}" @selected(old('class_name', $project->class_name) === $c->class_name)>
            {{ $c->class_name }} (No. Akun: #{{ $c->grade }})
          </option>
        @endforeach
        @if($project->class_name && !$availableClasses->contains('class_name', $project->class_name))
          <option value="{{ $project->class_name }}" selected>{{ $project->class_name }}</option>
        @endif
      </select>
      <p class="muted small" style="margin-top: 4px;">Pilih nama kelas yang terdaftar dari Kelola Kelas.</p>
    </div>
    <div class="field"><label for="title">Judul artikel</label><input class="input" id="title" name="title" value="{{ old('title', $project->title) }}" maxlength="160" required></div>
    <div class="field"><label for="introduction">Ringkasan</label><textarea class="input" id="introduction" name="introduction" maxlength="2000" required>{{ old('introduction', $project->introduction) }}</textarea></div>
    <div class="upload-box md-upload-box">
      <div class="upload-box-head">
        <span class="upload-icon" aria-hidden="true">📄</span>
        <div>
          <strong>Opsi Unggah File Markdown (.md)</strong>
          <p>Pilih file .md dari komputer Anda untuk langsung mengisi area teks artikel di bawah.</p>
        </div>
      </div>
      <div class="upload-box-actions">
        <input class="sr-only" id="article-md-file-input" name="md_file" type="file" accept=".md,.markdown,.txt" data-article-markdown-file>
        <label class="btn sec" for="article-md-file-input">📂 Pilih File .md</label>
        <span class="upload-filename-text" role="status" aria-live="polite" data-article-markdown-status></span>
      </div>
    </div>
    <div class="field">
      <div class="markdown-field-head">
        <label for="article_body">Isi Artikel (Mendukung Markdown)</label>
        <span class="muted small">Gunakan ## judul, - poin, atau &gt; kutipan.</span>
      </div>
      <textarea class="input markdown-editor" id="article_body" name="article_body" rows="16" maxlength="50000" data-article-body>{{ old('article_body', $project->contentAsMarkdown()) }}</textarea>
      @error('article_body')<p class="errors">{{ $message }}</p>@enderror
      @error('md_file')<p class="errors">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="image">Gambar sampul (JPG, PNG, WebP maks. 5 MB)</label>
      <div class="project-image-preview">
        <img data-project-image-preview @if($project->image) src="{{ Storage::disk('public')->url($project->image) }}" alt="Sampul {{ $project->title }}" @else alt="" hidden @endif>
        <div data-project-generated-preview @if($project->image) hidden @endif>
          @include('marketplace.partials.project-photo', ['project' => $project, 'alt' => 'Foto referensi proyek'])
        </div>
      </div>
      <p class="muted small">Foto referensi sementara dipilih dari tema proyek. Upload sampul sendiri untuk menggantinya.</p>
      <input class="input" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" data-project-image-input>
      @if($project->image)
        <label class="chk" style="margin:0;"><input type="checkbox" name="remove_image" value="1"><span>Hapus gambar sampul saat ini</span></label>
      @endif
    </div>
    @php
      $classProducts = \App\Models\Product::whereHas('seller', function ($q) use ($project) {
          $q->where('project_id', $project->id)
            ->orWhere('store_name', $project->class_name);
      })->orWhere('project_id', $project->id)->get();
    @endphp
    @if($classProducts->isNotEmpty())
      <div class="panel" style="margin-bottom: 20px;">
        <div class="eyebrow">PRODUK TERKAIT KELAS (OTOMATIS)</div>
        <p class="muted" style="margin-bottom: 12px; font-size: 13px;">
          Produk berikut otomatis terhubung berdasarkan kepemilikan kelas (<strong>{{ $project->class_name }}</strong>):
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px;">
          @foreach($classProducts as $cp)
            <div style="padding: 10px 14px; background: var(--bg); border: 1px solid var(--line); border-radius: 6px;">
              <div style="font-weight: 700; font-size: 14px;">{{ $cp->name }}</div>
              <div class="muted" style="font-size: 12px; margin-top: 2px;">
                {{ \App\Support\Money::format($cp->price) }} &middot; Stok: {{ $cp->stock }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
    <label class="chk"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project->exists ? $project->is_published : true))><span>Publikasikan artikel untuk umum</span></label>
    <div class="stack" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
      <button class="btn" type="submit">Simpan artikel</button>
      @if($project->exists)
        <a class="btn sec" href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener">Lihat Halaman Publik ↗</a>
      @endif
      <a class="btn sec" href="{{ route('admin.articles.index') }}">Kembali</a>
    </div>
  </form>
</section>
@endsection

@push('scripts')
<script>
  (() => {
    const markdownFileInput = document.querySelector('[data-article-markdown-file]');
    const articleBody = document.querySelector('[data-article-body]');
    const markdownFileStatus = document.querySelector('[data-article-markdown-status]');
    if (markdownFileInput && articleBody && markdownFileStatus) {
      markdownFileInput.addEventListener('change', () => {
        const selectedFile = markdownFileInput.files?.[0];
        if (!selectedFile) {
          return;
        }

        if (selectedFile.size > 2 * 1024 * 1024) {
          markdownFileStatus.textContent = 'Ukuran file maksimal 2 MB.';
          markdownFileInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', () => {
          if (typeof reader.result !== 'string') {
            markdownFileStatus.textContent = 'Isi file tidak dapat dibaca.';
            return;
          }

          articleBody.value = reader.result;
          markdownFileStatus.textContent = `${selectedFile.name} berhasil dimuat.`;
        });
        reader.addEventListener('error', () => {
          markdownFileStatus.textContent = 'File tidak dapat dibaca.';
        });
        reader.readAsText(selectedFile);
      });
    }

    const imageInput = document.querySelector('[data-project-image-input]');
    const imagePreview = document.querySelector('[data-project-image-preview]');
    const generatedPreview = document.querySelector('[data-project-generated-preview]');

    if (!imageInput || !imagePreview || !generatedPreview) {
      return;
    }

    const savedImageUrl = imagePreview.getAttribute('src');
    let activePreviewUrl;

    imageInput.addEventListener('change', () => {
      if (activePreviewUrl) {
        URL.revokeObjectURL(activePreviewUrl);
        activePreviewUrl = undefined;
      }

      const selectedFile = imageInput.files?.[0];
      if (selectedFile?.type.startsWith('image/')) {
        activePreviewUrl = URL.createObjectURL(selectedFile);
        imagePreview.src = activePreviewUrl;
        imagePreview.alt = 'Pratinjau sampul proyek';
        imagePreview.hidden = false;
        generatedPreview.hidden = true;

        return;
      }

      imagePreview.hidden = !savedImageUrl;
      generatedPreview.hidden = Boolean(savedImageUrl);
      if (savedImageUrl) {
        imagePreview.src = savedImageUrl;
      }
    });
  })();
</script>
@endpush
