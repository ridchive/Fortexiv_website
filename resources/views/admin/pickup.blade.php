@extends('layouts.app')

@section('title', 'Validasi pickup — FORTEXIV')

@section('content')
<section class="auth-card">
  <div class="eyebrow">SERAH-TERIMA PESANAN</div>
  <h1>Validasi pickup grup kelas</h1>
  <p class="muted">Setiap grup kelas memiliki kode pickup terpisah. Pilih kelas hanya setelah produk pada grup tersebut diserahkan kepada buyer.</p>
  <form method="post" action="{{ route('admin.pickup.validate') }}">@csrf
    <div class="field"><label for="pickup_code">Kode pickup grup kelas</label><input class="input" id="pickup_code" name="pickup_code" placeholder="FTXG-XXXXXXXXXXXX" maxlength="32" value="{{ old('pickup_code') }}" required autofocus></div>
    <div class="field"><label for="seller_id">Kelas yang menyerahkan</label><select class="input" id="seller_id" name="seller_id" required><option value="">Pilih kelas</option>@foreach($sellers as $seller)<option value="{{ $seller->id }}" @selected((string) old('seller_id') === (string) $seller->id)>{{ $seller->project?->class_name ?? $seller->store_name }}</option>@endforeach</select></div>
    <button class="btn" type="submit">Konfirmasi serah-terima kelas</button>
  </form>
  @if(session('pickup_group'))
    @php($group = session('pickup_group'))
    <div class="panel" style="margin-top:20px"><strong>Grup kelas berhasil diserahkan</strong><p>{{ $group['seller_name'] }} · Pesanan {{ $group['order_code'] }} · Buyer {{ $group['buyer_name'] }}.</p></div>
  @endif
</section>
@endsection
