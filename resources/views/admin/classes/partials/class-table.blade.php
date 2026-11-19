{{-- ACTION TOOLBAR --}}
<div class="panel" style="margin-bottom: 20px; padding: 18px 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
  <div>
    <div class="eyebrow">PENGATURAN KELAS & AKUN SELLER</div>
    <div style="font-weight: 700; font-size: 1.15rem; margin-top: 2px;">
      Daftar Akun Kelas & Hak Akses
    </div>
    <div class="muted" style="font-size: 13px;">
      Total <strong>{{ $classes->count() }} Kelas</strong> &middot; 
      <strong>{{ $classes->filter(fn($c) => $c->seller?->user)->count() }} Kelas Memiliki Akun Seller</strong>.
      Setiap kelas memiliki nomor akun unik dan artikel tersendiri.
    </div>
  </div>
  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
    <button class="btn" type="button" onclick="openCreateClassModal()">+ Tambah Kelas Baru</button>
    <button class="btn sec" type="button" onclick="toggleQuickAddRow()">⚡ Form Cepat Tambah</button>
    <form method="post" action="{{ route('admin.classes.initialize') }}" onsubmit="return confirm('Inisialisasi 11 Level kelas (Level 1 sampai 11) jika belum ada?')" style="display: inline;">
      @csrf
      <button class="btn line" type="submit" style="border: 1px solid var(--line);">Inisialisasi 11 Level</button>
    </form>
  </div>
</div>

{{-- FORM CEPAT TAMBAH KELAS (INLINE) --}}
<div id="quick-add-panel" class="panel" style="display: none; margin-bottom: 20px; padding: 18px 20px; background: var(--soft); border: 2px dashed var(--line);">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
    <strong>Form Cepat: Tambah Kelas Baru</strong>
    <button type="button" class="link-button" onclick="toggleQuickAddRow()" style="font-size: 13px; color: var(--muted); cursor: pointer;">✕ Tutup</button>
  </div>
  <form method="post" action="{{ route('admin.classes.store') }}">
    @csrf
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) auto; gap: 12px; align-items: end;">
      <div>
        <label for="qa_grade" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">No. Akun (Unik) *</label>
        <input class="input" id="qa_grade" name="grade" type="number" min="1" max="1000" placeholder="1, 2, 3..." required value="{{ old('grade', ($classes->max('grade') ?? 0) + 1) }}">
      </div>
      <div>
        <label for="qa_class_name" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Nama Kelas *</label>
        <input class="input" id="qa_class_name" name="class_name" type="text" placeholder="Format: (Level) - (Nama), cth: 10 - ISTC A" required>
      </div>
      <div>
        <label for="qa_username" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Username (Opsional)</label>
        <input class="input" id="qa_username" name="username" type="text" placeholder="Contoh: kelas1" autocomplete="off">
      </div>
      <div>
        <label for="qa_password" style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Password (Opsional)</label>
        <input class="input" id="qa_password" name="password" type="password" placeholder="Min 4 karakter" autocomplete="new-password">
      </div>
      <div>
        <button class="btn" type="submit" style="width: 100%;">+ Simpan</button>
      </div>
    </div>
  </form>
</div>

