@extends('layouts.app')

@section('title', 'Syarat & ketentuan — FORTEXIV')

@section('content')
<article class="wrap project-story">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('help') }}">Bantuan</a> / <span>Syarat & ketentuan</span></nav>
  @if($isPreview ?? false)
    <div class="note warn" role="status">Pratinjau belum dipublikasikan. <a href="{{ route('admin.terms.edit') }}">Kembali ke editor</a></div>
  @endif
  <div class="pgh">
    <h1>Syarat & ketentuan</h1>
    <p class="muted">Versi {{ $termsVersion }}</p>
  </div>
  @foreach($sections as $section)
    <section class="panel">
      <span class="eyebrow">Bagian {{ $loop->iteration }}</span>
      <h2>{{ $section['title'] }}</h2>
      <div class="terms-content">{!! $section['content'] !!}</div>
    </section>
  @endforeach
  @if($isPreview ?? false)
    <a class="btn sec" href="{{ route('admin.terms.edit') }}">Kembali ke editor</a>
  @else
    <a class="btn sec" href="{{ url()->previous() }}">Kembali</a>
  @endif
</article>
@endsection
