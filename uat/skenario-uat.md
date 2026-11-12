# Skenario UAT FORTEXIV

**Versi:** 0.1 (draft untuk sesi UAT berikutnya)
**Tanggal disusun:** 12 November 2026
**Sumber requirement:**
- **BRD** — `design/design/BRD - Product Catalogue & Ordering Website.pdf` (FR-01 s.d. FR-07, Admin Requirements, NFR)
- **PRD** — `design/design/agile-lengkap/01-product-backlog-prd.md` (US-01 s.d. US-20)
- **Handoff** — `FORTEXIV-dokumentasi-handoff-fungsional.md` v1.2 (§6–§16)

Format tiap skenario mengikuti Handoff §16: prasyarat, langkah, hasil diharapkan, hasil aktual, keputusan lulus/tidak lulus, dan pemilik tindak lanjut.

### Legenda

| Simbol | Arti |
|---|---|
| ✅ | Fitur tersedia di aplikasi saat ini; skenario diharapkan lulus |
| ⚠️ | Tersedia sebagian; ada bagian requirement yang belum terpenuhi |
| ❌ | Belum dibangun; skenario dicatat sebagai **gap** (di Playwright ditandai `fixme`) |
| 🤖 | Diotomasi di `uat/tests/…` (bisa ditonton di browser) |
| 🖐 | Perlu dieksekusi manual (butuh ubah konfigurasi/perangkat nyata) |

### Akun uji (dibuat otomatis oleh `uat/support/seed.php`, password `Password123!`)

| Peran | Email | Catatan |
|---|---|---|
| Admin | `admin@e2e.test` | |
| Buyer | `buyer@e2e.test`, `buyer2@e2e.test` | |
| Buyer khusus | `toggle@e2e.test` (dinonaktifkan), `reset@e2e.test` (lupa password), `unverified@e2e.test` (belum verifikasi email) | |
| Seller Kelas A | `seller@e2e.test` | Toko E2E A · E2E Kelas A |
| Seller Kelas B | `seller2@e2e.test` | Toko E2E B · E2E Kelas B |

---

## Ringkasan

