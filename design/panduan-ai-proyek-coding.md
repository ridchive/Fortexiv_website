# Panduan Kerja dengan AI — Saat Mengerjakan Proyek (Coding)
### Mencakup dua cara kerja: Copy-Paste (manual) dan Agent/CLI (AI terhubung langsung ke proyek)

> 📌 **Catatan istilah:** nama kelas/program tempat proyek ini dikerjakan **bukan** nama aplikasi yang sedang kalian bangun. Di seluruh dokumen ini, "proyek kalian" merujuk ke aplikasi yang sedang kalian kembangkan — pakai nama proyek/aplikasi kalian sendiri, bukan nama kelasnya.
>
> Dokumen ini untuk tahap **coding**, setelah dokumen perencanaan (PRD, ERD, Route/API Spec, Sitemap) selesai dikunci. Kalau kalian masih di tahap mengisi dokumen perencanaan, pakai `panduan-ai-mengisi-template.md`.

---

## 1. Dua Cara Kerja dengan AI

Alat yang mungkin dipakai (Copilot, Claude, Gemini, atau lainnya) bisa dipakai dengan salah satu dari dua cara ini. Kalian boleh memilih salah satu, atau memakai keduanya secara bergantian sesuai kebutuhan — yang penting paham kapan risikonya berbeda.

| | **A. Copy-Paste (manual)** | **B. Agent/CLI (AI terhubung langsung ke proyek)** |
|---|---|---|
| **Cara kerja** | Buka chat AI di tab/aplikasi terpisah. Salin kode/error, tempel ke chat, minta bantuan, lalu **kalian sendiri** yang menempelkan jawabannya ke editor | AI (misalnya Copilot Agent mode, Claude Code, atau sejenisnya) **membaca dan mengubah file di proyek kalian secara langsung**, bahkan bisa menjalankan perintah terminal/git sendiri |
| **Kecepatan** | Lebih lambat — tiap salin-tempel dilakukan manual | Lebih cepat — bisa mengubah banyak file sekaligus dalam satu perintah |
| **Kontrol** | Tinggi — tidak ada yang masuk ke proyek tanpa melewati tangan kalian | Lebih rendah kalau tidak diawasi — AI bisa mengubah file yang tidak kalian duga |
| **Risiko utama** | Lupa membaca/memahami sebelum menempel | AI mengubah/menghapus sesuatu yang tidak diminta, atau menjalankan perintah berbahaya tanpa disadari |
| **Cocok untuk** | Belajar konsep, potongan kode kecil, debugging spesifik | Pekerjaan berulang/besar yang sudah jelas polanya, setelah tim terbiasa dengan alur review |

> ⚠️ **Aturan yang sama tetap berlaku untuk keduanya** (Bagian 2) — cara Agent/CLI **tidak menghapus** kewajiban membaca dan memahami kode, malah butuh kedisiplinan ekstra karena perubahannya bisa lebih besar dan lebih cepat dari yang bisa dibaca manusia dengan santai.

---

## 2. Aturan yang Berlaku untuk KEDUA Cara

1. **Kalau tidak bisa menjelaskan baris ini, baris ini tidak boleh masuk produk.** Tidak peduli baris itu datang dari hasil copy-paste chat atau dari agent yang menulis otomatis — kalau timnya sendiri tidak paham, jangan digabungkan ke branch.
2. **Pikirkan dulu apa yang mau ditanyakan/diminta, baru buka AI.** Berlaku sama untuk menulis prompt chat maupun instruksi ke agent.
3. **Baca pesan error sendiri minimal 5 menit sebelum bertanya ke AI.** Banyak error sebenarnya sudah menjelaskan masalahnya sendiri.
4. **AI tidak memutuskan fitur atau arsitektur — itu tugas tim**, sesuai yang sudah disepakati di dokumen PRD/ERD/Route Spec.
5. **Kode yang dihasilkan AI (cara mana pun) tetap wajib lewat Pull Request dan code review** seperti kode yang ditulis manual — lihat `panduan-kolaborasi-git.md`. Tidak ada jalan pintas hanya karena "AI yang menulis".
6. **Jurnal Prompt tetap wajib diisi** (Bagian 6).

