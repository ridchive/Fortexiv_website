@extends('layouts.app')

@section('title', 'Verify your email — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Verify your email</h1>
  <p class="muted">Verify <strong>{{ auth()->user()->email }}</strong> using the link in your email or a one-time code.</p>
  @if(session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
  @endif
  <form method="post" action="{{ route('verification.code.send') }}">
    @csrf
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Send verification code</button>
  </form>
  <form method="post" action="{{ route('verification.code.verify') }}" style="margin-top:18px">
    @csrf
    <div class="field">
      <label for="code">6-digit verification code</label>
      <input class="input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
      @error('code')
        <p class="muted" role="alert">{{ $message }}</p>
      @enderror
    </div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Verify code</button>
  </form>
  <p class="muted" style="margin-top:18px">You can also verify with an email link.</p>
  <form method="post" action="{{ route('verification.send') }}">
    @csrf
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Resend verification email</button>
  </form>
  <form method="post" action="{{ route('logout') }}" style="margin-top:14px">
    @csrf
    <button class="btn btn-outline" style="width:100%" type="submit">Sign out</button>
  </form>
</section>
@endsection