| ID | Skenario | Status fitur | Eksekusi |
|---|---|---|---|
| UAT-01 | Beranda menjelaskan acara, status PO, dan dua jalur (Proyek & Katalog) | ✅ | 🤖 |
| UAT-02 | Daftar proyek dan filter kelas | ✅ | 🤖 |
| UAT-03 | Detail proyek terhubung ke produk kelasnya | ✅ | 🤖 |
| UAT-04 | Filter katalog kelas + kategori bersamaan, reset, hasil kosong | ✅ | 🤖 |
| UAT-05 | Detail produk: kelas, cerita, harga, info pickup, dua CTA | ✅ | 🤖 |
| UAT-06 | Produk stok habis terlihat tetapi tidak bisa dibeli | ✅ | 🤖 |
| UAT-07 | Produk draft tidak tampil ke publik | ✅ | 🤖 |
| UAT-08 | Bantuan, cara pickup, dan ToS dapat diakses | ✅ | 🤖 |
| UAT-09 | PO belum buka / sudah tutup menonaktifkan transaksi | ✅ | 🖐 |
| UAT-10 | Daftar akun buyer + verifikasi email | ✅ | 🤖 |
| UAT-11 | Login, logout, dan arah halaman sesuai peran | ✅ | 🤖 |
| UAT-12 | Akses halaman dibatasi sesuai peran | ✅ | 🤖 |
| UAT-13 | Lupa password | ✅ | 🤖 |
| UAT-14 | Akun nonaktif tidak bisa masuk | ✅ | 🤖 |
| UAT-15 | Keranjang lintas kelas dikelompokkan per kelas | ✅ | 🤖 |
| UAT-16 | Validasi stok saat menambah/mengubah keranjang | ✅ | 🤖 |
| UAT-17 | Checkout menampilkan ringkasan, jadwal, lokasi, dan mewajibkan ToS | ✅ | 🤖 |
| UAT-18 | Satu checkout lintas kelas = satu pesanan dengan grup per kelas | ✅ | 🤖 |
| UAT-19 | Beli Sekarang hanya memproses satu produk | ✅ | 🤖 |
| UAT-20 | Unggah bukti transfer (jenis & ukuran file dibatasi) | ✅ | 🤖 |
| UAT-21 | Pesanan belum dibayar tidak memiliki kode/QR pickup | ✅ | 🤖 |
| UAT-22 | Pesanan Saya hanya menampilkan pesanan milik sendiri | ✅ | 🤖 |
| UAT-23 | Perubahan harga produk tidak mengubah pesanan lama | ✅ | 🤖 |
| UAT-24 | Pesanan belum dibayar kedaluwarsa setelah batas bayar | ❌ | 🤖 (fixme) |
| UAT-25 | Buyer mengajukan masalah dan melihat status resolusi | ❌ | 🤖 (fixme) |
| UAT-26 | Admin memverifikasi pembayaran → kode pickup per grup terbit | ✅ | 🤖 |
| UAT-27 | Admin menolak pembayaran dengan alasan → stok kembali | ✅ | 🤖 |
| UAT-28 | Bukti pembayaran hanya dapat dibuka admin | ✅ | 🤖 |
| UAT-29 | Pembayaran yang sudah diproses tidak dapat diproses ulang | ✅ | 🤖 |
| UAT-30 | Seller menambah produk (draft) lalu menerbitkan | ⚠️ | 🤖 |
| UAT-31 | Seller mengubah harga dan stok | ✅ | 🤖 |
| UAT-32 | Produk stok 0 tidak dapat diterbitkan | ✅ | 🤖 |
| UAT-33 | Seller hanya melihat produk & pesanan kelasnya | ✅ | 🤖 |
| UAT-34 | Seller tidak dapat mengelola artikel proyek / cerita produk | ✅ | 🤖 |
| UAT-35 | Produk baru wajib direview admin sebelum tayang | ❌ | 🤖 (fixme) |
| UAT-36 | Rekap produksi kelas hanya dari pesanan dibayar | ❌ | 🤖 (fixme) |
| UAT-37 | Kesiapan Hari H dan laporan insiden kelas | ❌ | 🤖 (fixme) |
| UAT-38 | Settlement & payout per kelas | ❌ | 🤖 (fixme) |
| UAT-39 | Tiket digital berisi ID pesanan, item per grup, tanggal, kode | ⚠️ | 🤖 |
| UAT-40 | Grup A diserahkan lebih dulu; grup B tetap tertunda | ✅ | 🤖 |
| UAT-41 | Grup terakhir diserahkan → pesanan selesai | ✅ | 🤖 |
| UAT-42 | Kode pickup yang sudah dipakai tidak bisa dipakai ulang | ✅ | 🤖 |
| UAT-43 | Kelas lain tidak bisa menyerahkan grup yang bukan miliknya | ✅ | 🤖 |
| UAT-44 | Pencarian fallback di meja pickup (nama / kode pesanan) | ❌ | 🤖 (fixme) |
| UAT-45 | QR pickup dapat dipindai kamera | ❌ | 🖐 |
| UAT-46 | Koreksi handover dan resolusi per grup (refund/batal) | ❌ | 🤖 (fixme) |
| UAT-47 | Handover mencatat waktu dan pelaku | ⚠️ | 🤖 |
| UAT-48 | Dashboard admin memantau pesanan & status pembayaran | ✅ | 🤖 |
| UAT-49 | Laporan harian per tanggal | ✅ | 🤖 |
| UAT-50 | Admin mengelola akun buyer/seller tanpa menghapus riwayat | ✅ | 🤖 |
| UAT-51 | Admin mengelola artikel proyek & cerita produk | ❌ | 🤖 (fixme) |
| UAT-52 | Admin mengatur acara & jendela PO dari panel | ❌ | 🤖 (fixme) |
| UAT-53 | Admin mengelola produk seluruh kelas | ❌ | 🤖 (fixme) |
| UAT-54 | Riwayat tindakan (audit log) untuk tindakan manual | ❌ | 🤖 (fixme) |
| UAT-55 | Admin mengelola FAQ/ToS/pengumuman | ❌ | 🤖 (fixme) |
| UAT-56 | Mobile-first: halaman utama tanpa overflow horizontal | ✅ | 🤖 |
| UAT-57 | Katalog & detail produk termuat cepat | ✅ | 🤖 |
| UAT-58 | Dua buyer berebut stok terakhir → hanya satu pesanan berhasil | ✅ | 🤖 |
| UAT-59 | Data pesanan buyer lain tidak dapat diakses | ✅ | 🤖 |

**Rekap:** 42 ✅ · 3 ⚠️ · 14 ❌

---

## A. Pengunjung & konten publik

