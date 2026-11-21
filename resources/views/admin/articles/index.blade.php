@extends('layouts.app')

@section('title', 'Artikel proyek — Admin FORTEXIV')

@section('content')
<div class="section-head"><div><span class="eyebrow">KONTEN EDITORIAL</span><h1>Artikel proyek</h1></div><a class="btn" href="{{ route('admin.articles.create') }}">Buat artikel</a></div>
<p class="muted">Artikel proyek dan cerita yang tampil untuk publik hanya dikelola Admin. Seller tidak mendapat akses ke editor ini.</p>
<div class="table-wrap"><table><thead><tr><th>Artikel</th><th>Kelas</th><th>Produk terkait</th><th>Status publikasi</th><th>Terakhir diperbarui</th><th>Tindakan</th></tr></thead><tbody>
  @forelse($projects as $project)
    <tr>
      <td><strong>{{ $project->title }}</strong></td><td>{{ $project->class_name }}</td><td>{{ $project->article_products_count }}</td>
      <td><span class="badge {{ $project->is_published ? 'approved' : 'draft' }}">{{ $project->is_published ? 'Dipublikasikan' : 'Draf/arsip' }}</span></td>
      <td>{{ $project->updated_at->format('d/m/Y H:i') }}</td>
      <td class="stack" style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
        <a class="btn sec sm" href="{{ route('admin.articles.edit', $project) }}">Kelola</a>
        @if($project->is_published)
          <a class="btn sec sm" href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener">Lihat ↗</a>
          <form method="post" action="{{ route('admin.articles.archive', $project) }}" style="display:inline">
            @csrf @method('PATCH')
            <button class="btn sec sm" type="submit" onclick="return confirm('Arsipkan artikel ini dari publik?')">Arsipkan</button>
          </form>
        @else
          <form method="post" action="{{ route('admin.articles.publish', $project) }}" style="display:inline">
            @csrf @method('PATCH')
            <button class="btn sm" type="submit" style="background:var(--good); color:#fff; border-color:var(--good);">Publikasikan</button>
          </form>
        @endif
        <form method="post" action="{{ route('admin.articles.destroy', $project) }}" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten artikel proyek &quot;{{ $project->title }}&quot;? Data kelas dan akun seller akan tetap aman.')">
          @csrf @method('DELETE')
          <button class="btn bad sm" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6">Belum ada artikel proyek.</td></tr>
  @endforelse
</tbody></table></div>
<div class="pagination">{{ $projects->links() }}</div>
@endsection
