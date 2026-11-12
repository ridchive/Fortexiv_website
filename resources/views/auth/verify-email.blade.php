@extends('layouts.app')

@section('title', 'Verifikasi email — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Verifikasi email Anda</h1>
  <p class="muted">Verifikasi <strong>{{ auth()->user()->email }}</strong> melalui tautan di email atau kode sekali pakai.</p>
  @if(session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
  @endif
  <form method="post" action="{{ route('verification.code.send') }}">
    @csrf
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Kirim kode verifikasi</button>
  </form>
  <form method="post" action="{{ route('verification.code.verify') }}" style="margin-top:18px">
    @csrf
    <div class="field">
      <label for="code">Kode verifikasi 6 digit</label>
      <input class="input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
      @error('code')
        <p class="muted" role="alert">{{ $message }}</p>
      @enderror
    </div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Verifikasi kode</button>
  </form>
  <p class="muted" style="margin-top:18px">Anda juga dapat melakukan verifikasi melalui tautan email.</p>
  <form method="post" action="{{ route('verification.send') }}">
    @csrf
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Kirim ulang email verifikasi</button>
  </form>
  <form method="post" action="{{ route('logout') }}" style="margin-top:14px">
    @csrf
    <button class="btn btn-outline" style="width:100%" type="submit">Keluar</button>
  </form>
</section>
@endsection
