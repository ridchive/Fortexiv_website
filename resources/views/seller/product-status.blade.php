@extends('layouts.app')

@section('title', 'Status Produk — Panel Penjual FORTEXIV')

@section('content')
<div class="section-head" style="margin-bottom: 20px;">
  <div>
    <div class="eyebrow">PORTAL PENJUAL & KELAS</div>
    <h1>Status & Penjualan Produk</h1>
    <p class="muted" style="margin-top: 4px; font-size: 14px;">Pantau performa penjualan, total unit terjual per karya, dan rincian transaksi pembeli.</p>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a class="btn sec" href="{{ route('seller.products.index') }}">Kelola Produk</a>
    <a class="btn" href="{{ route('seller.products.create') }}">+ Tambah Produk</a>
  </div>
</div>

<div class="tabs" style="display: flex; gap: 8px; border-bottom: 1px solid var(--line); margin-bottom: 24px;">
  <a href="{{ route('seller.products.status', ['tab' => 'totals']) }}" 
     class="tab-btn {{ $tab !== 'sales' ? 'active' : '' }}" 
     style="padding: 10px 18px; font-size: 14px; font-weight: 700; text-decoration: none; border-bottom: 2px solid {{ $tab !== 'sales' ? 'var(--dark)' : 'transparent' }}; color: {{ $tab !== 'sales' ? 'var(--dark)' : 'var(--muted)' }};">
    📊 Subtab 1: Total Per Produk
  </a>
  <a href="{{ route('seller.products.status', ['tab' => 'sales']) }}" 
     class="tab-btn {{ $tab === 'sales' ? 'active' : '' }}" 
     style="padding: 10px 18px; font-size: 14px; font-weight: 700; text-decoration: none; border-bottom: 2px solid {{ $tab === 'sales' ? 'var(--dark)' : 'transparent' }}; color: {{ $tab === 'sales' ? 'var(--dark)' : 'var(--muted)' }};">
    🧾 Subtab 2: Rincian Terjual & Pembeli
  </a>
</div>

@if($tab !== 'sales')
  {{-- SUBTAB 1: TOTAL PER PRODUK --}}
  <div class="stats" style="margin-bottom: 24px;">
    <div class="stat">
      <strong>{{ $productsWithStats->count() }}</strong>
      <span>Total jenis produk</span>
    </div>
    <div class="stat">
      <strong>{{ $productsWithStats->sum('sold_units') }} unit</strong>
      <span>Total karya terjual</span>
    </div>
    <div class="stat">
      <strong>{{ \App\Support\Money::format($productsWithStats->sum('revenue')) }}</strong>
      <span>Total omzet kelas</span>
    </div>
    <div class="stat">
      <strong>{{ $productsWithStats->sum('delivered_units') }} unit</strong>
      <span>Sudah diserahkan (Delivered)</span>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Harga Satuan</th>
          <th>Sisa Stok</th>
          <th>Terjual</th>
          <th>Delivered</th>
          <th>Belum Diambil</th>
          <th>Total Omzet</th>
          <th>Status Publikasi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($productsWithStats as $item)
          @php
            $p = $item['product'];
          @endphp
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 12px;">
                @if($p->image)
                  <img src="{{ $p->imageUrl() ?? Storage::disk('public')->url($p->image) }}" alt="{{ $p->name }}" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;">
                @else
                  <div style="width: 44px; height: 44px; border-radius: 6px; background: var(--soft); display: flex; align-items: center; justify-content: center; font-size: 18px;">📦</div>
                @endif
                <div>
                  <div style="font-weight: 700;">{{ $p->name }}</div>
                  <div class="muted" style="font-size: 12px;">ID: #{{ $p->id }}</div>
                </div>
              </div>
            </td>
            <td>{{ $p->category?->name ?? '—' }}</td>
            <td>{{ \App\Support\Money::format($p->price) }}</td>
            <td>
              @if($p->stock > 0)
                <span class="badge open">{{ $p->stock }} unit</span>
              @else
                <span class="badge closed">Habis</span>
              @endif
            </td>
            <td><strong>{{ $item['sold_units'] }} unit</strong></td>
            <td><span class="badge delivered">{{ $item['delivered_units'] }} unit</span></td>
            <td><span class="badge {{ $item['pending_units'] > 0 ? 'waiting_verification' : 'paid' }}">{{ $item['pending_units'] }} unit</span></td>
            <td><strong style="color: var(--good);">{{ \App\Support\Money::format($item['revenue']) }}</strong></td>
            <td>
              @if($p->status === 'published')
                <span class="badge open">Tayang</span>
              @else
                <span class="badge {{ $p->review_status === 'pending_review' ? 'waiting_verification' : 'closed' }}">
                  {{ ['draft' => 'Draf', 'pending_review' => 'Menunggu Review', 'revision_requested' => 'Revisi', 'rejected' => 'Ditolak'][$p->review_status] ?? $p->status }}
                </span>
              @endif
            </td>
            <td>
              <a class="btn sec sm" href="{{ route('seller.products.edit', $p) }}">Edit</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" style="text-align: center; padding: 28px; color: var(--muted);">Belum ada produk yang didaftarkan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

