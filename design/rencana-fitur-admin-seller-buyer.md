# Rencana Fitur Lanjutan FORTEXIV — Admin, Seller, Buyer

**Status:** Draft untuk direview
**Tanggal:** 12 November 2026
**Sumber analisis:** kode saat ini (`routes/web.php`, `AdminController`, `SellerController`, `MarketplaceController`, `CompleteOrderFulfillment`, migrasi, `config/marketplace.php`) dan `FORTEXIV-dokumentasi-handoff-fungsional.md` v1.2.

Dokumen ini berisi **usulan**, belum keputusan. Setiap fitur diberi prioritas dan perkiraan ukuran supaya mudah dipilih mana yang dikerjakan dulu.

- **Prioritas:** P0 = celah serius/sebelum PO dibuka · P1 = sangat membantu operasional · P2 = nilai tambah
- **Ukuran:** S = ±1 hari · M = 2–4 hari · L = ≥1 minggu

---

## 1. Kondisi Saat Ini (Ringkas)

### Yang sudah berjalan

| Peran | Fitur yang ada |
|---|---|
| Publik | Beranda, daftar & detail proyek kelas, katalog (cari, filter kelas/kategori/stok), detail produk, bantuan, ToS |
| Buyer | Registrasi + verifikasi email, keranjang lintas kelas, Beli Sekarang, checkout (nama & WA pengambil, persetujuan ToS), upload bukti transfer, daftar & detail pesanan, tiket/QR pickup per grup kelas |
| Seller | Dashboard ringkas, CRUD produk (draft → publish sendiri), daftar item pesanan, konfirmasi pickup dengan kode |
| Admin | Dashboard angka, verifikasi/tolak bukti bayar, lihat bukti, daftar user + aktif/nonaktif, meja pickup (pilih kelas + kode), laporan harian |

### Celah utama yang ditemukan

| # | Celah | Dampak |
|---|---|---|
| G1 | **Siapa pun bisa daftar sebagai seller** (`role` dipilih bebas di form registrasi). | Akun seller palsu bisa membuat & menerbitkan produk ke katalog publik. |
| G2 | **Seller menerbitkan produk sendiri** tanpa review admin. | Bertentangan dengan handoff §8.4 ("produk tidak tampil sebelum disetujui"). |
| G3 | **Tidak ada pengelolaan proyek/artikel kelas di admin.** Proyek, kategori, dan `products.story` hanya bisa diisi lewat seeder/database. | Admin tidak bisa menulis/merevisi artikel (handoff §8.3). |
| G4 | **Jadwal PO, lokasi, instruksi pickup, rekening bank, banner, dan versi ToS ada di `.env`.** | Mengubah jadwal butuh developer + deploy; rawan salah. |
| G5 | **Pesanan belum dibayar tidak pernah kedaluwarsa.** Stok sudah dikurangi saat checkout. | Stok "tersandera" pesanan yang ditinggalkan; produk tampak habis padahal tidak. |
| G6 | **Bukti bayar ditolak = pesanan langsung dibatalkan.** Buyer tidak bisa upload ulang (mis. salah foto). | Buyer harus checkout ulang; stok bisa keburu habis. |
| G7 | **Tidak ada notifikasi apa pun** (selain email verifikasi/reset). Folder `app/Mail`, `app/Notifications` belum ada. | Buyer tidak tahu pembayaran sudah diverifikasi; admin tidak tahu ada bukti baru. |
| G8 | **Tidak ada audit log** untuk verifikasi, penolakan, handover, toggle user. | Handoff §8.9 mewajibkan pelaku, waktu, alasan, objek. Hanya `verified_by` & `handed_over_by` yang tercatat. |
| G9 | Admin **tidak bisa mencari/memfilter** pesanan, pembayaran, atau user; tidak ada halaman detail pesanan admin. | Saat buyer bertanya "pesanan saya bagaimana?", admin kesulitan menelusuri. |
| G10 | Seller tidak punya **rekap produksi** (total qty per produk dari pesanan yang sudah dibayar). | Kelas harus menghitung manual dari daftar item. |
| G11 | Tidak ada **koreksi handover** (salah tandai) dan **resolusi grup** (refund/batal per kelas). | Handoff §8.5 & §12.3; saat ini tidak bisa ditangani lewat sistem. |

---

## 2. Rencana Fitur — Admin

### A1. Persetujuan akun seller · **P0 · S**
- Registrasi publik hanya untuk buyer. Akun seller dibuat admin **atau** registrasi seller masuk status `pending` sampai disetujui.
- Admin: daftar pengajuan seller → setujui/tolak + pilih proyek kelas yang ditautkan.
- Menutup celah G1.

