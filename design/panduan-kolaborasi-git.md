# Panduan Kolaborasi Tim dengan Git
### Dipakai bersama, berlaku untuk semua proyek — isi bagian "🔧 Kesepakatan Tim" dengan keputusan tim kalian sendiri

> Dokumen ini generik dan tidak dipisah jadi versi "lengkap" vs "template" seperti PRD/ERD/Sitemap/Route Spec — cara kerja Git & cara mengelola konflik sama saja apa pun proyeknya. Yang perlu disesuaikan per tim hanya beberapa keputusan kecil, ditandai kotak **🔧 Kesepakatan Tim** di tiap bagian.
>
> Sepakati dokumen ini di **Sprint 0**, sebelum coding fitur dimulai — lihat `design/agile-lengkap/00-panduan-metode-agile.md` Bagian 9 (atau `design/agile-template/00-panduan-metode-agile-template.md` Bagian 9 kalau kalian memakai versi template).

---

## 1. Dasar yang Dipakai di Dokumen Ini

Kalau tim kalian belum pernah pakai Git sama sekali, kuasai dulu lima perintah ini sebelum lanjut ke bagian alur kerja tim:

| Perintah | Kegunaan |
|---|---|
| `git clone <url>` | Mengambil salinan repository dari GitHub ke komputer sendiri (dilakukan sekali di awal) |
| `git add <file>` (atau `git add .`) | Menandai perubahan yang mau disimpan ke riwayat |
| `git commit -m "pesan"` | Menyimpan perubahan yang ditandai sebagai satu titik riwayat, dengan penjelasan |
| `git push` | Mengirim commit dari komputer sendiri ke GitHub |
| `git pull` | Mengambil commit terbaru dari GitHub ke komputer sendiri |

Repository (`.git` di dalam folder proyek) menyimpan seluruh riwayat perubahan — kapan pun bisa kembali ke versi sebelumnya kalau ada yang salah. GitHub adalah tempat repository itu disimpan **bersama**, supaya semua anggota tim bekerja dari sumber yang sama.

> ⚠️ **File `.env` tidak pernah dikirim ke GitHub.** Isinya rahasia (password database, kunci aplikasi) — pastikan sudah masuk `.gitignore` sejak commit pertama.

> 🔧 **Kesepakatan Tim:** apakah semua anggota sudah punya akun GitHub dan sudah bisa `clone`/`push`/`pull` di komputer masing-masing?
>
> `_________________________________________________________________`

---

## 2. Kenapa Butuh Alur Kerja yang Disepakati Bersama

Tahu perintah `git commit` dan `git push` saja tidak cukup kalau beberapa orang menyentuh proyek yang sama tiap hari. Tanpa kesepakatan, yang terjadi biasanya:

- Semua orang commit langsung ke `main` → `main` gampang rusak, susah tahu versi mana yang stabil
- Dua orang menyunting file yang sama tanpa tahu → konflik yang membingungkan karena tidak ada konteks
- Pesan commit isinya "update" semua → susah lacak kenapa sebuah baris berubah

Alur kerja di bawah ini menyelesaikan ketiga masalah itu sekaligus, dan sama persis dengan pola yang dipakai tim developer sungguhan.

---

## 3. Strategi Branching

| Branch | Fungsi | Aturan |
|---|---|---|
| `main` | Selalu berisi versi yang **jalan** — dipakai untuk demo Sprint Review | Tidak ada yang commit langsung ke sini. Hanya menerima perubahan lewat Pull Request yang sudah di-review |
| `feature/US-<id>-<deskripsi-singkat>` | Satu branch per user story dari backlog | Dibuat dari `main` terbaru, dihapus setelah PR digabungkan |

**Contoh nama branch** (mengacu ID backlog di dokumen PRD, misal `01-product-backlog-prd.md`):

```
feature/US-08-tambah-produk
feature/US-14-checkout
feature/US-17-digital-ticket
```

