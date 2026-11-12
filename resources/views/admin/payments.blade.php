@extends('layouts.app')

@section('title', 'Payment review — FORTEXIV')

@section('content')
<h1>Payment review</h1>
<p class="muted">Review manual transfer receipts. Midtrans payments are updated automatically by payment notifications.</p>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Buyer</th><th>Transfer method</th><th>Total</th><th>Status</th><th>Receipt</th><th>Decision</th></tr></thead><tbody>
  @forelse($payments as $payment)
    <tr>
      <td>{{ $payment->order->order_code }}</td><td>{{ $payment->order->buyer->name }}</td><td>{{ $payment->method ?? ucfirst($payment->gateway) }}</td><td>{{ \App\Support\Money::format($payment->order->total_price) }}</td><td><span class="badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
      <td>@if($payment->proof_image)<a href="{{ route('admin.payments.proof', $payment) }}">Download receipt</a>@else — @endif</td>
      <td>
        @if($payment->gateway === 'midtrans')
          Updated automatically
        @elseif($payment->status === 'pending')
          <div class="stack">
            <form method="post" action="{{ route('admin.payments.verify', $payment) }}">@csrf<button class="btn btn-small">Verify</button></form>
            <form method="post" action="{{ route('admin.payments.reject', $payment) }}" onsubmit="return confirm('Reject this payment and return the reserved stock?')">@csrf<input class="input" name="notes" placeholder="Reason" maxlength="255" required><button class="btn btn-danger btn-small">Reject</button></form>
          </div>
        @else
          {{ $payment->notes ?? '—' }}
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="7">No payments found.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $payments->links() }}</div>
@endsection