{{-- SPREADSHEET TABLE --}}
<div class="table-wrap" style="border: 1px solid var(--line); border-radius: var(--radius); overflow-x: auto;">
  <table style="width: 100%; border-collapse: collapse;">
    <thead>
      <tr style="background: var(--soft);">
        <th style="width: 100px; text-align: center;">No. Akun</th>
        <th style="min-width: 160px;">Nama Kelas</th>
        <th style="min-width: 140px;">Username</th>
        <th style="min-width: 130px;">Password</th>
        <th style="min-width: 180px;">Artikel & Produk</th>
        <th style="width: 180px; text-align: center;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($classes as $c)
        @php
          $hasAccount = (bool) ($c->seller?->user);
          $username = $c->seller?->user?->username ?? '';
        @endphp
        <tr style="border-bottom: 1px solid var(--line);">
          <td style="text-align: center; font-weight: 800; font-size: 1.15rem; color: var(--good);">
            {{ $c->grade }}
          </td>
          <td style="font-weight: 600; font-size: 1rem;">
            {{ $c->class_name }}
          </td>
          <td>
            @if($hasAccount && $username)
              <span class="badge open" style="font-family: monospace; font-size: 12px; padding: 4px 8px;">
                @<span>{{ $username }}</span>
              </span>
            @elseif($hasAccount)
              <span class="badge info" style="font-size: 11px;">Akun aktif (tanpa username)</span>
            @else
              <span class="muted" style="font-style: italic;">— Tidak ada akun —</span>
            @endif
          </td>
          <td>
            @if($hasAccount)
              <span class="muted" style="letter-spacing: 2px; font-weight: 700;" title="Password tersimpan dan aktif">••••••••</span>
            @else
              <span class="muted">—</span>
            @endif
          </td>
          <td>
            <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
              <a href="{{ route('admin.articles.edit', $c) }}" class="badge verified" style="text-decoration: none;" title="Edit Artikel Proyek">
                ✓ Ada Artikel
              </a>
              @if($c->products_count > 0)
                <span class="badge info">{{ $c->products_count }} Produk</span>
              @else
                <span class="muted" style="font-size: 12px;">0 Produk</span>
              @endif
            </div>
          </td>
          <td style="text-align: center;">
            <div style="display: inline-flex; gap: 8px; justify-content: center; align-items: center;">
              <button
                type="button"
                class="btn sec sm"
                style="padding: 4px 10px; font-size: 12px;"
                data-class-id="{{ $c->id }}"
                data-grade="{{ $c->grade }}"
                data-class-name="{{ $c->class_name }}"
                data-has-account="{{ $hasAccount ? '1' : '0' }}"
                data-username="{{ $username }}"
                data-update-url="{{ route('admin.classes.update', $c) }}"
                onclick="handleEditClassClick(this)"
              >
                ✏️ Edit
              </button>
              
              <form
                id="delete-class-form-{{ $c->id }}"
                method="post"
                action="{{ route('admin.classes.destroy', $c) }}"
                style="display: inline; margin: 0;"
              >
                @csrf
                @method('DELETE')
                <button
                  type="button"
                  class="btn bad sm"
                  style="padding: 4px 10px; font-size: 12px;"
                  data-class-id="{{ $c->id }}"
                  data-class-name="{{ $c->class_name }}"
                  data-has-account="{{ $hasAccount ? '1' : '0' }}"
                  data-username="{{ $username }}"
                  onclick="handleDeleteClassClick(this)"
                >
                  🗑️ Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center; padding: 40px 16px;">
            <p class="muted" style="margin-bottom: 14px; font-size: 15px;">Belum ada kelas yang terdaftar.</p>
            <form method="post" action="{{ route('admin.classes.initialize') }}">
              @csrf
              <button class="btn" type="submit">Inisialisasi 11 Level Default Sekarang</button>
            </form>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL TAMBAH KELAS --}}
<div id="modal-class-create" class="modal-backdrop" style="display: none;">
  <div class="modal-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 style="margin: 0;">Tambah Kelas Baru</h3>
      <button type="button" class="link-button" onclick="closeCreateClassModal()" style="font-size: 22px; cursor: pointer;">&times;</button>
    </div>
    
    <form method="post" action="{{ route('admin.classes.store') }}">
      @csrf
      <div class="field">
        <label for="create_grade_modal">Nomor Akun (Unik) *</label>
        <input class="input" id="create_grade_modal" name="grade" type="number" min="1" max="1000" value="{{ old('grade', ($classes->max('grade') ?? 0) + 1) }}" required>
        <span class="muted" style="font-size: 12px;">Nomor identitas unik kelas/akun (contoh: 1, 2, 3...)</span>
      </div>

      <div class="field">
        <label for="create_name_modal">Nama Kelas *</label>
        <input class="input" id="create_name_modal" name="class_name" type="text" placeholder="Format: (Level) - (Nama), cth: 10 - ISTC A atau 1 - SD 1" value="{{ old('class_name') }}" required>
      </div>

      <div style="border-top: 1px solid var(--line); margin: 18px 0 14px; padding-top: 14px;">
        <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Akun Seller (Opsional)</div>
        <p class="muted" style="font-size: 12px; margin-top: 0; margin-bottom: 12px;">
          Opsional: jika diisi, akun seller akan langsung dibuat dan dapat digunakan login di <code>/login</code>.
        </p>

        <div class="field">
          <label for="create_user_modal">Username Seller (Opsional)</label>
          <input class="input" id="create_user_modal" name="username" type="text" placeholder="Contoh: kelas1" value="{{ old('username') }}" autocomplete="off">
        </div>

        <div class="field">
          <label for="create_pass_modal">Password Seller</label>
          <input class="input" id="create_pass_modal" name="password" type="password" placeholder="Minimal 4 karakter" autocomplete="new-password">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" class="btn sec" onclick="closeCreateClassModal()">Batal</button>
        <button type="submit" class="btn">Simpan Kelas</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT KELAS --}}
