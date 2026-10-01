# Product Requirements Document (PRD) — Aplikasi Contoh
### Versi Lengkap, format Agile Product Backlog

Sumber: `BRD - Product Catalogue & Ordering Website.pdf` (design/). Dokumen ini menerjemahkan requirement BRD menjadi backlog yang siap dikerjakan per sprint. Lihat `00-panduan-metode-agile.md` untuk cara memakai dokumen ini dalam Sprint Planning.

---

## 1. Vision Statement

> Membangun website yang memungkinkan **seller** mengelola dan menampilkan produk, sementara **buyer** dapat melihat produk, melakukan pemesanan, pembayaran, mendapatkan digital ticket/order, dan melakukan pickup pada hari yang ditentukan.

## 2. Persona

| Persona | Kebutuhan utama | Frustrasi kalau tidak terpenuhi |
|---|---|---|
| **Admin** | Kontrol penuh atas platform, data akun, dan laporan | Tidak tahu status transaksi keseluruhan, tidak bisa menindak masalah |
| **Seller** | Cara cepat mengelola produk & harga tanpa ribet | Produk salah info/harga sampai ke buyer, stok tidak sinkron |
| **Buyer** | Belanja mudah, kepastian pesanan diterima, bukti jelas untuk pickup | Bayar tapi tidak ada bukti, bingung kapan/dimana pickup |

## 3. Epic (dari Functional Requirements BRD)

| Epic | FR terkait | Deskripsi singkat |
|---|---|---|
| **E1 — Autentikasi & Peran** | FR-01 | Sign up, login, logout, akses sesuai peran |
| **E2 — Katalog Produk** | FR-02 | Tampilkan produk ke buyer, browsing, detail produk |
| **E3 — Manajemen Produk** | FR-03 | Seller kelola produk, harga, stok |
| **E4 — Keranjang Belanja** | FR-04 | Tambah/ubah/hapus item sebelum checkout |
| **E5 — Order & Pembayaran** | FR-05 | Checkout, catat order, verifikasi pembayaran |
| **E6 — Digital Ticket** | FR-06 | Terbitkan bukti order setelah pembayaran sukses |
| **E7 — Pickup** | FR-07 | Validasi ticket saat pengambilan barang |
| **E8 — Admin & Laporan** | Bagian 6 BRD | Kelola akun, monitor, laporan harian |

---

## 4. Product Backlog

Kolom **Prioritas** memakai MoSCoW, **Poin** memakai skala 1/2/3/5/8 (lihat `00-panduan-metode-agile.md` Bagian 4–5).

### Epic 1 — Autentikasi & Peran

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-01 | Sebagai calon buyer, saya ingin **mendaftar akun**, supaya saya bisa mulai berbelanja. | Must | 3 | 1 | Form daftar (nama, email, password); email harus unik; password ter-hash; setelah daftar langsung bisa login. |
| US-02 | Sebagai pengguna terdaftar, saya ingin **login**, supaya saya bisa mengakses fitur sesuai peran saya. | Must | 2 | 1 | Login gagal dengan pesan jelas kalau email/password salah; setelah login diarahkan ke dashboard sesuai peran (admin/seller/buyer). |
| US-03 | Sebagai pengguna, saya ingin **logout**, supaya sesi saya aman di perangkat bersama. | Must | 1 | 1 | Sesi benar-benar berakhir; mengakses halaman yang butuh login setelah logout diarahkan ke halaman login. |
| US-04 | Sebagai sistem, saya ingin **membatasi akses halaman sesuai peran**, supaya seller tidak bisa membuka panel admin dan sebaliknya. | Must | 3 | 1 | Buyer yang mencoba akses URL admin/seller mendapat 403, bukan halaman kosong atau error server. |

### Epic 2 — Katalog Produk

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-05 | Sebagai buyer, saya ingin **melihat daftar produk yang tersedia**, supaya saya tahu apa saja yang bisa dibeli. | Must | 3 | 1 | Hanya produk berstatus "published" & stok > 0 yang tampil; menampilkan nama, gambar, harga, seller. |
| US-06 | Sebagai buyer, saya ingin **melihat detail satu produk**, supaya saya yakin sebelum membeli. | Must | 2 | 1 | Halaman detail menampilkan deskripsi lengkap, stok tersisa, dan tombol "Tambah ke Keranjang". |
| US-07 | Sebagai buyer, saya ingin **mencari/memfilter produk per kategori**, supaya saya cepat menemukan yang saya cari. | Should | 3 | 2 | Pencarian berdasarkan nama produk; filter berdasarkan kategori & seller. |