> 💡 **Kenapa nama branch menyebut ID backlog, bukan cuma nama fitur?** Supaya siapa pun bisa langsung menelusuri branch/PR mana yang mengerjakan story mana — penting begitu jumlah branch sudah puluhan setelah beberapa sprint.

Kalau story-nya besar (poin 8, misalnya "verifikasi pembayaran"), boleh dipecah jadi beberapa branch kecil (`feature/US-16-form-verifikasi`, `feature/US-16-update-status-order`) asal digabungkan lagi sebelum sprint berakhir.

> 🔧 **Kesepakatan Tim:**
>
> Pola nama branch tim kami (kalau berbeda dari contoh di atas): `_________________________________`
>
> Siapa yang berhak menggabungkan PR ke `main`? `_________________________________`

---

## 4. Konvensi Pesan Commit

Format: `<tipe>: <penjelasan singkat kenapa, bukan sekadar apa>`

| Tipe | Dipakai untuk |
|---|---|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `refactor` | Merapikan kode tanpa mengubah perilaku |
| `docs` | Perubahan dokumen |
| `chore` | Hal teknis lain (setup, dependency, konfigurasi) |

**Contoh baik:**
```
feat: kurangi stok produk saat checkout, bukan saat verifikasi

Mencegah dua buyer sama-sama dapat stok terakhir yang sama
saat keduanya checkout hampir bersamaan.
```

**Contoh yang dihindari:** `update`, `fix bug`, `perbaikan`, `asdf`.

---

## 5. Alur Kerja Harian

```
git switch main
git pull                                    ← selalu mulai dari versi terbaru
git switch -c feature/US-14-checkout

... kerjakan, commit kecil-kecil ...

git add .
git commit -m "feat: tambah validasi stok sebelum checkout"
git push -u origin feature/US-14-checkout

... buka Pull Request di GitHub, tunjuk 1 reviewer ...
... setelah disetujui dan digabungkan ...

git switch main
git pull                                    ← ambil hasil gabungan terbaru
git branch -d feature/US-14-checkout        ← hapus branch lokal yang sudah selesai
```

**Daftar Periksa Reviewer** — dipakai setiap kali me-review PR anggota tim lain:

| Yang diperiksa | Sudah? |
|---|---|
| Kode benar-benar mengerjakan yang dijelaskan di deskripsi PR | ☐ |
| Tidak ada file rahasia ikut terkirim (periksa daftar file yang berubah — `.env` tidak boleh ada) | ☐ |
| Halaman/endpoint baru sudah diperiksa otorisasinya (role yang benar bisa akses, yang lain ditolak) | ☐ |
| Tidak ada sisa kode percobaan (`dd()`, `dump()`, komentar coba-coba) | ☐ |
| Sudah dicoba dijalankan langsung, benar-benar berfungsi | ☐ |
| Nama variabel/fungsi mudah dipahami orang lain | ☐ |

> 💡 Sampaikan komentar review sebagai pengamatan/pertanyaan, bukan penilaian — "Bagian ini belum diperiksa wewenangnya, kalau dibuka buyer apakah tetap jalan?" jauh lebih menolong daripada "salah".

> 🔧 **Kesepakatan Tim** — tambahan item checklist khusus proyek kalian (kalau ada):
>
> - [ ] _______
> - [ ] _______

---

## 6. Membagi Kerja Supaya Konflik Minim

Konflik paling sering terjadi bukan karena Git-nya bermasalah, tapi karena **pembagian kerja tidak jelas**. Aturan main:

- **Bagi tugas per Epic/modul** (lihat dokumen PRD) — orang yang mengerjakan modul Produk sebisa mungkin tidak menyentuh file yang sama dengan orang yang mengerjakan modul Order.
- **File "rawan tabrakan"** yang disentuh banyak orang — beri perhatian ekstra:
  - `routes/web.php` — kalau dua orang menambah route di baris yang sama, konflik hampir pasti terjadi. Solusi: kelompokkan route per modul dengan komentar (`// Route Produk`, `// Route Order`), dan tambahkan route baru **di bagian kelompoknya sendiri**, bukan di akhir file secara sembarangan.
  - File migration — biasanya aman karena tiap migration jadi file terpisah dengan timestamp sendiri, **tapi** jangan mengubah migration yang sudah di-push orang lain; buat migration baru untuk perubahan tambahan.
  - `composer.json` / `package.json` — kalau menambah dependency, informasikan ke tim sebelum push supaya semua orang tahu perlu `composer install` ulang.
- **Pull `main` sesering mungkin** ke branch kerja kalian (minimal tiap pagi sebelum mulai, dan sebelum membuka PR) — konflik kecil yang ditemukan lebih awal jauh lebih mudah diselesaikan daripada konflik besar yang menumpuk seminggu.
- **Commit kecil dan sering**, jangan menyimpan satu commit raksasa di akhir sprint — commit besar lebih susah ditelusuri kalau terjadi konflik atau perlu di-*revert*.

> 🔧 **Kesepakatan Tim — pembagian tugas:**

| Anggota | Modul/Epic tanggung jawab | File utama yang disentuh |
|---|---|---|
| | | |
| | | |
| | | |

---

## 7. Mengelola Merge Conflict

### 7.1 Kenapa Konflik Terjadi

Git bisa menggabungkan perubahan dari dua branch secara otomatis **kecuali** kedua branch sama-sama mengubah baris yang persis sama (atau satu pihak menghapus file yang diubah pihak lain). Saat itu terjadi, Git berhenti dan meminta manusia yang memutuskan — **ini bukan error**, ini Git yang sengaja tidak mau menebak-nebak keputusan yang bukan wewenangnya.

### 7.2 Cara Mengenali Konflik

Muncul saat `git pull` atau `git merge`:

```
Auto-merging routes/web.php
CONFLICT (content): Merge conflict in routes/web.php
Automatic merge failed; fix conflicts and then commit the result.
```

Cek file mana saja yang konflik:

```bash
git status
```

```
Unmerged paths:
  (use "git add <file>..." to mark resolution)
        both modified:   routes/web.php
```

### 7.3 Membaca Penanda Konflik

Buka file yang konflik, akan terlihat seperti ini:

```php
Route::get('/products', [ProductController::class, 'index']);

<<<<<<< HEAD
Route::post('/checkout', [OrderController::class, 'store'])->middleware('auth');
=======
Route::get('/cart', [CartController::class, 'index'])->middleware('auth');
>>>>>>> feature/US-12-keranjang
```

| Bagian | Artinya |
|---|---|
| Antara `<<<<<<< HEAD` dan `=======` | Versi di branch yang sedang kalian tempati (biasanya `main`, atau branch kalian) |
| Antara `=======` dan `>>>>>>> nama-branch` | Versi dari branch yang sedang digabungkan masuk |

### 7.4 Langkah Menyelesaikan

1. **Baca kedua versi**, pahami maksud masing-masing perubahan (kalau tidak yakin, tanya orang yang menulis versi satunya — jangan menebak).
2. **Putuskan hasil akhirnya** — bisa pilih salah satu, gabungkan keduanya, atau tulis versi baru. Untuk contoh di atas, kedua route sama-sama dibutuhkan, jadi hasil akhirnya:
   ```php
   Route::get('/products', [ProductController::class, 'index']);

   Route::post('/checkout', [OrderController::class, 'store'])->middleware('auth');
   Route::get('/cart', [CartController::class, 'index'])->middleware('auth');
   ```
3. **Hapus semua baris penanda** (`<<<<<<<`, `=======`, `>>>>>>>`) — kalau ada satu saja yang lupa terhapus, aplikasi akan error.
4. **Coba jalankan aplikasi**, pastikan bagian yang barusan digabungkan benar-benar berfungsi — jangan langsung commit tanpa dicoba.
5. Tandai selesai dan lanjutkan:
   ```bash
   git add routes/web.php
   git commit
   git push
   ```