### A2. Review & publikasi produk · **P0 · M**
- Status produk: `draft` → `in_review` → `published` / `needs_revision` (+ catatan alasan).
- Tombol seller "Publish" berubah menjadi "Ajukan Review".
- Halaman admin **Review Produk**: antrean, pratinjau seperti tampilan publik, tombol Setujui / Minta Revisi (alasan wajib).
- Opsi: perubahan harga/nama pada produk yang sudah publish kembali ke review (keputusan terbuka #10).

### A3. Pengaturan Acara & Jendela PO dari dashboard · **P0 · M**
- Halaman **Pengaturan Acara** menggantikan nilai `.env`: buka/tutup PO, batas bayar, tanggal & lokasi Hari H, instruksi pickup, banner pengumuman, info rekening, versi ToS.
- Disimpan di tabel `settings` (key–value) dengan cache; `PurchaseWindow` membaca dari sini dengan fallback ke config.
- Tampilkan pratinjau status ("PO akan buka dalam 3 hari") dan validasi urutan tanggal (buka < tutup ≤ batas bayar < Hari H).
- Tombol darurat **Tutup PO sekarang**.

### A4. Manajemen Proyek/Artikel Kelas · **P0 · M**
- CRUD proyek: kelas, angkatan, judul, pengantar, tujuan, cerita, proses (langkah-langkah), hasil, gambar sampul, galeri, status draft/publish.
- Editor **cerita produk** (`products.story`) di admin, terpisah dari form seller (sesuai pembagian wewenang di handoff §4).
- Pratinjau sebelum publish.

### A5. Manajemen Kategori · **P1 · S**
- CRUD kategori sederhana (nama, slug otomatis); cegah hapus jika masih dipakai produk.

### A6. Pusat Pesanan Admin · **P1 · M**
- Daftar semua pesanan dengan **pencarian** (kode order, nama/email buyer, no. WA) dan **filter** status, kelas, tanggal.
- **Halaman detail pesanan admin**: item per grup kelas, status pembayaran + bukti, status tiap grup, riwayat tindakan, kontak pengambil.
- Tautan cepat ke WhatsApp buyer (`wa.me/…`) untuk follow-up.

### A7. Antrean verifikasi pembayaran yang lebih cepat · **P1 · S**
- Filter tab: Menunggu / Diverifikasi / Ditolak.
- Pratinjau bukti **inline** (gambar langsung tampil, bukan diunduh) berdampingan dengan total yang harus dibayar.
- Tombol "Verifikasi & lanjut ke berikutnya".
- Alasan penolakan siap pakai (nominal kurang, bukti tidak terbaca, rekening salah) + teks bebas.

### A8. Kedaluwarsa pesanan otomatis · **P0 · S**
- Scheduled command (`orders:expire-unpaid`) tiap 15 menit: pesanan `pending_payment` yang lewat batas bayar (atau X jam setelah checkout — keputusan terbuka #2) → `expired`, stok dikembalikan.
- Admin melihat berapa pesanan yang akan kedaluwarsa dalam 24 jam di dashboard.

### A9. Audit log / riwayat tindakan · **P1 · M**
- Tabel `activity_logs` (pelaku, aksi, objek, alasan, data sebelum/sesudah, waktu).
- Dicatat untuk: verifikasi/tolak bayar, handover & koreksinya, resolusi grup, toggle user, perubahan pengaturan acara, review produk.
- Tampil di detail pesanan dan halaman **Riwayat Tindakan** (filter per pelaku/aksi).

### A10. Resolusi grup & koreksi handover · **P1 · M**
- Per grup kelas: **Batalkan grup / Refund grup** (alasan wajib, stok dikembalikan, nominal refund tercatat) tanpa memengaruhi grup lain.
- **Koreksi handover** (kembalikan ke `pending`) hanya admin, alasan wajib, masuk audit log.
- Status pesanan menjadi selesai jika semua grup `handed_over` atau `resolved` (aturan handoff §13).

### A11. Laporan & ekspor · **P1 · M**
- Laporan per rentang tanggal (bukan hanya harian) dan **per kelas**: pesanan, omzet, qty terjual, refund, sudah/belum diambil.
- Ekspor **CSV/Excel**: pesanan, rekap produksi per kelas, daftar pengambil Hari H.
- Grafik sederhana penjualan per hari selama PO.

### A12. Dashboard Hari H · **P1 · M**
- Progress real-time: grup diserahkan vs. tersisa, per kelas.
- Daftar **belum diambil** (nama, WA) untuk follow-up/no-show.
- **Meja pickup dengan pemindai QR kamera** (bukan ketik kode) dan pencarian berdasarkan nama/kode order sebagai fallback.

### A13. Manajemen user lebih lengkap · **P2 · S**
- Cari & filter user, lihat detail (pesanan buyer / produk seller), kirim ulang verifikasi email, reset password manual.
- Peran tambahan opsional: **petugas pickup** (hanya akses meja pickup) dan **finance** (verifikasi bayar, refund, payout) — sesuai aktor di handoff §4.

### A14. Settlement & payout per kelas · **P2 · L**
- Ringkasan per kelas: omzet bersih (dibayar − refund), status grup, tombol "Tandai siap payout" → "Payout dicatat" (bukti transfer, pelaku).
- Bergantung keputusan terbuka #11 (rumus & penyetuju).

### A15. Konten statis (FAQ, ToS, pengumuman) · **P2 · M**
- Edit FAQ/ToS/bantuan dari admin; ToS berversi — perubahan menaikkan `terms_version` secara otomatis.

---

## 3. Rencana Fitur — Seller (Kelas)

### S1. Alur review produk dari sisi seller · **P0 · S** *(pasangan A2)*
- Status jelas di daftar produk: Draft, Menunggu Review, Perlu Revisi (+ catatan admin), Tayang.
- Notifikasi saat produk disetujui/diminta revisi.

### S2. Rekap Produksi · **P1 · S**
- Tabel per produk: total qty dari pesanan **dibayar**, qty menunggu verifikasi (terpisah, sesuai handoff §7.4), qty sudah diserahkan.
- Ekspor CSV / versi cetak untuk dibawa ke ruang produksi.

### S3. Dashboard kelas yang lebih informatif · **P1 · S**
- Kartu: omzet terverifikasi, pesanan dibayar, grup belum diambil, produk stok menipis/habis.
- Status PO (hitung mundur buka/tutup) dan Hari H.
- "Yang perlu kamu lakukan": produk perlu revisi, stok habis, grup belum diserahkan.

### S4. Daftar pengambilan Hari H & pemindai QR · **P1 · M**
- Daftar grup kelas: nama pengambil, item, status; pencarian nama/kode.
- Scan QR pakai kamera ponsel → tampil isi grup → tombol **Konfirmasi Serah Terima** (pemindaian saja bukan handover, sesuai §8.7).
- Mode cetak daftar sebagai cadangan jika internet putus.

### S5. Pengelolaan stok cepat · **P1 · S**
- Edit stok langsung dari daftar produk (inline), tanpa membuka form penuh.
- Peringatan stok menipis; tombol **Arsipkan** produk (menggantikan saran "ubah stok jadi nol" saat produk punya riwayat pesanan).

### S6. Foto produk lebih dari satu · **P2 · M**
- Galeri hingga 5 foto + pilih foto utama; kompres/resize otomatis untuk performa mobile.

### S7. Tim kelas (multi-akun) · **P2 · M**
- Satu kelas punya beberapa anggota (lead & petugas pickup). Saat ini `sellers.user_id` unik → satu akun per kelas, sehingga password dipakai bersama saat Hari H.

### S8. Laporan insiden kelas · **P2 · S**
- Seller melaporkan kendala (stok kurang, produk rusak) per produk/grup → masuk antrean admin untuk diputuskan (refund/ganti). Seller tidak mengubah pesanan sendiri (§7.5).

---

## 4. Rencana Fitur — Buyer

### B1. Notifikasi email status pesanan · **P0 · M**
Email (via queue) pada momen penting:
| Momen | Isi |
|---|---|
| Checkout berhasil | Ringkasan, total, rekening, **batas bayar** |
| Pengingat bayar | H-1 / beberapa jam sebelum kedaluwarsa |
| Pembayaran diverifikasi | Link tiket/QR pickup, jadwal & lokasi |
| Pembayaran ditolak | Alasan + cara memperbaiki |
| Pesanan kedaluwarsa | Info stok dikembalikan |
| H-1 Hari H | Pengingat lokasi, jam, bawa QR |
| Grup diserahkan / pesanan selesai | Konfirmasi |

Admin juga mendapat email/indikator saat ada bukti bayar baru. (Kanal WhatsApp bisa jadi tahap lanjut — keputusan terbuka #14.)

### B2. Upload ulang bukti bayar · **P0 · S**
- Penolakan karena bukti tidak valid tidak lagi membatalkan pesanan; status kembali ke `pending_payment` dengan alasan terlihat, buyer bisa upload ulang sebelum batas bayar.
- Pembatalan penuh hanya lewat kedaluwarsa (A8) atau keputusan admin eksplisit.

### B3. Hitung mundur & instruksi bayar yang jelas · **P1 · S**
- Di detail pesanan: hitung mundur batas bayar, tombol **salin nomor rekening** & **salin nominal**, langkah transfer.
- Badge status yang mudah dibedakan di "Pesanan Saya" + filter "Perlu tindakan".

### B4. Batalkan pesanan sendiri · **P1 · S**
- Buyer dapat membatalkan pesanan yang **belum dibayar**; stok kembali. Mengurangi pesanan menggantung.

### B5. QR Hari H yang praktis · **P1 · S**
- Mode layar penuh dengan kecerahan tinggi, satu QR per grup kelas + nomor meja/zona kelas.
- Tombol **Simpan sebagai gambar/PDF** (cadangan jika sinyal buruk di lokasi).
- Status per grup terupdate (sudah diambil / belum).

### B6. Kontak & bantuan dari pesanan · **P1 · S**
- Tombol "Hubungi admin via WhatsApp" yang otomatis menyertakan kode pesanan.
- FAQ kontekstual sesuai status pesanan.

### B7. Profil & alamat kontak tersimpan · **P2 · S**
- Edit nama, no. WA, ganti password dari halaman Akun.
- Nama & WA pengambil terisi otomatis saat checkout.

### B8. Wishlist / "Ingatkan saya saat PO buka" · **P2 · S**
- Simpan produk favorit sebelum PO dibuka; email saat PO dibuka.

### B9. Peningkatan katalog · **P2 · S**
- Urutkan (terbaru, harga termurah/termahal), label "Hampir habis", produk terkait di detail produk, tombol bagikan (WhatsApp/salin link) untuk artikel proyek & produk.

### B10. Login tamu / checkout lebih ringan · **P2 · M** *(perlu keputusan)*
- Opsi checkout dengan verifikasi email ringan agar orang tua/kerabat tidak harus membuat akun penuh. Perlu ditimbang dengan keamanan tiket.

---

## 5. Usulan Urutan Pengerjaan

### Fase 1 — Wajib sebelum PO dibuka (P0)
1. A1 Persetujuan akun seller
2. A2 + S1 Review produk
3. A3 Pengaturan Acara & PO dari dashboard
4. A4 Manajemen proyek/artikel + cerita produk
5. A8 Kedaluwarsa pesanan + B2 upload ulang bukti
6. B1 Notifikasi email inti (checkout, diverifikasi, ditolak)

### Fase 2 — Selama PO (P1 operasional)
7. A6 Pusat pesanan + A7 antrean verifikasi cepat
8. A9 Audit log
9. S2 Rekap produksi + S3 dashboard kelas + S5 stok cepat
10. B3 hitung mundur bayar + B4 batalkan sendiri + B6 kontak
11. A5 kategori, A11 laporan & ekspor

### Fase 3 — Menjelang & saat Hari H
12. A12 Dashboard Hari H + S4 pemindai QR
13. B5 QR praktis + email pengingat H-1
14. A10 Resolusi grup & koreksi handover

### Fase 4 — Setelah Hari H / pengembangan lanjut (P2)
15. A14 Settlement & payout, A13 peran finance/petugas, S7 tim kelas
16. A15 konten statis, S6 galeri foto, S8 insiden, B7–B10

---

## 6. Dampak Teknis (Gambaran)

| Area | Perubahan |
|---|---|
| Database | Tabel baru: `settings`, `activity_logs`, `product_images` (opsional), `notifications` (bawaan Laravel). Kolom baru: `products.review_notes`, `products.status` tambah nilai `in_review`/`needs_revision`/`archived`, `sellers.approved_at`, `orders.expires_at`, `order_fulfillments.status` tambah `resolved`/`refunded` + `resolution_notes`. |
| Backend | Controller admin baru (Project, Category, Product review, Order, Settings), `Mailable`/`Notification` per momen, scheduled command kedaluwarsa, `Policy` untuk otorisasi seller/admin, service `ActivityLogger`. |
| Frontend | Halaman admin baru (mobile-first, sesuai prinsip handoff), komponen status badge, pemindai QR kamera (library JS — perlu persetujuan dependensi). |
| Infrastruktur | Queue worker & scheduler harus berjalan di server (cron `schedule:run`); SMTP sudah ada di `PANDUAN_KONEKSI_EMAIL.md`. |
| Tes | Feature test untuk tiap alur baru: review produk, kedaluwarsa + pengembalian stok, upload ulang, resolusi grup, otorisasi peran. |

---

## 7. Pertanyaan untuk Reviewer

1. **Seller:** dibuat oleh admin saja, atau tetap bisa daftar sendiri tapi menunggu persetujuan?
2. **Review produk:** apakah perubahan harga/nama setelah tayang perlu review ulang?
3. **Batas bayar:** satu tanggal global (seperti sekarang) atau per pesanan (mis. 24 jam setelah checkout)?
4. **Penolakan bukti bayar:** setuju buyer boleh upload ulang sampai batas bayar?
5. **Notifikasi:** cukup email dulu, atau WhatsApp juga diperlukan di fase awal?
6. **Peran tambahan:** perlu akun terpisah untuk petugas pickup dan finance, atau admin + seller cukup?
7. **Pemindai QR kamera:** boleh menambah dependensi JS untuk scanner?
8. Fitur mana yang ingin **ditambah, dihapus, atau diubah prioritasnya**?
