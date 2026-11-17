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
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <g class="eye-open"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></g>
            <g class="eye-closed"><path d="M3 12c2.2 2.8 5.2 4.5 9 4.5s6.8-1.7 9-4.5"></path><path d="m6 16.8-1 1.7m7-1.5v2m6-2.2 1 1.7"></path></g>
          </svg>
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
