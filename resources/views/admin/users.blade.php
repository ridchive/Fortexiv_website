@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna — FORTEXIV')

@section('content')
@php($roleLabels = ['buyer' => 'Pembeli', 'seller' => 'Penjual'])

<div class="section-head" style="margin-bottom: 16px;">
  <div>
    <span class="eyebrow">ADMINISTRASI PENGGUNA</span>
    <h1>Kelola Akun</h1>
    <p class="muted">Kelola akun pembeli (buyer) dan penjual (seller). Nonaktifkan akun tanpa menghapus riwayat pesanan.</p>
  </div>
</div>

@if(session('status'))
  <div class="flash" role="status" style="margin-bottom: 20px;">{{ session('status') }}</div>
@endif

{{-- SUBTABS ROLE --}}
<div style="display: flex; gap: 8px; margin: 16px 0 22px; border-bottom: 2px solid var(--line); padding-bottom: 2px; overflow-x: auto;">
  <a
    href="{{ route('admin.users') }}"
    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid {{ empty($role) ? 'var(--good)' : 'transparent' }}; color: {{ empty($role) ? 'var(--good)' : 'var(--muted)' }}; margin-bottom: -4px;"
  >
    👥 Semua Akun <span class="badge" style="font-size: 11px;">{{ $totalCount }}</span>
  </a>
  <a
    href="{{ route('admin.users', ['role' => 'buyer']) }}"
    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid {{ $role === 'buyer' ? 'var(--good)' : 'transparent' }}; color: {{ $role === 'buyer' ? 'var(--good)' : 'var(--muted)' }}; margin-bottom: -4px;"
  >
    🛍️ Buyer (Pembeli) <span class="badge info" style="font-size: 11px;">{{ $buyersCount }}</span>
  </a>
  <a
    href="{{ route('admin.users', ['role' => 'seller']) }}"
    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid {{ $role === 'seller' ? 'var(--good)' : 'transparent' }}; color: {{ $role === 'seller' ? 'var(--good)' : 'var(--muted)' }}; margin-bottom: -4px;"
  >
    🏫 Seller (Penjual / Kelas) <span class="badge open" style="font-size: 11px;">{{ $sellersCount }}</span>
  </a>
</div>

{{-- TABLE BERDASARKAN SUBTAB --}}
<div class="table-wrap">
  <table>
    @if($role === 'buyer')
      {{-- TABLE KHUSUS BUYER --}}
      <thead>
        <tr>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>Nomor Handphone</th>
          <th>Jumlah Pesanan</th>
          <th>Status Akun</th>
          <th>Terdaftar</th>
          <th style="text-align: right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--ink);">{{ $user->name }}</div>
            </td>
            <td>
              <span>{{ $user->email }}</span>
              @if(!$user->email_verified_at)
                <span class="badge warn" style="font-size: 10px; margin-left: 4px;">Belum verif</span>
              @endif
            </td>
            <td>
              @if(filled($user->phone))
                <span style="font-family: monospace; font-size: 13px;">{{ $user->phone }}</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <span class="badge info">{{ $user->orders_count ?? 0 }} Pesanan</span>
            </td>
            <td>
              <span class="badge {{ $user->is_active ? 'paid' : 'rejected' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="muted" style="font-size: 12px;">
              {{ $user->created_at->format('d M Y H:i') }}
            </td>
            <td style="text-align: right;">
              <form method="post" action="{{ route('admin.users.toggle', $user) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button
                  class="btn sm {{ $user->is_active ? 'bad' : 'sec' }}"
                  type="submit"
                  onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun {{ $user->name }}?')"
                >
                  {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="text-align: center; padding: 36px 16px;" class="muted">
              Belum ada akun pembeli (buyer) yang terdaftar.
            </td>
          </tr>
        @endforelse
      </tbody>

    @elseif($role === 'seller')
      {{-- TABLE KHUSUS SELLER --}}
      <thead>
        <tr>
          <th>Nama Seller / Tim</th>
          <th>Username</th>
          <th>Toko / Kelas Terkait</th>
          <th>Email</th>
          <th>Status Akun</th>
          <th style="text-align: right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--ink);">{{ $user->name }}</div>
            </td>
            <td>
              @if($user->username)
                <span class="badge open" style="font-family: monospace; font-size: 12px;">@<span>{{ $user->username }}</span></span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <strong>{{ $user->seller?->store_name ?? $user->seller?->project?->class_name ?? '—' }}</strong>
            </td>
            <td>
              <span>{{ $user->email }}</span>
            </td>
            <td>
              <span class="badge {{ $user->is_active ? 'paid' : 'rejected' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td style="text-align: right;">
              <form method="post" action="{{ route('admin.users.toggle', $user) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button
                  class="btn sm {{ $user->is_active ? 'bad' : 'sec' }}"
                  type="submit"
                  onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun seller {{ $user->name }}?')"
                >
                  {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align: center; padding: 36px 16px;" class="muted">
              Belum ada akun penjual (seller) yang terdaftar.
            </td>
          </tr>
        @endforelse
      </tbody>

    @else
      {{-- TABLE SEMUA PENGGUNA --}}
      <thead>
        <tr>
          <th>Nama Pengguna</th>
          <th>Peran</th>
          <th>Identitas (Username / No. HP)</th>
          <th>Email / Toko</th>
          <th>Status</th>
          <th style="text-align: right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--ink);">{{ $user->name }}</div>
            </td>
            <td>
              @if($user->role === 'buyer')
                <span class="badge info">Buyer</span>
              @elseif($user->role === 'seller')
                <span class="badge open">Seller</span>
              @else
                <span class="badge">{{ ucfirst($user->role) }}</span>
              @endif
            </td>
            <td>
              @if($user->role === 'seller' && $user->username)
                <span style="font-family: monospace; font-size: 12px; font-weight: 600;">@<span>{{ $user->username }}</span></span>
              @elseif($user->phone)
                <span style="font-family: monospace; font-size: 13px;">{{ $user->phone }}</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <div>{{ $user->email }}</div>
              @if($user->seller?->store_name)
                <span class="muted small">Toko: {{ $user->seller->store_name }}</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $user->is_active ? 'paid' : 'rejected' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td style="text-align: right;">
              <form method="post" action="{{ route('admin.users.toggle', $user) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button
                  class="btn sm {{ $user->is_active ? 'bad' : 'sec' }}"
                  type="submit"
                  onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun {{ $user->name }}?')"
                >
                  {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align: center; padding: 36px 16px;" class="muted">
              Belum ada akun pembeli atau penjual yang terdaftar.
            </td>
          </tr>
        @endforelse
      </tbody>
    @endif
  </table>
</div>

<div class="pagination">{{ $users->links() }}</div>
@endsection