@else
  {{-- SUBTAB 2: RINCIAN PENJUALAN PER PESANAN --}}
  <div class="panel" style="margin-bottom: 20px; padding: 16px 20px;">
    <form method="get" action="{{ route('seller.products.status') }}" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
      <input type="hidden" name="tab" value="sales">
      <div style="flex: 1; min-width: 200px;">
        <label for="filter_product" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Filter Berdasarkan Produk</label>
        <select class="input" id="filter_product" name="product_id">
          <option value="">Semua Produk Kelas</option>
          @foreach($allProducts as $prod)
            <option value="{{ $prod->id }}" @selected((string)$selectedProductId === (string)$prod->id)>{{ $prod->name }}</option>
          @endforeach
        </select>
      </div>

      <div style="width: 200px;">
        <label for="filter_sort" style="font-size: 12px; font-weight: 700; margin-bottom: 4px; display: block;">Urutan Waktu</label>
        <select class="input" id="filter_sort" name="sort">
          <option value="newest" @selected($sort === 'newest')>Baru ke Lama (Terbaru)</option>
          <option value="oldest" @selected($sort === 'oldest')>Lama ke Baru (Terlama)</option>
        </select>
      </div>

      <div style="display: flex; gap: 8px;">
        <button class="btn" type="submit">Terapkan Filter</button>
        @if(request('product_id') || request('sort'))
          <a class="btn sec" href="{{ route('seller.products.status', ['tab' => 'sales']) }}">Reset</a>
        @endif
      </div>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Waktu Pembelian</th>
          <th>Nomor Pesanan</th>
          <th>Produk</th>
          <th>Jumlah</th>
          <th>Total Harga</th>
          <th>Nama Pembeli</th>
          <th>Nomor Kontak / WhatsApp</th>
          <th>Status Serah Terima</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sales as $item)
          @php
            $deliveredUnits = $item->units->where('status', 'delivered')->count();
            $pendingUnits = $item->units->where('status', 'pending')->count();
          @endphp
          <tr>
            <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '—' }}</td>
            <td><strong>{{ $item->order->order_code }}</strong></td>
            <td>
              <strong>{{ $item->product_name }}</strong>
            </td>
            <td>{{ $item->quantity }} unit</td>
            <td><strong>{{ \App\Support\Money::format($item->subtotal) }}</strong></td>
            <td>{{ $item->order->buyer?->name ?? '—' }}</td>
            <td>
              @php
                $phone = $item->order->pickup_contact_phone ?? $item->order->buyer?->phone;
              @endphp
              @if($phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" rel="noopener" style="font-weight: 600; text-decoration: none; color: var(--good);">
                  💬 {{ $phone }}
                </a>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              @if($pendingUnits === 0 && $deliveredUnits > 0)
                <span class="badge delivered">Lengkap (Delivered)</span>
              @elseif($deliveredUnits > 0)
                <span class="badge partially_delivered">Sebagian ({{ $deliveredUnits }}/{{ $item->quantity }})</span>
              @else
                <span class="badge waiting_verification">Menunggu Diambil</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center; padding: 28px; color: var(--muted);">Belum ada riwayat penjualan yang cocok dengan filter.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination" style="margin-top: 20px;">
    {{ $sales->links() }}
  </div>
@endif
@endsection
