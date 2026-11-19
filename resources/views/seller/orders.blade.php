@extends('layouts.app')

@section('title', 'Pesanan kelas — FORTEXIV')

@section('content')
<div class="wrap">
<div class="pgh"><span class="eyebrow">PORTAL KELAS</span><h1>Pesanan untuk kelasmu</h1><p>Scan QR kelas untuk konfirmasi sekaligus atau QR produk untuk konfirmasi satu per satu.</p><a class="btn" href="{{ route('seller.pickup.scan') }}">Scan Class QR / Scan Product QR</a></div>
<div class="table-wrap"><table><thead><tr><th>Pembeli</th><th>Produk</th><th>Jumlah</th><th>Pesanan</th><th>Pembayaran</th><th>Tanggal pengambilan</th><th>Status pesanan</th><th>Status produk</th></tr></thead><tbody>
  @forelse($items as $item)
    @php($deliveredCount = $item->units->where('status', 'delivered')->count())
    <tr>
      <td>{{ $item->order->buyer->name }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->order->order_code }}</td>
      <td><span class="badge {{ $item->order->payment?->status ?? 'pending' }}">{{ ['pending' => 'Menunggu bukti', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$item->order->payment?->status ?? 'pending'] ?? 'Belum dikirim' }}</span></td>
      <td>{{ $item->order->pickup_date->format('d/m/Y') }}</td>
      <td><span class="badge {{ $item->order->status }}">{{ ['pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi', 'paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil', 'cancelled' => 'Dibatalkan'][$item->order->status] ?? $item->order->status }}</span></td>
      <td><span class="badge {{ $deliveredCount === $item->quantity ? 'delivered' : ($deliveredCount > 0 ? 'partially_delivered' : 'pending') }}">{{ $deliveredCount }} / {{ $item->quantity }} Delivered</span></td>
    </tr>
  @empty
    <tr><td colspan="8">Pesanan untuk produk kelasmu akan muncul di sini.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $items->links() }}</div>
</div>
@endsection
