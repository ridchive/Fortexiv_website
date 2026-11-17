@extends('layouts.app')

@section('title', 'Bergabung dengan FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Bergabung dengan FORTEXIV</h1>
  <p class="muted">Buat akun untuk berbelanja dan mendukung karya proyek kelas.</p>
  <form method="post" action="{{ route('register.store') }}">
    @csrf
    <div class="field"><label for="name">Nama lengkap</label><input class="input" id="name" name="name" value="{{ old('name') }}" maxlength="100" required></div>
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
    <div class="field"><label for="phone">Nomor telepon (opsional)</label><input class="input" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30"></div>
    <div class="field">
      <label for="password">Kata sandi (minimal 8 karakter)</label>
      <div class="password-input">
        <input class="input" id="password" name="password" type="password" autocomplete="new-password" required>
        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <g class="eye-open"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></g>
            <g class="eye-closed"><path d="M3 12c2.2 2.8 5.2 4.5 9 4.5s6.8-1.7 9-4.5"></path><path d="m6 16.8-1 1.7m7-1.5v2m6-2.2 1 1.7"></path></g>
          </svg>
        </button>
      </div>
    </div>
    <div class="field">
      <label for="password_confirmation">Konfirmasi kata sandi</label>
      <div class="password-input">
        <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <g class="eye-open"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></g>
            <g class="eye-closed"><path d="M3 12c2.2 2.8 5.2 4.5 9 4.5s6.8-1.7 9-4.5"></path><path d="m6 16.8-1 1.7m7-1.5v2m6-2.2 1 1.7"></path></g>
          </svg>
        </button>
      </div>
    </div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Buat akun</button>
  </form>
  <p class="muted">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a></p>
</section>
@endsection
