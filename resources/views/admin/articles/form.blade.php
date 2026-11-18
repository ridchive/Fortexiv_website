@extends('layouts.app')

@section('title', ($project->exists ? 'Kelola' : 'Buat').' artikel proyek — Admin FORTEXIV')

@section('content')
<section class="form-card">
  <div class="eyebrow">EDITOR ADMIN</div><h1>{{ $project->exists ? 'Kelola artikel proyek' : 'Buat artikel proyek' }}</h1>
  <p class="muted">Konten artikel, cerita, publikasi, dan relasi produk hanya dapat diubah oleh Admin.</p>
  <form method="post" enctype="multipart/form-data" action="{{ $project->exists ? route('admin.articles.update', $project) : route('admin.articles.store') }}">
    @csrf @if($project->exists) @method('PUT') @endif
    <div class="field"><label for="class_name">Nama kelas</label><input class="input" id="class_name" name="class_name" value="{{ old('class_name', $project->class_name) }}" maxlength="100" required></div>
    <div class="field"><label for="grade">Tingkat kelas</label><input class="input" id="grade" name="grade" type="number" min="1" max="12" value="{{ old('grade', $project->grade) }}" required></div>
    <div class="field"><label for="title">Judul artikel</label><input class="input" id="title" name="title" value="{{ old('title', $project->title) }}" maxlength="160" required></div>
    <div class="field"><label for="introduction">Ringkasan</label><textarea class="input" id="introduction" name="introduction" maxlength="2000" required>{{ old('introduction', $project->introduction) }}</textarea></div>
    <div class="field"><label for="story">Cerita / latar belakang</label><textarea class="input" id="story" name="story" rows="7" maxlength="10000" required>{{ old('story', $project->story) }}</textarea></div>
    <div class="field"><label for="goal">Tujuan proyek</label><textarea class="input" id="goal" name="goal" maxlength="5000">{{ old('goal', $project->goal) }}</textarea></div>
    <div class="field"><label for="process_steps">Proses (satu langkah per baris)</label><textarea class="input" id="process_steps" name="process_steps" rows="6">{{ old('process_steps', is_array($project->process) ? implode("\n", $project->process) : '') }}</textarea></div>
    <div class="field"><label for="outcome">Hasil dan target</label><textarea class="input" id="outcome" name="outcome" maxlength="5000" required>{{ old('outcome', $project->outcome) }}</textarea></div>
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
    <fieldset class="panel"><legend>Produk terkait</legend><p class="muted">Pilih produk yang terkait dengan cerita proyek ini agar pembeli dapat langsung memesan dari halaman artikel.</p>
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:10px; margin-top:10px;">
        @foreach($products as $productOption)
          <label class="chk" style="padding:8px 12px; background:var(--bg); border:1px solid var(--line); border-radius:6px; display:flex; align-items:flex-start; gap:8px;">
            <input type="checkbox" name="product_ids[]" value="{{ $productOption->id }}" @checked(in_array($productOption->id, old('product_ids', $project->exists ? $project->products->pluck('id')->all() : [])))>
            <span>
              <strong>{{ $productOption->name }}</strong><br>
              <span class="muted" style="font-size:12px;">{{ $productOption->seller->project?->class_name ?? $productOption->seller->store_name }} &middot; {{ \App\Support\Money::format($productOption->price) }}</span>
            </span>
          </label>
        @endforeach
      </div>
    </fieldset>
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
