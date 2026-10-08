@extends('layouts.app')

@section('title', 'Manage accounts — FORTEXIV')

@section('content')
<h1>Manage accounts</h1>
<p class="muted">Disable accounts without deleting their purchase and pickup history.</p>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Store</th><th>Status</th><th></th></tr></thead><tbody>
  @forelse($users as $user)
    <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ ucfirst($user->role) }}</td><td>{{ $user->seller?->store_name ?? '—' }}</td><td>{{ $user->is_active ? 'Active' : 'Disabled' }}</td><td><form method="post" action="{{ route('admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="btn btn-small {{ $user->is_active ? 'btn-danger' : '' }}" type="submit">{{ $user->is_active ? 'Disable' : 'Enable' }}</button></form></td></tr>
  @empty
    <tr><td colspan="6">No buyer or seller accounts yet.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $users->links() }}</div>
@endsection
