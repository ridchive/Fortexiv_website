@extends('layouts.app')

@section('title', 'Sign in — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Welcome back</h1>
  <p class="muted">Sign in to discover projects and support students.</p>
  <form method="post" action="{{ route('login.store') }}">
    @csrf
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
    <div class="field"><label for="password">Password</label><input class="input" id="password" name="password" type="password" autocomplete="current-password" required></div>
    <label><input type="checkbox" name="remember" value="1"> Remember me</label>
    <button class="btn" style="width:100%;margin-top:18px" type="submit">Sign in</button>
  </form>
  <p class="muted">New to FORTEXIV? <a href="{{ route('register') }}">Create an account</a></p>
</section>
@endsection
