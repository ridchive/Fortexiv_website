@extends('layouts.app')

@section('title', ($product->exists ? 'Ubah' : 'Tambah').' produk — FORTEXIV')

@section('content')
<section class="form-card">
  <h1>{{ $product->exists ? 'Ubah produk' : 'Tambah produk' }}</h1>
  <p class="muted">Produk baru berstatus draf. Tambahkan stok sebelum menerbitkannya.</p>
  <form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div class="field"><label for="name">Nama produk</label><input class="input" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="120" required></div>
    <div class="field"><label for="description">Deskripsi</label><textarea class="input" id="description" name="description" rows="5" maxlength="10000" required>{{ old('description', $product->description) }}</textarea></div>
    <div class="field"><label for="price">Harga (rupiah penuh)</label><input class="input" id="price" name="price" type="number" min="1" max="100000000" value="{{ old('price', $product->price) }}" required></div>
    <div class="field"><label for="stock">Stok</label><input class="input" id="stock" name="stock" type="number" min="0" max="100000" value="{{ old('stock', $product->exists ? $product->stock : 0) }}" required></div>
    <div class="field"><label for="category_id">Kategori</label><select class="input" id="category_id" name="category_id"><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <div class="field"><label for="project_id">Proyek kelas</label><select class="input" id="project_id" name="project_id"><option value="">Tidak ada proyek terkait</option>@foreach($projects as $projectOption)<option value="{{ $projectOption->id }}" @selected((string) old('project_id', $product->project_id ?? auth()->user()->seller->project_id) === (string) $projectOption->id)>{{ $projectOption->class_name }} · {{ $projectOption->title }}</option>@endforeach</select></div>
    <div class="field"><label for="image">Gambar produk (JPG, PNG, atau WebP, maks. 5 MB)</label><input class="input" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp"></div>
    @if($product->image)<p>Gambar saat ini: <img src="{{ Storage::disk('public')->url($product->image) }}" alt="" style="max-width:120px;border-radius:8px"></p>@endif
    <div class="stack"><button class="btn" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Simpan sebagai draf' }}</button><a class="btn btn-outline" href="{{ route('seller.products.index') }}">Batal</a></div>
  </form>
</section>
@endsection