---

## 3. Etika & Keamanan Data — Khusus Coding

Di tahap ini AI menyentuh **kode dan konfigurasi sungguhan**, jadi risikonya lebih tinggi dari sekadar dokumen perencanaan.

### 3.1 Yang Tidak Boleh Pernah Masuk ke Prompt atau Diakses AI

| Jangan pernah | Kenapa | Lakukan sebagai gantinya |
|---|---|---|
| Isi file `.env` (password database, kunci aplikasi) | Kalau bocor ke layanan AI pihak ketiga, sama saja membocorkan akses ke sistem kalian | Kalau perlu bertanya soal konfigurasi, pakai **nilai contoh/dummy** (`DB_PASSWORD=xxxxx`), bukan nilai asli |
| Data pengguna sungguhan (kalau aplikasi sudah dipakai orang sungguhan: nama, kontak, bukti transfer) | Data pribadi yang bocor ke AI pihak ketiga tidak bisa ditarik kembali | Sanitasi dulu — ganti dengan data fiktif sebelum ditempel ke chat, atau jelaskan masalahnya tanpa menempel data mentah |
| Struktur folder/kredensial server produksi (kalau sudah online) | Bisa dipakai pihak tidak bertanggung jawab kalau bocor | Tanyakan konsepnya secara umum, bukan detail server sungguhan |
| Source code milik pihak lain yang ada batasan lisensi | Bisa melanggar lisensi kalau ditempel ke layanan pihak ketiga | Tanyakan konsepnya, bukan kode orang lain secara utuh |

### 3.2 Tambahan Khusus untuk Mode Agent/CLI

Karena AI mode ini bisa membaca file dan menjalankan perintah **tanpa kalian ketik ulang**, risikonya lebih besar kalau tidak diawasi:

- **Jangan biarkan agent bekerja di branch `main` langsung** — selalu di branch kerja terpisah (lihat `panduan-kolaborasi-git.md` Bagian 3), supaya kalau agent salah besar, gampang dibuang tanpa merusak versi yang stabil.
- **Selalu tinjau perubahan (`git diff`) sebelum commit/push** — perlakukan hasil kerja agent sama seperti kode dari anggota tim yang harus di-review, bukan otomatis dipercaya.
- **Jangan beri izin otomatis (auto-approve) untuk perintah yang berisiko**: menghapus file/folder, `git push --force`, mengubah/menghapus tabel database, mengirim data keluar. Perintah seperti ini harus **kalian setujui manual satu per satu**, bukan "izinkan semua".
- **Batasi akses agent hanya ke folder proyek ini** — jangan beri akses ke folder lain di komputer (dokumen pribadi, folder proyek tim lain) kalau alatnya menawarkan pilihan itu.
- **Jangan sambungkan agent ke akun/kredensial cloud sungguhan** (akun deploy, akun email sekolah, dompet digital) kecuali benar-benar diperlukan dan sudah didiskusikan dengan guru/pendamping.
- **Kalau agent tiba-tiba menyarankan/melakukan sesuatu yang tidak diminta** (mengubah file yang tidak disebut, mencoba mengakses internet di luar dokumentasi, mencoba menjalankan perintah aneh) — **hentikan (stop/cancel), jangan disetujui**, diskusikan dulu dengan tim/guru.

### 3.3 Kalau Memakai Akun Bersama (satu akun AI dipakai beberapa tim/kelas)

- Jangan taruh data pribadi kalian sendiri di sesi yang sama (riwayat chat bisa terlihat pengguna lain akun tersebut).
- Tiap entri Jurnal Prompt **wajib mencantumkan nama tim/skuad**, bukan cuma nama siswa — supaya jelas prompt siapa yang menghasilkan kode apa saat akun dipakai bersama.

---

## 4. Cara Kerja Copy-Paste — Langkah Praktis

