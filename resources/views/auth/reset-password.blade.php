@extends('layouts.app')

@section('title', 'Choose a new password — FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Choose a new password</h1>
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
    <div class="field"><label for="password">New password (at least 8 characters)</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" required></div>
    <div class="field"><label for="password_confirmation">Confirm new password</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Reset password</button>
  </form>
</section>
@endsection
