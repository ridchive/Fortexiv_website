@extends('layouts.app')

@section('title', 'Kelola akun — FORTEXIV')

@section('content')
@php($roleLabels = ['buyer' => 'Pembeli', 'seller' => 'Penjual'])
<h1>Kelola akun</h1>
<p class="muted">Nonaktifkan akun tanpa menghapus riwayat pembelian dan pengambilan.</p>
<div class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Toko</th><th>Status</th><th></th></tr></thead><tbody>
  @forelse($users as $user)
    <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $roleLabels[$user->role] ?? $user->role }}</td><td>{{ $user->seller?->store_name ?? '—' }}</td><td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td><td><form method="post" action="{{ route('admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="btn btn-small {{ $user->is_active ? 'btn-danger' : '' }}" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td></tr>
  @empty
    <tr><td colspan="6">Belum ada akun pembeli atau penjual.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $users->links() }}</div>
@endsection
