@extends('layouts.app')

@section('title', 'Forgot password — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Reset your password</h1>
  <p class="muted">Enter your email address and we will send a password reset link if an account exists.</p>
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
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Send reset link</button>
  </form>
  <p class="muted"><a href="{{ route('login') }}">Back to sign in</a></p>
</section>
@endsection
