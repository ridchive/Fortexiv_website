@extends('layouts.app')

@section('title', 'Verifikasi pembayaran — FORTEXIV')

@section('content')
@php($paymentStatusLabels = ['pending' => 'Menunggu verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'])
<h1>Verifikasi pembayaran</h1>
<p class="muted">Tinjau bukti pembayaran manual. Pembayaran Midtrans lama tetap diperbarui otomatis melalui notifikasi.</p>
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
            <form method="post" action="{{ route('admin.payments.verify', $payment) }}">
              @csrf
              <label for="received_amount_{{ $payment->id }}">Nominal Masuk Sesuai Mutasi (opsional)</label>
              <input class="input" id="received_amount_{{ $payment->id }}" name="received_amount" type="number" min="0" max="4294967295" step="1" placeholder="Nominal rupiah">
              <label for="verify_notes_{{ $payment->id }}">Catatan Admin (opsional)</label>
              <input class="input" id="verify_notes_{{ $payment->id }}" name="notes" maxlength="255" placeholder="Catatan">
              <button class="btn btn-small" type="submit">Verifikasi</button>
            </form>
            <form method="post" action="{{ route('admin.payments.reject', $payment) }}" onsubmit="return confirm('Tolak pembayaran ini? Pembeli dapat mengunggah bukti baru.')">
              @csrf
              <label for="reject_notes_{{ $payment->id }}">Alasan Penolakan</label>
              <input class="input" id="reject_notes_{{ $payment->id }}" name="notes" placeholder="Alasan" maxlength="255" required>
              <button class="btn btn-danger btn-small" type="submit">Tolak</button>
            </form>
          </div>
        @else
          @if($payment->received_amount !== null)
            <div>Nominal masuk: {{ \App\Support\Money::format($payment->received_amount) }}</div>
          @endif
          <div>{{ $payment->notes ?? '—' }}</div>
        @endif
        @if($payment->status === 'verified')
          @foreach($payment->order->items as $item)
            <form method="post" action="{{ route('admin.carbon-impact.adjust', $item) }}" style="margin-top:8px;">
              @csrf
              <div class="muted">{{ $item->product_name }} · {{ $item->quantity }} unit · hasil CEA {{ $item->carbon_contribution === null ? 'belum lengkap' : number_format((float) $item->carbon_contribution, 6, ',', '.').' kg CO2e' }}</div>
              <input class="input" name="corrected_contribution" type="number" step="0.000001" placeholder="CEA terkoreksi (boleh negatif, kg CO2e)">
              <input class="input" name="reason" maxlength="2000" placeholder="Alasan koreksi" required>
              <button class="btn btn-small" type="submit">Koreksi jejak karbon</button>
            </form>
          @endforeach
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="7">Tidak ada pembayaran.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $payments->links() }}</div>
@endsection
