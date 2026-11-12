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
    <div class="field"><label for="image">Gambar sampul (JPG, PNG, WebP maks. 5 MB)</label><input class="input" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp"></div>
    @if($project->image)<p>Gambar sampul saat ini: <img src="{{ Storage::disk('public')->url($project->image) }}" alt="" style="max-width:180px;border-radius:8px"></p>@endif
    <fieldset class="panel"><legend>Produk terkait</legend><p class="muted">Pilih produk yang sesuai dengan cerita proyek ini.</p>
      @foreach($products as $productOption)
        <label class="chk"><input type="checkbox" name="product_ids[]" value="{{ $productOption->id }}" @checked(in_array($productOption->id, old('product_ids', $project->exists ? $project->products->pluck('id')->all() : [])))><span>{{ $productOption->name }} · {{ $productOption->seller->project?->class_name ?? $productOption->seller->store_name }}</span></label>
      @endforeach
    </fieldset>
    <label class="chk"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project->exists ? $project->is_published : true))><span>Publikasikan artikel untuk umum</span></label>
    <div class="stack"><button class="btn" type="submit">Simpan artikel</button><a class="btn sec" href="{{ route('admin.articles.index') }}">Kembali</a></div>
  </form>
</section>
@endsection
