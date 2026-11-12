@extends('layouts.app')

@section('title', 'Tiket pickup — FORTEXIV')

@section('content')
<div class="wrap">
  <nav class="crumb"><a href="{{ route('orders.show', $order) }}">Detail pesanan</a> / <span>Tiket pickup</span></nav>
  <article class="ticket">
    <div class="eyebrow">FORTEXIV · TIKET PICKUP</div>
    <h1>{{ $order->order_code }}</h1>
    <p>Buyer: <strong>{{ auth()->user()->name }}</strong><br>Pengambil: <strong>{{ $order->pickup_contact_name ?? auth()->user()->name }}</strong><br>Tanggal: <strong>{{ $order->pickup_date->format('d/m/Y') }}</strong></p>
    <p><span class="badge {{ $order->status }}">{{ ['paid' => 'Siap diambil', 'partially_picked_up' => 'Sebagian diserahkan', 'picked_up' => 'Selesai diambil'][$order->status] ?? $order->status }}</span></p>

    <div class="note info"><strong>Perhatian:</strong> Setiap grup kelas memiliki kode pickup sendiri dan diserahkan terpisah. QR di bawah adalah ilustrasi demo dan tidak dapat dipindai; gunakan kode teks yang tampil.</div>
    @foreach($order->fulfillments as $fulfillment)
      <section class="panel" style="text-align:left">
        <div class="row sp">
          <div><span class="eyebrow">GRUP PICKUP</span><h2>{{ $fulfillment->seller->project?->class_name ?? $fulfillment->seller->store_name }}</h2></div>
          <span class="badge {{ $fulfillment->status === 'handed_over' ? 'handed_over' : 'pending' }}">{{ $fulfillment->status === 'handed_over' ? 'Sudah diserahkan' : 'Menunggu pengambilan' }}</span>
        </div>
        <div class="qrbox" data-pickup-card data-pickup-code="{{ $fulfillment->pickup_code }}" data-order-code="{{ $order->order_code }}" data-class-name="{{ $fulfillment->seller->project?->class_name ?? $fulfillment->seller->store_name }}">
          @include('marketplace.partials.pickup-qr-demo')
          <div>
            <p class="eyebrow">KODE PICKUP · FALLBACK TEKS</p>
            <div class="ticket-code">{{ $fulfillment->pickup_code }}</div>
            <p>Tunjukkan kode ini kepada petugas kelas yang sesuai.</p>
            <div class="btns ticket-downloads" aria-label="Unduh ticket pickup">
              <button class="btn sm" type="button" data-download-qr>Unduh QR (PNG)</button>
              <button class="btn sm sec" type="button" data-download-code>Unduh kode (TXT)</button>
            </div>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Produk</th><th>Jumlah</th></tr></thead>
            <tbody>
              @foreach($order->items->where('seller_id', $fulfillment->seller_id) as $item)
                <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if($fulfillment->handed_over_at)
          <p class="muted">Diserahkan {{ $fulfillment->handed_over_at->format('d/m/Y H:i') }}.</p>
        @endif
      </section>
    @endforeach
    <a class="btn sec" href="{{ route('orders.show', $order) }}">Kembali ke detail pesanan</a>
  </article>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const download = (blob, filename) => {
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');

      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    };

    document.querySelectorAll('[data-download-code]').forEach((button) => {
      button.addEventListener('click', () => {
        const card = button.closest('[data-pickup-card]');
        const code = card.dataset.pickupCode;
        const content = [
          'FORTEXIV - KODE PICKUP',
          `Order: ${card.dataset.orderCode}`,
          `Kelas: ${card.dataset.className}`,
          `Kode pickup: ${code}`,
          '',
          'Tunjukkan kode ini kepada petugas kelas yang sesuai.',
        ].join('\n');

        download(new Blob([content], { type: 'text/plain;charset=utf-8' }), `${code}.txt`);
      });
    });

    document.querySelectorAll('[data-download-qr]').forEach((button) => {
      button.addEventListener('click', () => {
        const card = button.closest('[data-pickup-card]');
        const svg = card.querySelector('.qr-demo svg');
        const code = card.dataset.pickupCode;
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        const source = new XMLSerializer().serializeToString(svg);
        const image = new Image();
        const url = URL.createObjectURL(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }));

        image.onload = () => {
          const canvas = document.createElement('canvas');
          const size = 1024;
          const context = canvas.getContext('2d');

          canvas.width = size;
          canvas.height = size;
          context.fillStyle = '#FFFFFF';
          context.fillRect(0, 0, size, size);
          context.drawImage(image, 0, 0, size, size);
          canvas.toBlob((blob) => {
            if (blob) {
              download(blob, `${code}-qr.png`);
            }
          }, 'image/png');
          URL.revokeObjectURL(url);
        };

        image.src = url;
      });
    });
  })();
</script>
@endpush
