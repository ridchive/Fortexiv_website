@extends('layouts.app')

@section('title', 'Pesanan kelas — FORTEXIV')

@section('content')
<div class="wrap">
<div class="pgh"><span class="eyebrow">PORTAL KELAS</span><h1>Pesanan untuk kelasmu</h1><p>Konfirmasikan kode pickup setelah produk pada grup kelasmu benar-benar diserahkan.</p></div>
<div class="table-wrap"><table><thead><tr><th>Buyer</th><th>Produk</th><th>Jumlah</th><th>Pesanan</th><th>Pembayaran</th><th>Tanggal pickup</th><th>Status pesanan</th><th>Serah-terima grup</th></tr></thead><tbody>
  @forelse($items as $item)
    <tr>
      <td>{{ $item->order->buyer->name }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->order->order_code }}</td>
      <td><span class="badge {{ $item->order->payment?->status ?? 'pending' }}">{{ ['pending' => 'Menunggu bukti', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$item->order->payment?->status ?? 'pending'] ?? 'Belum dikirim' }}</span></td>
      <td>{{ $item->order->pickup_date->format('d/m/Y') }}</td>
      <td><span class="badge {{ $item->order->status }}">{{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$item->order->status] ?? $item->order->status }}</span></td>
      <td>
        @if($item->fulfillment?->status === 'handed_over')
          <span class="badge picked_up">Sudah diserahkan</span>
        @elseif($item->fulfillment?->status === 'pending' && in_array($item->order->status, ['paid', 'partially_picked_up'], true) && $item->fulfillment->pickup_code)
          <form class="inline-form" method="post" action="{{ route('seller.pickup') }}">
            @csrf
            <input type="hidden" name="pickup_code" value="{{ $item->fulfillment->pickup_code }}">
            <button class="btn btn-small" type="submit">Konfirmasi serah-terima</button>
          </form>
        @else
          <span class="muted">Menunggu pembayaran terverifikasi</span>
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="8">Pesanan untuk produk kelasmu akan muncul di sini.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $items->links() }}</div>
</div>
@endsection
