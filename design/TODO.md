# TODO — Temuan E2E & UAT

Sumber: eksekusi test pada 12 Nov 2026 (Chromium, mode terlihat).

| Suite | Hasil |
|---|---|
| `e2e/` | **73 / 73 lulus** (setelah perbaikan selektor test, lihat bagian D) |
| `uat/` | **48 lulus · 15 dilewati** (14 gap fitur `fixme` + UAT-09 manual) · 0 gagal |

Rincian skenario dan rencana fitur: `uat/skenario-uat.md`, `design/rencana-fitur-admin-seller-buyer.md`.

---

## A. Bug / UX di aplikasi (perlu diperbaiki)

- [ ] **A1. Pesan error ToS tidak dicentang berbahasa Inggris & generik**
  - Checkout tanpa mencentang ToS menampilkan *"The accepted terms field is required."*. Pesan kustom *"Setujui Terms of Service sebelum mengonfirmasi pesanan."* tidak pernah muncul karena checkbox yang tidak dicentang tidak dikirim, jadi rule `required` gagal lebih dulu, dan pesan kustom hanya didefinisikan untuk `accepted`.
  - Lokasi: `app/Http/Controllers/MarketplaceController.php:234-236`.
  - Perbaikan: tambahkan pesan `accepted_terms.required` dengan teks yang sama.
  - Setelah diperbaiki, kembalikan assertion ke teks Indonesia di `e2e/tests/04-purchase-flow.spec.ts:129` dan `uat/tests/uat-04-alur-pesanan.spec.ts:104`. Saat ini assertion menerima kedua pesan.

- [ ] **A2. Kode pickup yang dipakai ulang memberi pesan menyesatkan**
  - Setelah semua grup kelas pada satu pesanan diserahkan (status `picked_up`), memasukkan ulang kode pickup menghasilkan *"Pesanan belum dibayar atau tidak siap untuk diambil."*. Seharusnya *"Grup kelas ini sudah diserahkan."*. Penyebabnya: cek status pesanan dijalankan sebelum cek status fulfillment.
  - Lokasi: `app/Actions/CompleteOrderFulfillment.php:31-37`.
  - Perbaikan: pindahkan cek `$fulfillment->status !== 'pending'` ke atas cek status pesanan, lalu tambahkan feature test PHPUnit-nya.
  - Setelah diperbaiki, perketat assertion di `e2e/tests/04-purchase-flow.spec.ts:266` dan `uat/tests/uat-04-alur-pesanan.spec.ts:276`.

- [ ] **A3. Bahasa antarmuka campur**
  - Halaman buyer berbahasa Indonesia, sedangkan halaman auth (Sign in, Create account), seller, dan admin (Verify, Reject, Disable, Download receipt) berbahasa Inggris. Pesan validasi bawaan Laravel juga berbahasa Inggris.
  - Perbaikan: tentukan satu bahasa. Jika Indonesia, set `APP_LOCALE=id`, tambahkan `lang/id/validation.php`, lalu terjemahkan label seller/admin.

## B. Keputusan bisnis yang dibutuhkan (konflik antar-dokumen)

- [ ] **B1. Produk stok habis (UAT-06).** PRD meminta disembunyikan dari katalog. Handoff meminta ditampilkan sebagai "Stok habis". Aplikasi saat ini mengikuti handoff.
- [ ] **B2. Bukti bayar ditolak (UAT-27).** Saat ini pesanan langsung *Dibatalkan* dan stok dikembalikan. Alternatifnya: buyer boleh mengunggah ulang sampai batas bayar.
- [ ] **B3. Registrasi seller tanpa persetujuan admin.** Siapa pun bisa mendaftar sebagai seller dan langsung menerbitkan produk (e2e 02-auth dan 03-seller memastikan perilaku ini). Perlu diputuskan apakah perlu approval admin. Lihat G1–G2 di rencana fitur.

## C. Fitur requirement yang belum dibangun (UAT `fixme`)

- [ ] UAT-24 Pesanan belum dibayar otomatis *Kedaluwarsa* setelah batas bayar, dan stok kembali. Butuh **scheduler / cron**; saat ini `routes/console.php` belum punya jadwal apa pun.
- [ ] UAT-25 Buyer mengajukan masalah dari detail pesanan dan melihat status resolusi.
- [ ] UAT-35 Produk seller berstatus *Menunggu review*; admin menyetujui atau meminta revisi.
- [ ] UAT-36 Rekap produksi per produk untuk seller (hanya dari pesanan dibayar).
- [ ] UAT-37 Seller menyatakan kesiapan Hari H dan melaporkan insiden.
- [ ] UAT-38 Payout per kelas (Kelas A diproses, Kelas B ditahan).
- [ ] UAT-44 Pencarian fallback pesanan (nama buyer / kode pesanan) di meja pickup, dengan pencatatan penggunaan.
- [ ] UAT-46 Koreksi handover dan resolusi/refund per grup kelas.
- [ ] UAT-51 CMS artikel proyek dan cerita produk oleh admin.
- [ ] UAT-52 Pengaturan jendela PO, batas bayar, Hari H, dan lokasi dari panel admin (saat ini dari `.env`).
- [ ] UAT-53 Admin mengelola produk seluruh kelas.
- [ ] UAT-54 Audit log tindakan manual (pelaku, waktu, alasan, objek).
- [ ] UAT-55 Admin mengedit FAQ, ToS berversi, dan pengumuman.
- [ ] **Email transaksional & notifikasi.** Saat ini hanya ada email verifikasi dan reset password, yang dikirim sinkron tanpa queue. Belum ada email pesanan dibuat, pembayaran diverifikasi/ditolak, pengingat batas bayar, atau pengingat Hari H. Butuh scheduler dan `queue:work`.

**Uji manual yang belum dijalankan:**
- [ ] UAT-09 Perilaku di luar jendela PO (sebelum mulai / setelah tutup).
- [ ] UAT-45 QR pickup dipindai kamera ponsel petugas.

## D. Catatan perbaikan test (sudah dikerjakan)

Masalah ini ada di kode test, bukan di aplikasi. Semuanya sudah diperbaiki di `e2e/` dan `uat/`:

- Selektor ambigu (strict mode):
  - label "CERITA PRODUK";
  - link "Terms of Service" (muncul di konten dan footer);
  - baris pesanan di dashboard admin (muncul di tabel pesanan terbaru dan antrean review).
- Helper `emailLink` merusak link verifikasi (`expires=17…` menjadi byte `0x17`). Penyebabnya: decoder quoted-printable diterapkan ke bagian email plain-text. Sekarang decoder hanya mengubah soft line break dan `=3D`.
