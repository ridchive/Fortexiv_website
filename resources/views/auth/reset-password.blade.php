@extends('layouts.app')

@section('title', 'Buat kata sandi baru — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Buat kata sandi baru</h1>
  @if($errors->any())
    <div class="errors" role="alert">
      @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
      @endforeach
    </div>
  @endif
  <form method="post" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required></div>
    <div class="field"><label for="password">Kata sandi baru (minimal 8 karakter)</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" required></div>
    <div class="field"><label for="password_confirmation">Konfirmasi kata sandi baru</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Atur ulang kata sandi</button>
  </form>
</section>
@endsection
