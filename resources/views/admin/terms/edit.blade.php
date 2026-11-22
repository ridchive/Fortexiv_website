@extends('layouts.app')

@section('title', 'Terms of Service — Portal Admin')

@section('content')
<style>
  .tos-editor-list, .tos-history-list { display: grid; gap: 12px; }
  .tos-section, .tos-history-entry { margin: 0; padding: 0; overflow: hidden; }
  .tos-section > summary, .tos-history-entry > summary { padding: 18px 22px; cursor: pointer; }
  .tos-section-body, .tos-history-body { padding: 0 22px 22px; }
  .tos-section-controls, .tos-toolbar { display: flex; flex-wrap: wrap; gap: 8px; }
  .tos-field { margin-top: 14px; }
  .tos-field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
  .tos-rich-text { min-height: 170px; max-height: 420px; overflow: auto; padding: 12px; border: 1px solid var(--line); border-radius: 6px; background: var(--surface); color: var(--ink); }
  .tos-rich-text:focus { outline: 2px solid #A9CB5B; outline-offset: 1px; }
  .tos-history-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; color: var(--muted); font-size: 13px; }
  .tos-history-content { padding: 12px 0; border-top: 1px solid var(--line); }
  @media (max-width: 640px) {
    .tos-section > summary, .tos-history-entry > summary { padding: 16px; }
    .tos-section-body, .tos-history-body { padding: 0 16px 16px; }
    .tos-rich-text { min-height: 140px; }
  }
</style>

<div class="section-head" style="margin-top: 0;">
  <div>
    <span class="eyebrow">KONTEN STATIS</span>
    <h1>Terms of Service</h1>
    <p class="muted">Kelola draft, pratinjau, publikasi, dan riwayat versi ketentuan.</p>
  </div>
</div>

<section class="panel">
  <div class="row sp">
    <div>
      <span class="eyebrow">VERSI PUBLIK</span>
      <h2 style="margin: 6px 0;">{{ $publishedVersion->version_label }}</h2>
      <div class="tos-history-meta">
        <span>Dipublikasikan {{ $publishedVersion->published_at?->format('d M Y H:i') ?? '—' }}</span>
        <span>oleh {{ $publishedVersion->publisher?->name ?? 'Sistem' }}</span>
      </div>
    </div>
    <span class="badge verified">Published</span>
  </div>
  @if($draftVersion)
    <p class="muted" style="margin-bottom: 0;">Draft terbaru: revisi #{{ $draftVersion->id }} · disimpan {{ $draftVersion->created_at->format('d M Y H:i') }}.</p>
  @else
    <p class="muted" style="margin-bottom: 0;">Belum ada draft. Editor dimulai dari konten publik saat ini.</p>
  @endif
</section>

<form method="post" action="{{ route('admin.terms.draft') }}" id="terms-editor-form" data-terms-form>
  @csrf
  <div class="section-head">
    <div>
      <span class="eyebrow">EDITOR</span>
      <h2>Section ToS</h2>
      <p class="muted">Buka section untuk mengubah isi. Gunakan tombol naik/turun untuk mengatur urutan.</p>
    </div>
    <button class="btn sec" type="button" id="add-terms-section">+ Tambah section</button>
  </div>

  <div class="tos-editor-list" id="terms-sections">
    @foreach($sections as $index => $section)
      <details class="panel tos-section" data-term-section>
        <summary><strong data-section-title>{{ $section['title'] }}</strong></summary>
        <div class="tos-section-body">
          <div class="tos-section-controls">
            <button class="btn sec sm" type="button" data-move="up" aria-label="Pindahkan section ke atas">Naik ↑</button>
            <button class="btn sec sm" type="button" data-move="down" aria-label="Pindahkan section ke bawah">Turun ↓</button>
            <button class="btn bad sm" type="button" data-remove-section>Hapus section</button>
          </div>
          <div class="tos-field">
            <label for="terms-title-{{ $index }}">Judul section</label>
            <input id="terms-title-{{ $index }}" class="input" type="text" data-field="title" name="sections[{{ $index }}][title]" value="{{ $section['title'] }}" maxlength="160" required>
          </div>
          <div class="tos-field tos-editor">
            <label>Isi section</label>
            <div class="tos-toolbar" role="toolbar" aria-label="Format isi section">
              <button class="btn sec sm" type="button" data-command="bold"><strong>B</strong> Tebal</button>
              <button class="btn sec sm" type="button" data-command="italic"><em>I</em> Miring</button>
              <button class="btn sec sm" type="button" data-command="formatBlock">Paragraf</button>
              <button class="btn sec sm" type="button" data-command="insertOrderedList">Daftar bernomor</button>
              <button class="btn sec sm" type="button" data-command="insertUnorderedList">Bullet</button>
              <button class="btn sec sm" type="button" data-command="createLink">Tautan</button>
            </div>
            <div class="tos-rich-text" contenteditable="true" data-rich-text role="textbox" aria-multiline="true" aria-label="Isi section">{!! $section['content'] !!}</div>
            <input type="hidden" data-field="content" name="sections[{{ $index }}][content]" value="{{ $section['content'] }}">
          </div>
          <input type="hidden" data-field="key" name="sections[{{ $index }}][key]" value="{{ $section['key'] }}">
          <input type="hidden" data-field="position" name="sections[{{ $index }}][position]" value="{{ $index }}">
        </div>
      </details>
    @endforeach
  </div>

  <div class="section-actions">
    <button class="btn" type="submit">Simpan Draft</button>
    <button class="btn sec" type="submit" formaction="{{ route('admin.terms.preview') }}">Preview</button>
    <button class="btn lime" type="submit" formaction="{{ route('admin.terms.publish') }}" data-confirm-publish>Publikasikan</button>
  </div>
