# Panduan Kerja dengan AI — Mengisi Dokumen Template
### Untuk siswa yang sedang mengisi file-file di folder `design/agile-template/`

> Dokumen ini untuk tahap **perencanaan** (mengisi PRD, ERD, Route/API Spec, Sitemap, dan kesepakatan Git kalian sendiri). Kalau kalian sudah masuk tahap menulis kode, pakai `panduan-ai-proyek-coding.md` — aturan di sana lebih ketat karena AI di tahap itu menyentuh kode sungguhan, bukan cuma dokumen.

---

## 1. Peran AI di Tahap Ini

AI (ChatGPT, Claude, Gemini, atau sejenisnya) bagus dipakai untuk:

- Menyarankan draf user story dari deskripsi kasar yang kalian berikan
- Mengecek apakah acceptance criteria sudah lengkap (ada kasus yang terlewat?)
- Menyarankan kolom yang mungkin lupa di rancangan tabel database
- Merapikan bahasa/format tabel supaya konsisten
- Jadi "teman diskusi" untuk membandingkan beberapa opsi desain

AI **tidak boleh** dipakai untuk:

- **Memutuskan** fitur mana yang prioritas — itu keputusan Product Owner & tim kalian, berdasarkan kebutuhan proyek kalian sendiri
- **Mengisi otomatis** seluruh dokumen tanpa tim membaca dan menyepakati isinya
- Menggantikan diskusi tim — kalau AI menyarankan sesuatu, itu bahan diskusi, bukan keputusan final

> 💡 Anggap AI sebagai **anggota tim magang yang pintar tapi tidak tahu konteks proyek kalian secara utuh** — bisa dimintai draf cepat, tapi hasilnya selalu perlu dicek oleh yang paham konteksnya.

---

## 2. Empat Aturan Dasar

Berlaku setiap kali memakai AI untuk dokumen apa pun di folder `design/agile-template/`:

1. **Kalau tidak bisa menjelaskan kenapa sebuah baris masuk dokumen, baris itu tidak boleh masuk dokumen.** Berlaku sama seperti untuk kode — kalau timnya sendiri tidak paham, jangan dimasukkan.
2. **Pikirkan dan tulis dulu apa yang mau ditanyakan, baru buka AI.** Prompt yang asal ketik menghasilkan jawaban asal juga.
3. **AI tidak memutuskan fitur atau prioritas — itu tugas tim.** AI boleh menyarankan, tim yang memilih.
4. **Semua hasil AI dibaca dan didiskusikan dulu sebelum ditempel ke dokumen** — jangan copy-paste otomatis dari jawaban AI langsung ke file tanpa ada yang membacanya.

---

## 3. Etika & Keamanan Data — Bagian Terpenting

Ini bagian yang **wajib** dipahami semua anggota tim sebelum mulai memakai AI untuk dokumen apa pun.

### 3.1 Kenapa Ini Penting

Kebanyakan chatbot AI versi gratis **menyimpan riwayat percakapan kalian**, dan pada beberapa layanan, isi percakapan itu bisa dipakai untuk melatih model AI selanjutnya. Begitu sesuatu diketik ke chat AI, anggap itu **tidak bisa ditarik kembali sepenuhnya** — beda dengan menghapus file di komputer sendiri.

### 3.2 Yang Tidak Boleh Pernah Ditulis ke Chat AI

| Jangan pernah ketik/tempel | Kenapa | Ganti dengan |
|---|---|---|
| Nama asli, nomor HP, alamat, email sungguhan (teman, guru, calon pembeli, siapa pun) | Data pribadi yang bocor tidak bisa ditarik kembali | Nama & data fiktif yang jelas rekaan, contoh: "Budi Santoso (Contoh)", `contoh@email.test` |
| Dokumen sekolah asli yang berisi data pribadi murid (NISN, nilai, absensi) | Sama sekali di luar cakupan proyek ini, dan berisiko tinggi | Kalau perlu contoh struktur data, buat versi rekaan sendiri |
| Password, kunci API, kredensial apa pun | Bisa disalahgunakan kalau bocor, bahkan untuk dokumen "cuma perencanaan" | Tidak usah ditulis sama sekali — dokumen perencanaan tidak butuh kredensial sungguhan |
| Foto/scan dokumen resmi (KTP, kartu pelajar, rapor) | Data pribadi sensitif | Tidak relevan sama sekali untuk mengisi PRD/ERD/Sitemap |

### 3.3 Kebiasaan Aman Lainnya

- **Cek pengaturan privasi tools yang dipakai.** Beberapa chatbot punya opsi "jangan pakai percakapan saya untuk melatih model" — aktifkan kalau tersedia. Kalau bingung caranya, tanya guru/pendamping, jangan asal klik.
- **Kalau memakai satu akun AI bersama** (dipakai bergantian oleh beberapa tim/kelas), jangan taruh informasi pribadi kalian sendiri di situ — riwayat chat bisa dilihat pengguna lain yang memakai akun yang sama.
- **Kalau ragu apakah sesuatu aman ditulis ke AI, jangan ditulis dulu — tanya guru/pendamping.**

