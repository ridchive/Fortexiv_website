@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_code.' — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('orders.index') }}">Pesanan saya</a> / <span>{{ $order->order_code }}</span></nav>
  <div class="section-head">
    <div><span class="eyebrow">DETAIL PESANAN</span><h1>{{ $order->order_code }}</h1></div>
    <span class="badge {{ $order->status }}">{{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$order->status] ?? $order->status }}</span>
  </div>

  <section class="panel">
    <h2>Ringkasan</h2>
    <div class="two">
      <div>
        <div class="kv"><span>Tanggal dibuat</span><strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong></div>
        <div class="kv"><span>Tanggal pengambilan</span><strong>{{ $order->pickup_date->format('d/m/Y') }}</strong></div>
        <div class="kv"><span>Nama pengambil</span><strong>{{ $order->pickup_contact_name ?? $order->buyer->name }}</strong></div>
        @if($order->pickup_contact_phone)
          <div class="kv"><span>Nomor WhatsApp</span><strong>{{ $order->pickup_contact_phone }}</strong></div>
        @endif
      </div>
      <div>
        @if(config('marketplace.event_location'))
          <div class="kv"><span>Lokasi</span><strong>{{ config('marketplace.event_location') }}</strong></div>
        @endif
        @if(config('marketplace.pickup_instructions'))
          <p>{{ config('marketplace.pickup_instructions') }}</p>
        @endif
        @if($order->terms_accepted_at)
          <p class="muted">Terms of Service versi {{ $order->terms_version }} disetujui pada {{ $order->terms_accepted_at->format('d/m/Y H:i') }}.</p>
        @endif
      </div>
    </div>
  </section>

  <section aria-labelledby="fulfillment-heading">
    <div class="section-head"><div><span class="eyebrow">PICKUP TERPISAH</span><h2 id="fulfillment-heading">Status per grup kelas</h2></div></div>
    @forelse($order->fulfillments as $fulfillment)
      <article class="panel">
        <div class="row sp">
          <div><span class="eyebrow">GRUP KELAS</span><h3>{{ $fulfillment->seller->project?->class_name ?? $fulfillment->seller->store_name }}</h3></div>
          <span class="badge {{ $fulfillment->status === 'handed_over' ? 'handed_over' : ($order->status === 'paid' || $order->status === 'partially_picked_up' ? 'pending' : $order->status) }}">
            {{ $fulfillment->status === 'handed_over' ? 'Sudah diserahkan' : (in_array($order->status, ['paid', 'partially_picked_up'], true) ? 'Menunggu pengambilan' : 'Menunggu pembayaran') }}
          </span>
        </div>
        @foreach($order->items->where('seller_id', $fulfillment->seller_id) as $item)
          <div class="kv"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><strong>{{ \App\Support\Money::format($item->subtotal) }}</strong></div>
        @endforeach
        @if($fulfillment->handed_over_at)
          <p class="muted">Diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
        @endif
      </article>
    @empty
      <div class="empty"><p>Informasi grup pickup belum tersedia untuk pesanan ini.</p></div>
    @endforelse
  </section>

  <section class="panel">
    <div class="kv"><strong>Total pesanan</strong><strong>{{ \App\Support\Money::format($order->total_price) }}</strong></div>
  </section>

  @if($order->status === 'pending_payment')
    <section class="panel">
      <h2>Pembayaran transfer manual</h2>
      @if(config('marketplace.bank_name') && config('marketplace.account_name') && config('marketplace.account_number'))
        <p>Transfer sesuai total pesanan, lalu unggah bukti transfer JPG, PNG, atau PDF (maksimum 5 MB).</p>
        <p><strong>{{ config('marketplace.bank_name') }}</strong><br>Nama rekening: {{ config('marketplace.account_name') }}<br>Nomor rekening: {{ config('marketplace.account_number') }}</p>
        <form method="post" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data">
          @csrf
          <div class="field"><label for="method">Metode / bank transfer</label><input class="input" id="method" name="method" value="{{ old('method') }}" placeholder="Contoh: Transfer BCA" maxlength="50" required></div>
          <div class="field"><label for="proof">Bukti transfer</label><input class="input" id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required></div>
          <button class="btn" type="submit">Kirim bukti transfer</button>
        </form>
      @else
        <p class="note warn">Pesanan tersimpan dan menunggu informasi pembayaran. Jangan melakukan transfer sampai instruksi pembayaran resmi tersedia.</p>
      @endif
    </section>
  @elseif($order->payment)
    <section class="panel">
      <h2>Status pembayaran</h2>
      <p><span class="badge {{ $order->payment->status }}">{{ ['pending' => 'Menunggu verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$order->payment->status] ?? $order->payment->status }}</span></p>
      @if($order->payment->notes)<p>Catatan admin: {{ $order->payment->notes }}</p>@endif
    </section>
  @endif

  @if($order->ticket)
    <a class="btn" href="{{ route('tickets.show', $order) }}">Lihat tiket pickup per kelas</a>
  @endif
</div>
@endsection