</form>

<section style="margin-top: 42px;">
  <div class="section-head">
    <div>
      <span class="eyebrow">AUDIT & REFERENSI</span>
      <h2>Riwayat perubahan</h2>
      <p class="muted">Versi lama tetap tersimpan. Buka konten untuk referensi atau salin ke draft untuk pemulihan.</p>
    </div>
  </div>

  <div class="tos-history-list">
    @foreach($versions as $version)
      @php
        $statusLabels = [
          'published' => 'Published',
          'archived' => 'Arsip publikasi',
          'draft' => 'Draft',
          'superseded' => 'Draft terdahulu',
        ];
      @endphp
      <details class="panel tos-history-entry">
        <summary>
          <div class="row sp">
            <strong>{{ $version->version_label ?? 'Draft #'.$version->id }}</strong>
            <span class="badge {{ $version->status === 'published' ? 'verified' : ($version->status === 'draft' ? 'pending' : '') }}">{{ $statusLabels[$version->status] ?? ucfirst($version->status) }}</span>
          </div>
          <div class="tos-history-meta" style="margin-top: 6px;">
            <span>{{ $version->created_at->format('d M Y H:i') }}</span>
            <span>oleh {{ $version->publisher?->name ?? $version->creator?->name ?? 'Sistem' }}</span>
          </div>
        </summary>
        <div class="tos-history-body">
          <p><strong>Ringkasan:</strong> {{ $version->summary }}</p>
          @if($version->changed_sections)
            <p class="muted"><strong>Bagian berubah:</strong>
              {{ collect($version->changed_sections)->map(fn ($change) => $change['section'].' ('.$change['change'].')')->implode(', ') }}
            </p>
          @endif
          <div class="tos-history-content">
            @foreach($version->sections as $section)
              <section>
                <h3>{{ $section['title'] }}</h3>
                <div class="terms-content">{!! $section['content'] !!}</div>
              </section>
            @endforeach
          </div>
          <form method="post" action="{{ route('admin.terms.restore', $version) }}">
            @csrf
            <button class="btn sec sm" type="submit">Salin versi ini ke Draft</button>
          </form>
        </div>
      </details>
    @endforeach
  </div>
  <div style="margin-top: 16px;">{{ $versions->links() }}</div>
</section>

