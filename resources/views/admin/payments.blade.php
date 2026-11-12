@extends('layouts.app')

@section('title', 'Verifikasi pembayaran — FORTEXIV')

@section('content')
@php($paymentStatusLabels = ['pending' => 'Menunggu verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'])
<h1>Verifikasi pembayaran</h1>
<p class="muted">Tinjau bukti transfer manual. Status pembayaran Midtrans diperbarui otomatis melalui notifikasi pembayaran.</p>
<div class="table-wrap"><table><thead><tr><th>Pesanan</th><th>Pembeli</th><th>Metode transfer</th><th>Total</th><th>Status</th><th>Bukti pembayaran</th><th>Tindakan</th></tr></thead><tbody>
  @forelse($payments as $payment)
    <tr>
      <td>{{ $payment->order->order_code }}</td><td>{{ $payment->order->buyer->name }}</td><td>{{ $payment->method ?? ($payment->gateway === 'midtrans' ? 'Midtrans' : 'Transfer manual') }}</td><td>{{ \App\Support\Money::format($payment->order->total_price) }}</td><td><span class="badge {{ $payment->status }}">{{ $paymentStatusLabels[$payment->status] ?? $payment->status }}</span></td>
      <td>@if($payment->proof_image)<a href="{{ route('admin.payments.proof', $payment) }}">Unduh bukti pembayaran</a>@else — @endif</td>
      <td>
        @if($payment->gateway === 'midtrans')
          Diperbarui otomatis
        @elseif($payment->status === 'pending')
          <div class="stack">
            <form method="post" action="{{ route('admin.payments.verify', $payment) }}">@csrf<button class="btn btn-small">Verifikasi</button></form>
            <form method="post" action="{{ route('admin.payments.reject', $payment) }}" onsubmit="return confirm('Tolak pembayaran ini dan kembalikan stok yang telah dipesan?')">@csrf<input class="input" name="notes" placeholder="Alasan" maxlength="255" required><button class="btn btn-danger btn-small">Tolak</button></form>
          </div>
        @else
          {{ $payment->notes ?? '—' }}
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="7">Tidak ada pembayaran.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $payments->links() }}</div>
@endsection
