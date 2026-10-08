@extends('layouts.app')

@section('title', 'Account information — FORTEXIV')

@section('content')
<h1>Account information</h1>
<section class="panel" style="max-width:680px;margin-top:18px">
  <div class="eyebrow">PROFILE</div>
  <h2 style="margin:5px 0 18px">{{ $user->name }}</h2>
  <p>Email: <strong>{{ $user->email }}</strong></p>
  <p>Account type: <strong>{{ ucfirst($user->role) }}</strong></p>
  @if($user->phone)<p>Phone: <strong>{{ $user->phone }}</strong></p>@endif
  @if($user->seller)
    <p>Store: <strong>{{ $user->seller->store_name }}</strong></p>
    @if($user->seller->project)<p>Featured class: <strong>{{ $user->seller->project->class_name }} · {{ $user->seller->project->title }}</strong></p>@endif
  @endif
</section>
<p class="muted">Need to change account details? Contact the marketplace administrator.</p>
@endsection
