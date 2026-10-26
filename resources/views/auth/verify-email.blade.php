@extends('layouts.app')

@section('title', 'Verify your email — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Verify your email</h1>
  <p class="muted">We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Verify your address to access your account.</p>
  @if(session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
  @endif
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