---

## 4. Contoh Prompt yang Baik per Dokumen

### Untuk PRD / Product Backlog

> "Saya sedang membuat backlog Agile untuk aplikasi [jelaskan singkat: jenis aplikasi, siapa penggunanya]. Bantu saya menulis 3 draf user story untuk fitur [nama fitur], format 'Sebagai [peran], saya ingin [aksi], supaya [manfaat]', sertakan acceptance criteria untuk masing-masing. Saya yang akan memilih dan menyesuaikan prioritasnya sendiri — tidak usah disarankan levelnya (Must/Should/Could)."

### Untuk ERD & Database Schema

> "Saya merancang tabel `orders` untuk aplikasi pemesanan dengan alur: buyer checkout → menunggu pembayaran → admin verifikasi → lunas → pickup. Kolom yang sudah saya rancang: [sebutkan]. Ada kolom penting yang biasanya saya lewatkan untuk kasus seperti ini?"

### Untuk Route/API Spec

> "Saya sedang membuat peta route Laravel untuk fitur checkout. Ini alur bisnisnya: [jelaskan singkat]. Route apa saja yang biasanya dibutuhkan untuk alur seperti ini, dan middleware/role apa yang masuk akal untuk masing-masing?"

### Untuk Sitemap & Wireframe

> "Saya mau membuat wireframe kasar (bukan desain final) untuk halaman [nama halaman] di aplikasi [jenis aplikasi]. Elemen apa saja yang biasanya wajib ada di halaman seperti ini supaya tidak ada yang kelupaan?"

> 💡 Pola yang sama di semua contoh di atas: **beri konteks secukupnya, minta saran/draf, jangan minta AI memutuskan.**

---

## 5. Cara Memverifikasi Hasil AI Sebelum Dimasukkan ke Dokumen

Sebelum menempel jawaban AI ke file template, cek:

- [ ] Semua anggota tim yang akan menandatangani dokumen ini **paham** isi yang disarankan AI (bukan cuma satu orang yang paham)
- [ ] Tidak ada bagian yang bertentangan dengan keputusan bisnis yang sudah disepakati tim sebelumnya
- [ ] ID/penomoran (misal ID user story) tidak bentrok dengan yang sudah ada di dokumen
- [ ] Tidak ada data pribadi/sensitif yang ikut tertempel dari hasil AI
- [ ] Istilah dan gaya bahasa konsisten dengan bagian dokumen lain yang sudah kalian tulis sendiri

---

## 6. Jurnal Prompt (Wajib Diisi)

Sama seperti kebiasaan Jurnal Prompt di modul-modul sebelumnya — dokumen perencanaan pun perlu dicatat kapan dan bagaimana AI dipakai.

| Tanggal | Dokumen yang dikerjakan | Prompt yang ditulis | Hasil AI (ringkas) | Yang tim ubah/tolak | Alasan |
|---|---|---|---|---|---|
| | | | | | |
| | | | | | |

> 📌 Jurnal ini bukan formalitas — ini bukti bahwa keputusan akhir tetap ada di tangan tim, bukan disalin mentah dari AI. Kalau nanti ditanya "kenapa struktur tabel ini begini?", jurnal ini yang menjelaskan proses berpikirnya.

---

## 7. Contoh Kasus: Baik vs Buruk

| ❌ Kurang tepat | ✅ Lebih tepat |
|---|---|
| "Buatkan PRD lengkap untuk aplikasi saya" (lalu seluruh hasilnya ditempel apa adanya) | "Bantu saya draf 3 user story untuk fitur checkout, saya yang pilih mana yang dipakai" |
| Menempel nomor WhatsApp guru pembimbing sebagai "contoh kontak admin" | Memakai "081-XXX-CONTOH" atau data fiktif jelas-jelas rekaan |
| Menempel seluruh isi grup WhatsApp kelas untuk "minta dirangkum jadi user story" | Menuliskan sendiri ringkasan kebutuhan tanpa nama asli, baru ditanyakan ke AI |
| Menyalin skema database dari AI tanpa ada yang mengecek apakah cocok dengan alur bisnis kalian | Membandingkan saran AI dengan alur bisnis yang sudah disepakati, lalu menyesuaikan |

---

## 8. Checklist Sebelum Dokumen Dianggap Selesai

- [ ] Jurnal Prompt terisi untuk setiap sesi memakai AI
- [ ] Tidak ada data pribadi sungguhan (nama, kontak, dokumen resmi) yang tertulis di draf manapun
- [ ] Semua anggota tim sudah membaca dan bisa menjelaskan isi dokumen, bukan cuma yang menulis prompt
- [ ] Keputusan prioritas/fitur diambil oleh tim, bukan disalin dari saran AI begitu saja
