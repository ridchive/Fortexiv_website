@extends('layouts.app')

@section('title', 'Bergabung dengan FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Bergabung dengan FORTEXIV</h1>
  <p class="muted">Buat akun untuk berbelanja atau membagikan proyek kelas.</p>
  <form method="post" action="{{ route('register.store') }}">
    @csrf
    <div class="field"><label for="name">Nama lengkap</label><input class="input" id="name" name="name" value="{{ old('name') }}" maxlength="100" required></div>
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
    <div class="field"><label for="phone">Nomor telepon (opsional)</label><input class="input" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30"></div>
    <div class="field"><label for="role">Saya ingin</label><select class="input" id="role" name="role"><option value="buyer" @selected(old('role', 'buyer') === 'buyer')>Berbelanja</option><option value="seller" @selected(old('role') === 'seller')>Menjual sebagai proyek siswa</option></select></div>
    <div id="sellerFields">
      <div class="field"><label for="store_name">Nama toko atau kelas</label><input class="input" id="store_name" name="store_name" value="{{ old('store_name') }}" maxlength="100"></div>
      <div class="field"><label for="project_id">Proyek kelas pilihan (opsional)</label><select class="input" id="project_id" name="project_id"><option value="">Pilih nanti</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string) old('project_id') === (string) $project->id)>{{ $project->class_name }} · {{ $project->title }}</option>@endforeach</select></div>
    </div>
    <div class="field"><label for="password">Kata sandi (minimal 8 karakter)</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" required></div>
    <div class="field"><label for="password_confirmation">Konfirmasi kata sandi</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Buat akun</button>
  </form>
  <p class="muted">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a></p>
</section>
<script>
  const roleSelect = document.querySelector('#role');
  const sellerFields = document.querySelector('#sellerFields');
  const storeName = document.querySelector('#store_name');
  function toggleSellerFields() {
    const selling = roleSelect.value === 'seller';
    sellerFields.hidden = !selling;
    storeName.required = selling;
  }
  roleSelect.addEventListener('change', toggleSellerFields);
  toggleSellerFields();
</script>
@endsection