### UAT-01 — Beranda menjelaskan acara, status PO, dan dua jalur
- **Sumber:** Handoff §6.1 · BRD FR-02
- **Aktor / prasyarat:** Pengunjung (belum login); PO sedang buka; minimal satu proyek terbit.
- **Langkah:** 1) Buka `/`. 2) Baca hero, banner, dan chip status PO. 3) Klik **Jelajahi Proyek** lalu kembali dan klik **Lihat Katalog**.
- **Hasil diharapkan:** Pengunjung memahami FORTEXIV, melihat status "PO aktif" dengan jadwal PO dan Hari H, melihat cerita kelas, dan kedua jalur (Proyek & Katalog) dapat dibuka tanpa login.
- **Status fitur:** ✅ · **Otomasi:** `uat-01-pengunjung.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-02 — Daftar proyek dan filter kelas
- **Sumber:** Handoff §6.2
- **Aktor / prasyarat:** Pengunjung.
- **Langkah:** 1) Buka **Proyek**. 2) Pilih filter kelas "E2E Kelas B". 3) Pilih **Semua kelas**.
- **Hasil diharapkan:** Setiap kartu menyebut kelas dan mengarah ke satu artikel; filter hanya menampilkan proyek kelas terpilih.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-03 — Detail proyek terhubung ke produk kelasnya
- **Sumber:** Handoff §6.3, §14.2
- **Aktor / prasyarat:** Pengunjung; Kelas A punya produk terbit dan draft.
- **Langkah:** 1) Buka proyek "Proyek Uji Kelas A". 2) Periksa cerita, tujuan, proses, hasil. 3) Periksa bagian **Produk dari proyek ini**. 4) Klik **Lihat produk kelas ini**.
- **Hasil diharapkan:** Cerita lengkap tampil; hanya produk terbit milik Kelas A yang tampil; tombol menuju katalog dengan filter kelas yang sudah terpilih.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-04 — Filter katalog kelas + kategori bersamaan
- **Sumber:** Handoff §6.4 · PRD US-07 · BRD FR-02
- **Aktor / prasyarat:** Pengunjung.
- **Langkah:** 1) Buka **Katalog**. 2) Pilih Kelas = E2E Kelas A dan Kategori = Aksesori, terapkan. 3) Cari kata yang tidak ada. 4) Klik **Atur ulang filter**.
- **Hasil diharapkan:** Kedua filter berlaku bersamaan; hasil kosong menampilkan pesan dan tombol reset; reset mengembalikan semua produk.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-05 — Detail produk lengkap dengan dua CTA
- **Sumber:** Handoff §6.5 · PRD US-06 · BRD FR-02
- **Aktor / prasyarat:** Pengunjung, lalu Buyer.
- **Langkah:** 1) Buka "E2E Tote Bag". 2) Periksa harga, stok, kelas, cerita produk, proyek terkait, info PO & Hari H. 3) Login sebagai buyer dan periksa CTA.
- **Hasil diharapkan:** Semua informasi tampil; pengunjung diminta masuk; buyer melihat **Tambah ke Keranjang** dan **Beli Sekarang** dengan penjelasan perbedaan.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-06 — Produk stok habis terlihat tetapi tidak bisa dibeli
- **Sumber:** Handoff §6.4–6.5 · PRD US-05, US-10
- **Aktor / prasyarat:** Buyer; produk "E2E Habis Terjual" stok 0, status terbit.
- **Langkah:** 1) Filter katalog **Stok habis**. 2) Buka detail produk.
- **Hasil diharapkan:** Produk ditandai "Stok habis" dan tidak ada tombol beli.
- **Catatan:** PRD US-05 menyebut produk stok 0 **tidak tampil**, sedangkan Handoff §6.4 menyebut produk habis ditampilkan sebagai tidak tersedia. Aplikasi mengikuti Handoff. **Perlu konfirmasi pemilik produk.**
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-07 — Produk draft tidak tampil ke publik
- **Sumber:** Handoff §6.4, §8.4 · PRD US-08
- **Langkah:** 1) Cari "E2E Draft Lamp" di katalog. 2) Buka `/products/e2e-draft-lamp`.
- **Hasil diharapkan:** Tidak muncul di katalog maupun proyek; URL langsung menghasilkan 404.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-08 — Bantuan, cara pickup, dan ToS dapat diakses
- **Sumber:** Handoff §6.11, §14.8
- **Langkah:** 1) Dari footer buka **Cara memesan**, **Pengambilan barang**, **Terms of Service**. 2) Dari checkout buka tautan ToS.
- **Hasil diharapkan:** Instruksi pickup dan versi ToS terlihat; informasi konsisten dengan checkout.
- **Status fitur:** ✅ · **Otomasi:** `uat-01`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-09 — PO belum buka / sudah tutup menonaktifkan transaksi
- **Sumber:** Handoff §5.4, §6.1, §16 · BRD NFR Availability
- **Prasyarat:** Ubah `MARKETPLACE_PO_START`/`MARKETPLACE_PO_END` di environment uji sehingga PO belum buka, lalu ulangi dengan PO sudah tutup (saat ini jadwal PO hanya bisa diubah lewat `.env`, lihat UAT-52).
- **Langkah:** 1) Buka beranda, katalog, detail produk, keranjang, checkout. 2) Coba kirim checkout yang sudah terbuka sebelum PO ditutup.
- **Hasil diharapkan:** Konten tetap dapat dibaca; tombol beli diganti penjelasan "PO belum dibuka/sudah ditutup"; checkout tidak membuat pesanan baru dan menampilkan pesan jelas.
- **Status fitur:** ✅ · **Eksekusi:** 🖐 manual
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

## B. Akun & peran

### UAT-10 — Daftar akun buyer + verifikasi email
- **Sumber:** BRD FR-01 · PRD US-01
- **Langkah:** 1) Daftar sebagai buyer. 2) Coba buka Keranjang sebelum verifikasi. 3) Klik tautan verifikasi dari email. 4) Buka **Akun**.
- **Hasil diharapkan:** Sebelum verifikasi diarahkan ke halaman verifikasi; setelah verifikasi bisa berbelanja; data akun benar. Email duplikat ditolak dengan pesan jelas.
- **Status fitur:** ✅ · **Otomasi:** `uat-02-akun.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-11 — Login, logout, dan arah halaman sesuai peran
- **Sumber:** BRD FR-01 · PRD US-02, US-03
- **Langkah:** Login sebagai admin, seller, buyer bergantian; logout; buka halaman terlindungi setelah logout.
- **Hasil diharapkan:** Admin → `/admin`, seller → `/seller`, buyer → beranda; password salah memberi pesan jelas; setelah logout halaman terlindungi mengarah ke login.
- **Status fitur:** ✅ · **Otomasi:** `uat-02`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-12 — Akses halaman dibatasi sesuai peran
- **Sumber:** PRD US-04 · Handoff §4
- **Langkah:** Buyer membuka URL admin/seller; seller membuka keranjang/admin; admin membuka portal seller.
- **Hasil diharapkan:** HTTP 403 (bukan halaman kosong/error server).
- **Status fitur:** ✅ · **Otomasi:** `uat-02`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-13 — Lupa password
- **Sumber:** BRD FR-01
- **Langkah:** Minta tautan reset, buka tautan dari email, atur password baru, login dengan password baru.
- **Hasil diharapkan:** Password lama tidak berlaku; password baru berlaku; token palsu ditolak.
- **Status fitur:** ✅ · **Otomasi:** `uat-02`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-14 — Akun nonaktif tidak bisa masuk
- **Sumber:** PRD US-19 · BRD Admin Requirements
- **Langkah:** Admin menonaktifkan `toggle@e2e.test`; pengguna mencoba login; admin mengaktifkan kembali.
- **Hasil diharapkan:** Login ditolak dengan pesan "Akun ini telah dinonaktifkan."; sesi aktif kehilangan akses; setelah diaktifkan bisa login lagi.
- **Status fitur:** ✅ · **Otomasi:** `uat-02`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

