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
    <div class="field">
      <label for="password">Kata sandi (minimal 8 karakter)</label>
      <div class="password-input">
        <input class="input" id="password" name="password" type="password" autocomplete="new-password" required>
        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
          <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.5 10.5 0 0 0 4-.8"></path></svg>
        </button>
      </div>
    </div>
    <div class="field">
      <label for="password_confirmation">Konfirmasi kata sandi</label>
      <div class="password-input">
        <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
          <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.5 10.5 0 0 0 4-.8"></path></svg>
        </button>
      </div>
    </div>
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
