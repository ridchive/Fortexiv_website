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
    <div class="field"><label for="password">Kata sandi</label><input class="input" id="password" name="password" type="password" autocomplete="current-password" required></div>
    <label><input type="checkbox" name="remember" value="1"> Ingat saya</label>
    <p class="muted"><a href="{{ route('password.request') }}">Lupa kata sandi?</a></p>
    <button class="btn" style="width:100%;margin-top:18px" type="submit">Masuk</button>
  </form>
  <p class="muted">Belum bergabung dengan FORTEXIV? <a href="{{ route('register') }}">Buat akun</a></p>
</section>
@endsection