<div id="modal-class-edit" class="modal-backdrop" style="display: none;">
  <div class="modal-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 style="margin: 0;">Edit Data Kelas</h3>
      <button type="button" class="link-button" onclick="closeEditClassModal()" style="font-size: 22px; cursor: pointer;">&times;</button>
    </div>
    
    <form id="edit-class-form-modal" method="post" action="">
      @csrf
      @method('PUT')
      <div class="field">
        <label for="edit_grade_modal">Nomor Akun (Unik) *</label>
        <input class="input" id="edit_grade_modal" name="grade" type="number" min="1" max="1000" required>
        <span class="muted" style="font-size: 12px;">Setiap kelas harus memiliki nomor akun yang berbeda.</span>
      </div>

      <div class="field">
        <label for="edit_name_modal">Nama Kelas *</label>
        <input class="input" id="edit_name_modal" name="class_name" type="text" placeholder="Format: (Level) - (Nama), cth: 10 - ISTC A" required>
      </div>

      <div style="border-top: 1px solid var(--line); margin: 18px 0 14px; padding-top: 14px;">
        <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Akun Seller (Opsional)</div>
        <p class="muted" style="font-size: 12px; margin-top: 0; margin-bottom: 12px;">
          Username & password ini dipakai oleh tim seller kelas untuk masuk di <code>/login</code>.
        </p>

        <div class="field">
          <label for="edit_user_modal">Username Seller</label>
          <input class="input" id="edit_user_modal" name="username" type="text" placeholder="Biarkan kosong jika tanpa akun" autocomplete="off">
        </div>

        <div class="field">
          <label for="edit_pass_modal">Password Baru</label>
          <input class="input" id="edit_pass_modal" name="password" type="password" placeholder="Kosongkan jika tidak ingin mengubah password" autocomplete="new-password">
        </div>

        <div id="edit-remove-account-wrapper" style="display: none; margin-top: 12px; padding: 10px 12px; background: var(--bad-bg); border-radius: 6px;">
          <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--bad); cursor: pointer; margin: 0;">
            <input type="checkbox" id="edit_remove_account_input" name="remove_account" value="1">
            <strong>Hapus akun seller untuk kelas ini</strong> (menonaktifkan akses login)
          </label>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" class="btn sec" onclick="closeEditClassModal()">Batal</button>
        <button type="submit" class="btn">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL DOUBLE CONFIRMATION (KONFIRMASI 2 LANGKAH UNTUK AKUN SENSITIF) --}}
<div id="modal-class-confirm" class="modal-backdrop" style="display: none;">
  <div class="modal-card" style="max-width: 440px;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
      <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--warn-bg); color: var(--warn); display: grid; place-items: center; font-weight: 800; font-size: 18px; flex: 0 0 38px;">
        !
      </div>
      <div>
        <div id="class-confirm-badge" class="badge warn" style="font-size: 10px; text-transform: uppercase;">
          Konfirmasi 1 dari 2
        </div>
        <h3 id="class-confirm-title" style="margin: 3px 0 0; font-size: 1.15rem;">Konfirmasi Tindakan</h3>
      </div>
    </div>

    <p id="class-confirm-message" style="margin: 12px 0 18px; font-size: 14px; line-height: 1.55;"></p>

    <div style="display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" class="btn sec" onclick="closeClassConfirmModal()">Tidak, Batalkan</button>
      <button id="class-confirm-next-btn" type="button" class="btn">Ya, Lanjutkan</button>
    </div>
  </div>
</div>

