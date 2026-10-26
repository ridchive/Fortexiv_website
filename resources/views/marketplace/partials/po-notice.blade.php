@php
  $labels = [
      'open' => ['PO aktif', 'Pesan sebelum '.($window['closesAt']?->format('d/m/Y H:i') ?? 'periode berakhir').'. Pengambilan dilakukan saat acara.'],
      'upcoming' => ['PO belum dibuka', 'Pemesanan dibuka '.($window['opensAt']?->format('d/m/Y H:i') ?? 'sesuai pengumuman berikutnya').'. Produk tetap dapat dilihat.'],
      'closed' => ['PO ditutup', 'Pemesanan baru tidak tersedia. Produk dan cerita kelas tetap dapat dilihat.'],
      'not_configured' => ['Jadwal PO belum tersedia', 'Admin belum mengatur periode pemesanan. Produk tetap dapat dilihat, tetapi pesanan baru belum tersedia.'],
  ];
  [$label, $description] = $labels[$window['status']] ?? $labels['not_configured'];
@endphp
<div class="status-note {{ $window['status'] }}" role="status">
  <strong>{{ $label }}.</strong> {{ $description }}
  @if($window['eventAt'])
    <br><span>Hari H: {{ $window['eventAt']->format('d/m/Y H:i') }}</span>
  @endif
  @if($window['location'])
    <br><span>Lokasi: {{ $window['location'] }}</span>
  @endif
</div>