## C. Buyer: keranjang, checkout, pembayaran

### UAT-15 — Keranjang lintas kelas dikelompokkan per kelas
- **Sumber:** BRD FR-04 · PRD US-12, US-13 · Handoff §6.6
- **Langkah:** 1) Tambah Tote Bag (Kelas A) dan Kartu Pos (Kelas B). 2) Ubah jumlah Tote Bag. 3) Hapus satu item lain.
- **Hasil diharapkan:** Item dikelompokkan per kelas; subtotal & total diperbarui; item terhapus hilang; jumlah ikon keranjang sesuai.
- **Status fitur:** ✅ · **Otomasi:** `uat-04-alur-pesanan.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-16 — Validasi stok di keranjang
- **Sumber:** PRD US-10, US-13
- **Langkah:** Tambah jumlah melebihi stok dari detail produk; ubah jumlah di keranjang melebihi stok.
- **Hasil diharapkan:** Ditolak dengan pesan; jumlah di keranjang tidak berubah.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-17 — Checkout menampilkan ringkasan dan mewajibkan ToS
- **Sumber:** Handoff §6.7, §14.8 · BRD FR-05
- **Langkah:** 1) Lanjut ke checkout. 2) Periksa grup, total, tenggat bayar, tanggal & lokasi pickup. 3) Kirim tanpa centang ToS. 4) Kirim dengan ToS.
- **Hasil diharapkan:** Semua informasi tampil sebelum pembayaran; tanpa ToS ditolak; versi & waktu persetujuan ToS tercatat di detail pesanan.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-18 — Satu checkout lintas kelas = satu pesanan
- **Sumber:** Handoff §2, §6.7, §16
- **Langkah:** Checkout keranjang dua kelas.
- **Hasil diharapkan:** Satu kode pesanan `FX-…`, satu total, dua grup kelas; stok berkurang sesuai jumlah; keranjang kosong.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-19 — Beli Sekarang hanya memproses satu produk
- **Sumber:** Handoff §6.5, §14.6
- **Langkah:** Isi keranjang dengan Kartu Pos; dari detail Notebook pilih **Beli Sekarang** dan buat pesanan.
- **Hasil diharapkan:** Checkout hanya berisi Notebook; isi keranjang tetap ada setelah pesanan dibuat.
- **Status fitur:** ✅ · **Otomasi:** `uat-05-pembayaran-ditolak.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-20 — Unggah bukti transfer
- **Sumber:** PRD US-15 · Handoff §6.8
- **Langkah:** Unggah file teks (ditolak), lalu PNG.
- **Hasil diharapkan:** Hanya JPG/PNG/PDF ≤ 5 MB diterima; status berubah menjadi "Menunggu verifikasi"; form unggah hilang.
- **Status fitur:** ✅ · **Otomasi:** `uat-05`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-21 — Pesanan belum dibayar tidak memiliki kode/QR pickup
- **Sumber:** Handoff §6.8, §6.10, §14.7
- **Langkah:** Buka detail pesanan saat "Menunggu pembayaran" dan "Menunggu verifikasi"; buka URL tiket langsung.
- **Hasil diharapkan:** Tidak ada tombol tiket; URL tiket 404; seller belum bisa menyerahkan.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-22 — Pesanan Saya hanya menampilkan pesanan milik sendiri
- **Sumber:** Handoff §6.9 · BRD §5 (order history)
- **Langkah:** Buyer membuka **Pesanan saya**; buyer lain membuka URL pesanan tersebut.
- **Hasil diharapkan:** Daftar memuat kode, tanggal, jumlah grup, total, status; buyer lain mendapat 404.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-23 — Perubahan harga produk tidak mengubah pesanan lama
- **Sumber:** PRD US-09 · Handoff §16
- **Langkah:** Buyer memesan "E2E Gelang Anyam" (Rp 12.000); seller mengubah harga menjadi Rp 20.000; buyer membuka pesanan.
- **Hasil diharapkan:** Pesanan tetap Rp 12.000; detail produk menampilkan Rp 20.000.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-24 — Pesanan belum dibayar kedaluwarsa ❌
- **Sumber:** Handoff §6.8, §13, §16, §17 (#2)
- **Hasil diharapkan:** Setelah batas bayar, pesanan berstatus "Kedaluwarsa", stok kembali, tidak ada QR.
- **Kondisi saat ini:** Tidak ada mekanisme kedaluwarsa; stok tertahan tanpa batas.
- **Status fitur:** ❌ · **Otomasi:** `uat-06-gap.spec.ts` (fixme)
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-25 — Buyer mengajukan masalah dan melihat status resolusi ❌
- **Sumber:** Handoff §5.1, §6.9–6.10
- **Hasil diharapkan:** Dari detail pesanan buyer bisa mengajukan masalah dan memantau statusnya.
- **Kondisi saat ini:** Belum ada.
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

## D. Admin: pembayaran

### UAT-26 — Admin memverifikasi pembayaran
- **Sumber:** PRD US-16, US-17 · BRD FR-05, FR-06
- **Langkah:** Admin membuka **Payment review**, mengunduh bukti, klik **Verify**.
- **Hasil diharapkan:** Status pesanan "Siap diambil"; tiket dengan kode pickup per grup kelas terbit.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-27 — Admin menolak pembayaran dengan alasan
- **Sumber:** PRD US-16 · Handoff §8.5
- **Langkah:** Admin mengisi alasan dan klik **Reject**.
- **Hasil diharapkan:** Pesanan "Dibatalkan", alasan terlihat buyer, stok kembali, tidak ada tiket.
- **Catatan:** Handoff §13 membedakan status "Gagal" dan pembatalan; saat ini penolakan langsung membatalkan pesanan (buyer tidak bisa unggah ulang).
- **Status fitur:** ✅ · **Otomasi:** `uat-05`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-28 — Bukti pembayaran hanya dapat dibuka admin
- **Sumber:** BRD NFR Security
- **Langkah:** Seller dan buyer membuka URL unduh bukti pembayaran.
- **Hasil diharapkan:** 403.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-29 — Pembayaran tidak dapat diproses ulang
- **Sumber:** BRD NFR Data Integrity
- **Langkah:** Setelah verifikasi/penolakan, periksa baris pembayaran.
- **Hasil diharapkan:** Tombol Verify/Reject tidak tersedia lagi.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`, `uat-05`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

## E. Seller / kelas

### UAT-30 — Seller menambah produk lalu menerbitkan ⚠️
- **Sumber:** BRD FR-03, Seller Flow · PRD US-08
- **Langkah:** Isi nama, deskripsi, gambar, harga, stok, kategori, proyek; simpan; terbitkan.
- **Hasil diharapkan (BRD/PRD):** Produk tersimpan sebagai draft, setelah diterbitkan tampil di katalog dan halaman proyek.
- **Catatan:** Handoff §8.4 mensyaratkan review admin sebelum tayang (lihat UAT-35); saat ini seller menerbitkan sendiri.
- **Status fitur:** ⚠️ · **Otomasi:** `uat-03-seller.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-31 — Seller mengubah harga dan stok
- **Sumber:** BRD FR-03 · PRD US-09, US-10
- **Hasil diharapkan:** Perubahan tersimpan dan terlihat di daftar produk.
- **Status fitur:** ✅ · **Otomasi:** `uat-03`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-32 — Produk stok 0 tidak dapat diterbitkan
- **Sumber:** PRD US-10
- **Hasil diharapkan:** Penerbitan ditolak; produk tetap draft.
- **Status fitur:** ✅ · **Otomasi:** `uat-03`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-33 — Seller hanya melihat data kelasnya
- **Sumber:** Handoff §7, §14.13 · PRD US-11
- **Langkah:** Seller A membuka daftar produk, daftar pesanan, dan URL edit produk Kelas B.
- **Hasil diharapkan:** Produk/pesanan Kelas B tidak tampil; URL edit produk kelas lain 404.
- **Status fitur:** ✅ · **Otomasi:** `uat-03`, `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-34 — Seller tidak dapat mengelola artikel proyek / cerita produk
- **Sumber:** Handoff §4, §7.2, §14.13
- **Hasil diharapkan:** Form produk seller tidak memiliki kolom cerita produk; tidak ada menu artikel di portal seller.
- **Status fitur:** ✅ · **Otomasi:** `uat-03`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-35 — Produk baru wajib direview admin sebelum tayang ❌
- **Sumber:** Handoff §8.4, §13 (Draft / Menunggu review / Perlu revisi / Disetujui / Dipublikasikan)
- **Hasil diharapkan:** Seller mengajukan review; admin menyetujui atau meminta revisi dengan alasan; hanya produk disetujui yang tayang.
- **Kondisi saat ini:** Seller langsung menerbitkan produk.
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-36 — Rekap produksi kelas hanya dari pesanan dibayar ❌
- **Sumber:** Handoff §7.4, §14.7
- **Hasil diharapkan:** Seller melihat total jumlah per produk dari pesanan dibayar, terpisah dari yang menunggu verifikasi.
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-37 — Kesiapan Hari H dan laporan insiden kelas ❌
- **Sumber:** Handoff §7.5, §12.3
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-38 — Settlement & payout per kelas ❌
- **Sumber:** Handoff §7.7, §8.8, §16 ("Kelas A bersih, kelas B punya isu")
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

## F. Tiket digital & Hari H

### UAT-39 — Tiket digital lengkap ⚠️
- **Sumber:** BRD FR-06 · PRD US-17 · Handoff §6.10
- **Hasil diharapkan:** Tiket memuat kode pesanan, nama pengambil, tanggal pickup, status, dan per grup kelas: kode pickup + daftar produk.
- **Catatan:** QR masih ilustrasi demo (lihat UAT-45); PRD menyebut tiket "bisa diunduh" — belum ada unduhan.
- **Status fitur:** ⚠️ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-40 — Grup A diserahkan lebih dulu, grup B tetap tertunda
- **Sumber:** Handoff §12.2, §16
- **Langkah:** Seller A klik **Konfirmasi serah-terima** pada pesanannya.
- **Hasil diharapkan:** Grup A "Sudah diserahkan", grup B "Menunggu pengambilan", pesanan "Sebagian diserahkan".
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-41 — Grup terakhir diserahkan → pesanan selesai
- **Sumber:** Handoff §13 (aturan penyelesaian), §16
- **Langkah:** Admin memasukkan kode grup B dan memilih Kelas B di **Validate pickup**.
- **Hasil diharapkan:** Pesanan "Selesai diambil" di sisi buyer, seller, dan admin.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-42 — Kode pickup tidak bisa dipakai ulang
- **Sumber:** PRD US-18 · Handoff §8.7, §14.10
- **Hasil diharapkan:** Pesan "Grup kelas ini sudah diserahkan."
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-43 — Kelas lain tidak bisa menyerahkan grup yang bukan miliknya
- **Sumber:** Handoff §7.6, §16 ("Seller A membuka pickup order milik seller B")
- **Langkah:** Seller A mengirim kode grup B; admin memilih kelas yang salah untuk sebuah kode.
- **Hasil diharapkan:** Ditolak "Kode pickup tidak valid untuk kelas ini."; status grup tidak berubah.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-44 — Pencarian fallback di meja pickup ❌
- **Sumber:** Handoff §8.7, §12.3 ("QR tak terbaca / buyer tidak membawa QR")
- **Hasil diharapkan:** Petugas dapat mencari pesanan berdasarkan nama buyer/kode pesanan dan mencatat penggunaan fallback.
- **Kondisi saat ini:** Meja pickup hanya menerima kode pickup grup.
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-45 — QR pickup dapat dipindai kamera ❌
- **Sumber:** Handoff §1 (mobile), §6.10, §12.2
- **Kondisi saat ini:** QR adalah ilustrasi "DEMO" yang tidak dapat dipindai; kode teks dipakai sebagai pengganti.
- **Status fitur:** ❌ · **Eksekusi:** 🖐 manual dengan ponsel petugas

### UAT-46 — Koreksi handover dan resolusi per grup ❌
- **Sumber:** Handoff §8.5, §13, §14.10–14.11, §16 ("Satu grup direfund/diselesaikan resmi")
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-47 — Handover mencatat waktu dan pelaku ⚠️
- **Sumber:** PRD US-18 · Handoff §8.9
- **Hasil diharapkan:** Waktu dan petugas yang menyerahkan terlihat oleh admin.
- **Kondisi saat ini:** Waktu tampil ke buyer ("Diserahkan dd/mm/yyyy"); pelaku tersimpan di database tetapi belum tampil di UI.
- **Status fitur:** ⚠️ · **Otomasi:** `uat-04` (waktu)
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

## G. Admin: pemantauan & pengelolaan

### UAT-48 — Dashboard admin memantau pesanan & pembayaran
- **Sumber:** BRD Admin Requirements (Monitor orders / payment status)
- **Hasil diharapkan:** Angka ringkasan, daftar pesanan dengan status pembayaran, status pesanan, dan grup kelas; daftar pembayaran menunggu verifikasi.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`, `uat-06`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-49 — Laporan harian per tanggal
- **Sumber:** BRD §6 (laporan harian) · PRD US-20
- **Hasil diharapkan:** Total pesanan, pembayaran sukses, dibatalkan, produk terjual, revenue (hanya pesanan dibayar), pesanan diambil; dapat difilter tanggal.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`, `uat-06`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-50 — Admin mengelola akun buyer/seller
- **Sumber:** BRD Admin Requirements · PRD US-19
- **Hasil diharapkan:** Daftar akun buyer & seller (tanpa admin), dapat dinonaktifkan/diaktifkan tanpa menghapus riwayat.
- **Status fitur:** ✅ · **Otomasi:** `uat-02`, `uat-06`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-51 — Admin mengelola artikel proyek & cerita produk ❌
- **Sumber:** Handoff §8.3, §14.1, §16
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-52 — Admin mengatur acara & jendela PO dari panel ❌
- **Sumber:** Handoff §8.2
- **Kondisi saat ini:** Diatur lewat `.env` oleh developer.
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-53 — Admin mengelola produk seluruh kelas ❌
- **Sumber:** BRD Admin Requirements ("Manage products") · Handoff §8.4
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-54 — Riwayat tindakan (audit log) ❌
- **Sumber:** Handoff §8.9, §14.14
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

### UAT-55 — Admin mengelola FAQ/ToS/pengumuman ❌
- **Sumber:** Handoff §5.3, §8.9
- **Status fitur:** ❌ · **Otomasi:** `uat-06` (fixme)

## H. Non-fungsional

### UAT-56 — Mobile-first tanpa overflow horizontal
- **Sumber:** Handoff §1 (Prinsip Mobile-First, kriteria 1–9) · BRD NFR Usability
- **Langkah:** Buka halaman utama setiap peran pada viewport ponsel (Pixel 7).
- **Hasil diharapkan:** Tidak ada overflow horizontal; tombol utama dapat disentuh; alur keranjang → checkout dapat diselesaikan.
- **Status fitur:** ✅ · **Otomasi:** `uat-07-nonfungsional.spec.ts`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-57 — Katalog & detail produk termuat cepat
- **Sumber:** BRD NFR Performance
- **Hasil diharapkan:** Di lingkungan uji lokal, `DOMContentLoaded` katalog dan detail produk < 2 detik. (Ambang produksi perlu ditetapkan.)
- **Status fitur:** ✅ · **Otomasi:** `uat-07`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-58 — Dua buyer berebut stok terakhir
- **Sumber:** PRD US-14 · BRD NFR Data Integrity
- **Langkah:** Buyer 1 dan buyer 2 sama-sama memasukkan "E2E Stok Terakhir" (stok 1) ke keranjang; buyer 1 checkout lebih dulu, lalu buyer 2.
- **Hasil diharapkan:** Hanya pesanan buyer 1 yang dibuat; buyer 2 mendapat pesan stok berubah; stok tidak pernah negatif.
- **Status fitur:** ✅ · **Otomasi:** `uat-07`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

### UAT-59 — Data pesanan buyer lain tidak dapat diakses
- **Sumber:** BRD NFR Security · Handoff §6.9
- **Hasil diharapkan:** URL pesanan/tiket milik buyer lain menghasilkan 404.
- **Status fitur:** ✅ · **Otomasi:** `uat-04`
- **Hasil aktual:** … · **Keputusan:** ☐ Lulus ☐ Tidak lulus · **Tindak lanjut:** …

---

## Matriks keterlacakan

| Requirement | Skenario |
|---|---|
| BRD FR-01 Autentikasi | UAT-10, 11, 12, 13, 14 |
| BRD FR-02 Katalog | UAT-01, 04, 05, 06, 07 |
| BRD FR-03 Manajemen produk | UAT-30, 31, 32, 33 |
| BRD FR-04 Keranjang | UAT-15, 16 |
| BRD FR-05 Order & pembayaran | UAT-17, 18, 20, 26, 27, 29 |
| BRD FR-06 Tiket digital | UAT-21, 26, 39 |
| BRD FR-07 Pickup | UAT-40, 41, 42, 43, 44, 45 |
| BRD §6 Admin | UAT-14, 48, 49, 50, 53 |
| BRD §7 NFR | UAT-28, 56, 57, 58, 59 |
| PRD US-01 – US-04 | UAT-10, 11, 12 |
| PRD US-05 – US-07 | UAT-04, 05, 06 |
| PRD US-08 – US-11 | UAT-23, 30, 31, 32, 33 |
| PRD US-12 – US-13 | UAT-15, 16 |
| PRD US-14 – US-16 | UAT-18, 20, 26, 27, 58 |
| PRD US-17 – US-18 | UAT-39, 42, 47 |
| PRD US-19 – US-20 | UAT-14, 49, 50 |
| Handoff §6 Publik & buyer | UAT-01 – UAT-25 |
| Handoff §7 Seller | UAT-30 – UAT-38, 43 |
| Handoff §8 Admin | UAT-26 – 29, 35, 44, 46, 48 – 55 |
| Handoff §12 Hari H | UAT-40 – UAT-47 |
| Handoff §16 Skenario wajib | UAT-09, 17, 18, 23, 24, 38, 40, 41, 42, 43, 46, 51 |

## Catatan untuk sesi UAT berikutnya

1. Jalankan suite otomatis (`npm run test:watch` di folder `uat`) dan isi kolom **Hasil aktual** berdasarkan laporan Playwright.
2. Eksekusi manual UAT-09 dan UAT-45.
3. Putuskan konflik requirement: UAT-06 (produk stok habis tampil atau disembunyikan) dan UAT-27 (penolakan = batal atau boleh unggah ulang).
4. Skenario ❌ dipakai sebagai daftar gap; cocokkan dengan `design/rencana-fitur-admin-seller-buyer.md`.