<style>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(10, 20, 12, 0.65);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.modal-card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 12px;
  width: 100%;
  max-width: 480px;
  padding: 24px;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
  animation: modalAnim .18s ease-out;
}
@keyframes modalAnim {
  from { opacity: 0; transform: translateY(8px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
function toggleQuickAddRow() {
  const panel = document.getElementById('quick-add-panel');
  panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
  if (panel.style.display === 'block') {
    document.getElementById('qa_grade').focus();
  }
}

function openCreateClassModal() {
  document.getElementById('modal-class-create').style.display = 'flex';
}
function closeCreateClassModal() {
  document.getElementById('modal-class-create').style.display = 'none';
}

function openEditClassModal(btn) {
  const form = document.getElementById('edit-class-form-modal');
  form.action = btn.getAttribute('data-update-url');
  document.getElementById('edit_grade_modal').value = btn.getAttribute('data-grade');
  document.getElementById('edit_name_modal').value = btn.getAttribute('data-class-name');
  document.getElementById('edit_user_modal').value = btn.getAttribute('data-username') || '';
  document.getElementById('edit_pass_modal').value = '';
  document.getElementById('edit_remove_account_input').checked = false;

  const hasAccount = btn.getAttribute('data-has-account') === '1';
  document.getElementById('edit-remove-account-wrapper').style.display = hasAccount ? 'block' : 'none';

  document.getElementById('modal-class-edit').style.display = 'flex';
}
function closeEditClassModal() {
  document.getElementById('modal-class-edit').style.display = 'none';
}

function closeClassConfirmModal() {
  document.getElementById('modal-class-confirm').style.display = 'none';
}

// Handle Click Edit with Double Confirmation for Classes with Account
function handleEditClassClick(btn) {
  const hasAccount = btn.getAttribute('data-has-account') === '1';
  const className = btn.getAttribute('data-class-name');
  const username = btn.getAttribute('data-username');

  if (!hasAccount) {
    openEditClassModal(btn);
    return;
  }

  showClassDoubleConfirm({
    type: 'edit',
    step1: {
      badge: 'Konfirmasi 1 dari 2',
      title: 'Peringatan: Akun Seller Aktif',
      message: `Kelas <strong>"${escapeClassHtml(className)}"</strong> memiliki akun login seller aktif dengan username <strong>@${escapeClassHtml(username)}</strong>.<br><br>Apakah Anda yakin ingin mengedit data kelas ini?`,
      btnText: 'Ya, Lanjutkan (1/2)',
      btnClass: 'btn'
    },
    step2: {
      badge: 'Konfirmasi 2 dari 2 (Sensitif)',
      title: 'Konfirmasi Kedua: Akses Login',
      message: `Perubahan data atau kredensial pada kelas <strong>"${escapeClassHtml(className)}"</strong> dapat memengaruhi akses login tim kelas.<br><br>Apakah Anda benar-benar yakin ingin melanjutkan ke formulir edit?`,
      btnText: 'Ya, Buka Form Edit',
      btnClass: 'btn lime'
    },
    onComplete: () => {
      openEditClassModal(btn);
    }
  });
}

// Handle Click Delete with Double Confirmation for Classes with Account
function handleDeleteClassClick(btn) {
  const classId = btn.getAttribute('data-class-id');
  const className = btn.getAttribute('data-class-name');
  const hasAccount = btn.getAttribute('data-has-account') === '1';
  const username = btn.getAttribute('data-username');
  const form = document.getElementById('delete-class-form-' + classId);

  if (!hasAccount) {
    if (confirm(`Apakah Anda yakin ingin menghapus kelas "${className}"?`)) {
      form.submit();
    }
    return;
  }

  showClassDoubleConfirm({
    type: 'delete',
    step1: {
      badge: 'Konfirmasi 1 dari 2',
      title: 'Peringatan: Akun Seller Aktif',
      message: `Kelas <strong>"${escapeClassHtml(className)}"</strong> memiliki akun login seller aktif dengan username <strong>@${escapeClassHtml(username)}</strong>.<br><br>Apakah Anda yakin ingin menghapus kelas ini?`,
      btnText: 'Ya, Lanjutkan (1/2)',
      btnClass: 'btn'
    },
    step2: {
      badge: 'Konfirmasi 2 dari 2 (Final)',
      title: 'Konfirmasi Kedua: Hapus Permanen',
      message: `Tindakan ini permanen. Akun login seller <strong>@${escapeClassHtml(username)}</strong> dan seluruh data terkait kelas <strong>"${escapeClassHtml(className)}"</strong> akan dihapus.<br><br>Apakah Anda benar-benar yakin ingin menghapusnya?`,
      btnText: 'Ya, Hapus Kelas Sekarang',
      btnClass: 'btn bad'
    },
    onComplete: () => {
      form.submit();
    }
  });
}

function showClassDoubleConfirm(config) {
  const modal = document.getElementById('modal-class-confirm');
  const badge = document.getElementById('class-confirm-badge');
  const title = document.getElementById('class-confirm-title');
  const message = document.getElementById('class-confirm-message');
  const nextBtn = document.getElementById('class-confirm-next-btn');

  // Step 1
  badge.textContent = config.step1.badge;
  badge.className = 'badge warn';
  title.textContent = config.step1.title;
  message.innerHTML = config.step1.message;
  nextBtn.textContent = config.step1.btnText;
  nextBtn.className = config.step1.btnClass;

  nextBtn.onclick = function() {
    // Step 2
    badge.textContent = config.step2.badge;
    badge.className = config.type === 'delete' ? 'badge bad' : 'badge open';
    title.textContent = config.step2.title;
    message.innerHTML = config.step2.message;
    nextBtn.textContent = config.step2.btnText;
    nextBtn.className = config.step2.btnClass;

    nextBtn.onclick = function() {
      closeClassConfirmModal();
      config.onComplete();
    };
  };

  modal.style.display = 'flex';
}

function escapeClassHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>