1. **Salin secukupnya**, bukan seluruh file kalau tidak perlu — makin sedikit konteks yang ditempel, makin kecil risiko ada yang tidak seharusnya ikut tertempel.
2. **Beri konteks singkat**: framework yang dipakai, apa yang sedang dicoba, apa error/hasil yang didapat.
3. Setelah dapat jawaban, **tempel dan coba dulu satu bagian**, jangan langsung menimpa banyak file sekaligus dari satu jawaban panjang.
4. **Baca dan pahami** sebelum lanjut ke langkah berikutnya (Aturan #1, Bagian 2).

---

## 5. Cara Kerja Agent/CLI — Langkah Praktis

1. **Kerja di branch terpisah**, bukan `main` (lihat `panduan-kolaborasi-git.md`).
2. **Beri instruksi spesifik dan terbatas ruang lingkupnya** — misalnya "ubah hanya file `ProductController.php` untuk menambah validasi stok, jangan ubah file lain" — bukan instruksi yang sangat umum yang membuka peluang perubahan luas tak terduga.
3. **Baca ringkasan rencana AI sebelum mengizinkan eksekusi**, kalau alatnya menampilkan rencana dulu sebelum bertindak.
4. **Tinjau tiap file yang berubah** (`git diff` atau lewat tampilan diff di editor) sebelum commit — perlakukan seperti review PR.
5. **Coba jalankan aplikasinya**, jangan hanya percaya karena "kelihatannya AI sudah menjalankan test sendiri".
6. **Commit dengan pesan yang jelas** (lihat konvensi commit di `panduan-kolaborasi-git.md` Bagian 4) — sebutkan juga kalau sebagian besar perubahan dibantu agent, supaya reviewer tahu perlu membaca lebih teliti.

---

## 6. Jurnal Prompt (Wajib Diisi)

| Tanggal | Cara kerja (Copy-Paste/Agent) | Prompt/Instruksi yang ditulis | Hasil AI (ringkas) | Yang tim ubah/tolak | Alasan |
|---|---|---|---|---|---|
| | | | | | |
| | | | | | |

> 📌 Kalau akun AI dipakai bersama beberapa tim, tambahkan kolom **Nama Tim/Skuad** di jurnal kalian sendiri.

---

## 7. Checklist Sebelum Push/Merge

Tambahan khusus AI, melengkapi Definition of Done di panduan Agile dan checklist reviewer di `panduan-kolaborasi-git.md`:

- [ ] Tidak ada isi `.env`, password, atau kunci API yang pernah ditempel ke chat/agent AI
- [ ] Tidak ada data pengguna sungguhan yang ditempel mentah ke AI (sudah disamarkan/didummy-kan kalau perlu)
- [ ] Kalau memakai Agent/CLI: semua perubahan sudah ditinjau manual (`git diff`) sebelum commit
- [ ] Kalau memakai Agent/CLI: tidak ada perintah berisiko (hapus file, force push, ubah struktur DB) yang dijalankan tanpa persetujuan manual
- [ ] Jurnal Prompt terisi untuk sesi ini
- [ ] Semua anggota yang mengerjakan bisa menjelaskan kode yang dihasilkan, bukan cuma "AI yang bikin"

---

## 8. Ringkasan: Kapan Pakai Cara yang Mana

| Situasi | Cara yang lebih cocok |
|---|---|
| Baru belajar konsep baru, mau paham dulu | Copy-Paste — lebih lambat tapi memaksa membaca tiap bagian |
| Debugging satu error spesifik | Copy-Paste — cukup tempel error & potongan kode terkait |
| Menulis banyak boilerplate berulang (misal beberapa CRUD serupa) yang polanya sudah dipahami tim | Agent/CLI — lebih cepat, asal tetap direview |
| Perubahan yang menyentuh banyak file sekaligus dan berisiko tinggi (migrasi database besar, refactor struktur) | Copy-Paste dulu untuk memahami rencana, baru pertimbangkan Agent/CLI dengan pengawasan ketat |
| Tim belum terbiasa membaca diff dengan teliti | Copy-Paste dulu sampai kebiasaan review terbentuk, baru coba Agent/CLI |
