@extends('layouts.app')

@section('title', 'Join FORTEXIV')

@section('content')
<section class="auth-card">
  <h1>Join FORTEXIV</h1>
  <p class="muted">Create an account to shop or share your class project.</p>
  <form method="post" action="{{ route('register.store') }}">
    @csrf
    <div class="field"><label for="name">Full name</label><input class="input" id="name" name="name" value="{{ old('name') }}" maxlength="100" required></div>
    <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
    <div class="field"><label for="phone">Phone (optional)</label><input class="input" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30"></div>
    <div class="field"><label for="role">I want to</label><select class="input" id="role" name="role"><option value="buyer" @selected(old('role', 'buyer') === 'buyer')>Shop as a buyer</option><option value="seller" @selected(old('role') === 'seller')>Sell as a student project</option></select></div>
    <div id="sellerFields">
      <div class="field"><label for="store_name">Store / class name</label><input class="input" id="store_name" name="store_name" value="{{ old('store_name') }}" maxlength="100"></div>
      <div class="field"><label for="project_id">Featured class project (optional)</label><select class="input" id="project_id" name="project_id"><option value="">Choose later</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string) old('project_id') === (string) $project->id)>{{ $project->class_name }} · {{ $project->title }}</option>@endforeach</select></div>
    </div>
    <div class="field"><label for="password">Password (at least 8 characters)</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" required></div>
    <div class="field"><label for="password_confirmation">Confirm password</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="btn" style="width:100%;margin-top:10px" type="submit">Create account</button>
  </form>
  <p class="muted">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
</section>
<script>
  const roleSelect = document.querySelector('#role');
  const sellerFields = document.querySelector('#sellerFields');
  const storeName = document.querySelector('#store_name');
  function toggleSellerFields() {
    const selling = roleSelect.value === 'seller';
    sellerFields.hidden = !selling;
    storeName.required = selling;
  }
  roleSelect.addEventListener('change', toggleSellerFields);
  toggleSellerFields();
</script>
@endsection
