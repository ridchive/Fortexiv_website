@extends('layouts.app')

@section('title', 'Kelola Kelas & Akun Seller — FORTEXIV')

@section('content')
<div class="crumb">
  <a href="{{ route('admin.dashboard') }}">Dashboard</a> / <span>Kelola Kelas</span>
</div>

<div class="section-head" style="align-items: center; margin-bottom: 24px;">
  <div>
    <div class="eyebrow">ADMINISTRASI PLATFORM</div>
    <h1>Kelola Kelas & Akun Seller</h1>
    <p class="muted">Atur nomor akun kelas, nama kelas, artikel cerita proyek, dan akun login seller (username & password).</p>
  </div>
</div>

@if(session('status'))
  <div class="flash" role="status" style="margin-bottom: 20px;">{{ session('status') }}</div>
@endif

@if($errors->any())
  <div class="note bad" style="margin-bottom: 20px;">
    <strong>Terjadi kesalahan:</strong>
    <ul style="margin: 6px 0 0; padding-left: 20px;">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@include('admin.classes.partials.class-table')
@endsection