### Epic 3 — Manajemen Produk

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-08 | Sebagai seller, saya ingin **menambah produk baru**, supaya produk saya bisa dilihat buyer. | Must | 3 | 1 | Form: nama, deskripsi, gambar, harga, stok, kategori; produk baru berstatus "draft" sampai di-publish. |
| US-09 | Sebagai seller, saya ingin **mengubah info & harga produk**, supaya data selalu akurat. | Must | 2 | 1 | Perubahan harga tidak mengubah harga di order yang sudah dibuat sebelumnya (lihat ERD: `price_at_order`). |
| US-10 | Sebagai seller, saya ingin **mengatur stok**, supaya produk yang habis tidak bisa dipesan lagi. | Must | 2 | 1 | Stok otomatis berkurang saat checkout berhasil; produk stok 0 tidak muncul di katalog buyer. |
| US-11 | Sebagai seller, saya ingin **melihat status pesanan produk saya**, supaya saya tahu produk mana yang laku. | Should | 3 | 4 | Daftar order yang memuat produk milik seller tsb, dengan status pembayaran & pickup. |

### Epic 4 — Keranjang Belanja

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-12 | Sebagai buyer, saya ingin **menambahkan produk ke keranjang**, supaya saya bisa membeli beberapa produk sekaligus. | Must | 3 | 2 | Keranjang tersimpan per akun buyer (bukan per sesi anonim, karena buyer wajib login). |
| US-13 | Sebagai buyer, saya ingin **mengubah jumlah atau menghapus item di keranjang**, supaya saya bisa memperbaiki pesanan sebelum bayar. | Must | 2 | 2 | Subtotal ter-update otomatis saat jumlah diubah; stok tersedia dicek ulang. |

### Epic 5 — Order & Pembayaran

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-14 | Sebagai buyer, saya ingin **checkout dari keranjang**, supaya pesanan saya tercatat sistem. | Must | 5 | 2 | Order dibuat dengan status `pending_payment`; stok dikurangi saat order dibuat (bukan saat verifikasi) untuk mencegah dua buyer memesan stok terakhir yang sama. |
| US-15 | Sebagai buyer, saya ingin **mengunggah bukti transfer**, supaya admin bisa memverifikasi pembayaran saya. | Must | 3 | 3 | Upload dibatasi jenis file (jpg/png/pdf) dan ukuran maksimum; order berubah status jadi `waiting_verification`. |
| US-16 | Sebagai admin, saya ingin **memverifikasi atau menolak bukti pembayaran**, supaya hanya pembayaran sah yang lanjut ke pickup. | Must | 5 | 3 | Verifikasi mengubah status order jadi `paid` dan memicu penerbitan ticket (US-17); penolakan mengembalikan stok dan mengubah status jadi `cancelled`. |

### Epic 6 — Digital Ticket

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-17 | Sebagai buyer, saya ingin **menerima digital ticket setelah pembayaran diverifikasi**, supaya saya punya bukti untuk pickup. | Must | 5 | 3 | Ticket berisi kode unik, daftar produk & jumlah, tanggal pickup; bisa dilihat/diunduh dari halaman "Pesanan Saya". |

### Epic 7 — Pickup

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-18 | Sebagai admin/seller di lokasi, saya ingin **menandai ticket sebagai "sudah diambil"**, supaya tidak ada barang yang diambil dua kali dengan ticket yang sama. | Must | 3 | 4 | Ticket yang sudah dipakai tidak bisa dipakai ulang; dicatat waktu & siapa yang memproses pickup. |

### Epic 8 — Admin & Laporan

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-19 | Sebagai admin, saya ingin **mengelola akun seller & buyer**, supaya platform tetap tertib. | Should | 3 | 4 | Admin bisa menonaktifkan akun bermasalah tanpa menghapus riwayat order-nya. |
| US-20 | Sebagai admin, saya ingin **melihat laporan harian** (total order, pembayaran sukses, dibatalkan, produk terjual, revenue, status pickup), supaya saya bisa memantau operasional. | Should | 5 | 4 | Laporan bisa difilter per tanggal; angka revenue hanya menghitung order berstatus `paid`/`picked_up`. |

---

## 5. Out of Scope (Won't Have — kali ini)

Dicatat eksplisit supaya tidak "diam-diam diharapkan" oleh siapa pun:

- Payment gateway otomatis (Midtrans/Xendit dsb.) — pembayaran diverifikasi manual oleh admin, sama seperti pola di modul MarketDay.
- Notifikasi otomatis email/WhatsApp saat status order berubah — buyer mengecek status sendiri lewat halaman "Pesanan Saya".
- Scan QR otomatis untuk pickup — validasi ticket dilakukan admin/seller secara manual lewat kode unik.
- Multi-bahasa & multi-currency.
- Aplikasi mobile native (hanya web, responsif untuk HP).

## 6. Definition of Done (berlaku untuk semua story di atas)

Lihat `00-panduan-metode-agile.md` Bagian 7 — checklist berlaku sama untuk setiap ID di backlog ini, tidak ada pengecualian per sprint.
