@extends('layouts.app')

@section('title', 'Lupa kata sandi — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Atur ulang kata sandi</h1>
  <p class="muted">Masukkan alamat email Anda. Jika akun terdaftar, kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
  @if(session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="errors" role="alert">
      @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
      @endforeach
    </div>
  @endif
  <form method="post" action="{{ route('password.email') }}">
    @csrf
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Kirim tautan pengaturan ulang</button>
  </form>
  <p class="muted"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</section>
@endsection