<template id="terms-section-template">
  <details class="panel tos-section" data-term-section open>
    <summary><strong data-section-title>Bagian baru</strong></summary>
    <div class="tos-section-body">
      <div class="tos-section-controls">
        <button class="btn sec sm" type="button" data-move="up" aria-label="Pindahkan section ke atas">Naik ↑</button>
        <button class="btn sec sm" type="button" data-move="down" aria-label="Pindahkan section ke bawah">Turun ↓</button>
        <button class="btn bad sm" type="button" data-remove-section>Hapus section</button>
      </div>
      <div class="tos-field">
        <label>Judul section</label>
        <input class="input" type="text" data-field="title" value="Bagian baru" maxlength="160" required>
      </div>
      <div class="tos-field tos-editor">
        <label>Isi section</label>
        <div class="tos-toolbar" role="toolbar" aria-label="Format isi section">
          <button class="btn sec sm" type="button" data-command="bold"><strong>B</strong> Tebal</button>
          <button class="btn sec sm" type="button" data-command="italic"><em>I</em> Miring</button>
          <button class="btn sec sm" type="button" data-command="formatBlock">Paragraf</button>
          <button class="btn sec sm" type="button" data-command="insertOrderedList">Daftar bernomor</button>
          <button class="btn sec sm" type="button" data-command="insertUnorderedList">Bullet</button>
          <button class="btn sec sm" type="button" data-command="createLink">Tautan</button>
        </div>
        <div class="tos-rich-text" contenteditable="true" data-rich-text role="textbox" aria-multiline="true" aria-label="Isi section"></div>
        <input type="hidden" data-field="content" value="">
      </div>
      <input type="hidden" data-field="key" value="">
      <input type="hidden" data-field="position" value="">
    </div>
  </details>
</template>

<script>
  (() => {
    const form = document.querySelector('[data-terms-form]');
    const list = document.getElementById('terms-sections');
    const template = document.getElementById('terms-section-template');

    if (!form || !list || !template) return;

    const sections = () => Array.from(list.querySelectorAll('[data-term-section]'));
    const renumber = () => {
      sections().forEach((section, index) => {
        section.querySelectorAll('[data-field]').forEach((field) => {
          field.name = `sections[${index}][${field.dataset.field}]`;
          if (field.dataset.field === 'position') field.value = index;
        });
        section.querySelector('[data-move="up"]').disabled = index === 0;
        section.querySelector('[data-move="down"]').disabled = index === sections().length - 1;
      });
    };

    document.getElementById('add-terms-section').addEventListener('click', () => {
      const fragment = document.importNode(template.content, true);
      const section = fragment.querySelector('[data-term-section]');
      const key = window.crypto?.randomUUID?.() ?? `section-${Date.now()}-${Math.random().toString(36).slice(2)}`;
      section.querySelector('[data-field="key"]').value = key;
      list.append(section);
      renumber();
      section.querySelector('[data-field="title"]').focus();
    });

    list.addEventListener('input', (event) => {
      if (event.target.matches('[data-field="title"]')) {
        event.target.closest('[data-term-section]').querySelector('[data-section-title]').textContent = event.target.value || 'Bagian baru';
      }
    });

    list.addEventListener('mousedown', (event) => {
      if (event.target.closest('[data-command]')) event.preventDefault();
    });

    list.addEventListener('click', (event) => {
      const button = event.target.closest('button');
      if (!button) return;

      const section = button.closest('[data-term-section]');
      if (button.dataset.command) {
        const editor = section.querySelector('[data-rich-text]');
        editor.focus();
        if (button.dataset.command === 'createLink') {
          const url = window.prompt('Masukkan alamat tautan (https:// atau mailto:)');
          if (url) document.execCommand('createLink', false, url);
        } else {
          document.execCommand(button.dataset.command, false, button.dataset.command === 'formatBlock' ? 'p' : null);
        }
      } else if (button.dataset.removeSection !== undefined) {
        if (sections().length === 1) {
          window.alert('Terms of Service harus memiliki setidaknya satu section.');
          return;
        }
        section.remove();
      } else if (button.dataset.move === 'up' && section.previousElementSibling) {
        list.insertBefore(section, section.previousElementSibling);
      } else if (button.dataset.move === 'down' && section.nextElementSibling) {
        list.insertBefore(section.nextElementSibling, section);
      } else {
        return;
      }

      renumber();
    });

    form.addEventListener('submit', (event) => {
      sections().forEach((section) => {
        section.querySelector('[data-field="content"]').value = section.querySelector('[data-rich-text]').innerHTML;
      });

      if (event.submitter?.hasAttribute('data-confirm-publish')
        && !window.confirm('Publikasikan Terms of Service ini sekarang? Versi publik akan langsung berubah.')) {
        event.preventDefault();
      }
    });

    renumber();
  })();
</script>
@endsection
