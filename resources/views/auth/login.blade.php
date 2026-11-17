@extends('layouts.app')

@section('title', 'Masuk — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Selamat datang kembali</h1>
  <p class="muted">Masuk untuk melihat proyek dan mendukung karya siswa.</p>
  @if(session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
  @endif
  <form method="post" action="{{ route('login.store') }}">
    @csrf
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
    <div class="field">
      <label for="password">Kata sandi</label>
      <div class="password-input">
        <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
          <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.5 10.5 0 0 0 4-.8"></path></svg>
        </button>
      </div>
    </div>
    <label><input type="checkbox" name="remember" value="1"> Ingat saya</label>
    <p class="muted"><a href="{{ route('password.request') }}">Lupa kata sandi?</a></p>
    <button class="btn" style="width:100%;margin-top:18px" type="submit">Masuk</button>
  </form>
  <p class="muted">Belum bergabung dengan FORTEXIV? <a href="{{ route('register') }}">Buat akun</a></p>
</section>
@endsection
