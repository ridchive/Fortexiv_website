@extends('layouts.app')

@section('title', 'Verifikasi Email — FORTEXIV')

@section('content')
<section class="auth-card" style="max-width: 460px; margin: 32px auto;">
  <h1 style="font-size: 1.5rem; margin-bottom: 8px;">Verifikasi Email Anda</h1>
  <p class="muted" style="margin-bottom: 16px; line-height: 1.5;">
    Terima kasih telah mendaftar di FORTEXIV. Kami telah mengirimkan tautan verifikasi ke alamat email <strong>{{ auth()->user()->email }}</strong>.
  </p>

  @if(session('status'))
    <div class="flash" role="status" style="margin-bottom: 18px;">
      {{ session('status') }}
    </div>
  @endif

  <div class="notice" style="padding: 14px 16px; margin-bottom: 20px; font-size: 0.9rem; line-height: 1.45; border-radius: var(--radius);">
    <strong style="display: block; margin-bottom: 4px;">Belum menerima email?</strong>
    <span>Silakan periksa folder <strong>Spam</strong>, <strong>Junk</strong>, atau tab <strong>Promosi</strong> pada akun email Anda. Jika email belum masuk setelah beberapa saat, Anda dapat meminta pengiriman ulang tautan dengan menekan tombol di bawah ini.</span>
  </div>

  <form method="post" action="{{ route('verification.send') }}" style="margin-bottom: 16px;">
    @csrf
    <button class="btn" style="width: 100%;" type="submit">Kirim Ulang Email Verifikasi</button>
  </form>

  <div style="border-top: 1px solid var(--line); padding-top: 16px; margin-top: 20px;">
    <form method="post" action="{{ route('logout') }}">
      @csrf
      <button class="btn btn-outline" style="width: 100%;" type="submit">Keluar / Ganti Akun</button>
    </form>
  </div>
</section>
@endsection