### 7.5 Kalau Bingung dan Ingin Membatalkan

```bash
git merge --abort
```

Mengembalikan branch ke kondisi sebelum percobaan merge — aman dipakai kalau kalian merasa salah ambil keputusan di tengah jalan dan ingin mulai ulang dengan kepala dingin (atau setelah berdiskusi dulu dengan yang menulis versi satunya).

### 7.6 Pakai Bantuan Visual (VS Code)

VS Code mendeteksi konflik otomatis dan menampilkan tombol di atas tiap blok konflik:

```
[ Accept Current Change ]  [ Accept Incoming Change ]  [ Accept Both Changes ]  [ Compare Changes ]
```

- **Accept Current Change** = pakai versi `HEAD` saja
- **Accept Incoming Change** = pakai versi branch yang digabungkan saja
- **Accept Both Changes** = simpan keduanya berurutan (perlu dirapikan manual kalau urutannya penting)

Untuk kasus yang perlu digabung dengan pengertian (bukan sekadar salah satu/keduanya, seperti contoh route di atas), tetap edit manual — tombol-tombol ini titik awal yang cepat, bukan pengganti membaca kode.

### 7.7 Konflik yang Sering Muncul di Proyek Web Berbasis Laravel

| Situasi | Kenapa terjadi | Cara terbaik menghindari |
|---|---|---|
| Dua orang menambah route di `routes/web.php` pada baris berdekatan | File yang sama, area yang sama | Kelompokkan route per modul dengan komentar; pull sesering mungkin |
| Dua orang mengubah controller yang sama untuk fitur berbeda | Satu file dikerjakan dua modul sekaligus | Sebisa mungkin satu controller = satu penanggung jawab per sprint; kalau harus dua orang, pecah method dan koordinasikan dulu |
| File migration yang sama diubah dua orang | Salah satu mengubah migration yang sudah di-push, bukan membuat migration baru | Migration yang sudah digabung ke `main` **jangan diubah lagi** — kalau perlu perubahan struktur, buat migration baru |
| `.env.example` atau `composer.json` konflik | Dua orang menambah dependency/konfigurasi berbeda di waktu yang sama | Informasikan ke tim sebelum menambah dependency baru |

> 🔧 **Kesepakatan Tim** — catat kasus konflik yang pernah tim kalian alami & bagaimana menyelesaikannya (isi seiring berjalan):
>
> Kasus: `_________________________________________________________________`
>
> Cara menyelesaikan: `_________________________________________________________________`

---

## 8. Aturan yang Melengkapi Definition of Done

Tambahan khusus Git untuk Definition of Done (lihat dokumen panduan Agile Bagian 7):

- [ ] Branch dibuat dari `main` yang sudah di-`pull` terbaru (bukan dari `main` yang ketinggalan beberapa hari)
- [ ] Tidak ada konflik tersisa (tidak ada baris `<<<<<<<`/`=======`/`>>>>>>>` yang lolos ter-commit)
- [ ] Branch dihapus setelah PR digabungkan (menjaga daftar branch tetap rapi)
- [ ] `.env` dan file rahasia lain tidak pernah ikut ter-commit

---

## 9. Ringkasan Perintah

| Perintah | Kegunaan |
|---|---|
| `git switch main && git pull` | Mulai kerja dari versi terbaru |
| `git switch -c feature/US-xx-nama` | Buat branch baru untuk satu story |
| `git add . && git commit -m "..."` | Simpan perubahan dengan pesan bermakna |
| `git push -u origin nama-branch` | Kirim branch ke GitHub pertama kali |
| `git status` | Lihat file mana yang berkonflik |
| `git merge --abort` | Batalkan merge yang sedang bingung diselesaikan |
| `git log --oneline --graph --all` | Lihat riwayat & percabangan secara visual |
