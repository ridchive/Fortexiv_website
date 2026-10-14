# Dokumentasi Pengembang — FORTEXIV Marketplace

FORTEXIV adalah marketplace sekolah untuk produk hasil proyek kelas. Aplikasi memakai Laravel 13 dengan Blade, SQLite, dan Vite + Tailwind 4. Pembayaran memakai **transfer bank manual** yang diverifikasi admin. Barang diambil di lokasi dengan **ticket pickup** sekali pakai.

Panduan untuk pengguna akhir ada di [PANDUAN_PEMBELI.md](PANDUAN_PEMBELI.md), [PANDUAN_PENJUAL.md](PANDUAN_PENJUAL.md), [PANDUAN_ADMIN.md](PANDUAN_ADMIN.md), dan [PANDUAN_ADMIN_CS.md](PANDUAN_ADMIN_CS.md) (customer service). Panduan deploy ke server production ada di [PANDUAN_DEVOPS.md](PANDUAN_DEVOPS.md). Cara menjalankan e2e test ada di bagian 9.

---

## 1. Menjalankan secara lokal

### Kebutuhan

- **PHP 8.4+**, karena `composer.lock` mengunci Symfony 8.1 yang butuh PHP ≥ 8.4.1. Ekstensi yang dibutuhkan: `pdo_sqlite`, `sqlite3`, `fileinfo`, `mbstring`, `openssl`, `zip`.
- Composer 2, Node.js + npm.

> Di mesin pengembangan saat ini, PHP di PATH (`C:\xampp\php`) adalah 8.3. Gunakan `C:\xampp\php84\php.exe`, atau tambahkan `C:\xampp\php84` di depan PATH untuk sesi terminal:
> ```powershell
> $env:PATH = "C:\xampp\php84;$env:PATH"
> ```

### Setup

```sh
composer run setup     # install, .env, key, database.sqlite, migrate --seed, storage:link
npm install
npm run build          # atau `npm run dev` saat mengubah CSS/JS
php artisan serve      # http://localhost:8000
```

Agar checkout bisa berjalan, isi rekening tujuan di `.env`:

```env
PAYMENT_BANK_NAME=
PAYMENT_ACCOUNT_NAME=
PAYMENT_ACCOUNT_NUMBER=
```

Buat akun admin (password minimal 12 karakter, dimasukkan secara interaktif):

```sh
php artisan marketplace:create-admin "Nama Admin" admin@example.com
```

### Perintah lain

| Perintah | Fungsi |
|---|---|
| `php artisan test --compact` | Menjalankan test (saat ini 12 test, semua lulus). |
| `php artisan migrate:fresh --seed` | Reset database dan isi ulang kategori + 12 proyek kelas. |
| `vendor/bin/pint` | Merapikan format kode PHP. |
| `php artisan route:list --except-vendor` | Daftar route aplikasi. |

### Laravel Boost

Laravel Boost (`laravel/boost`, dev) sudah terpasang:

- `CLAUDE.md` berisi panduan AI yang dibuat oleh Boost.
- `.claude/skills/` berisi skill agent.
- `.mcp.json` berisi konfigurasi MCP server `php artisan boost:mcp`. Di mesin ini, `command` sudah diarahkan ke `C:/xampp/php84/php.exe`. Sesuaikan di mesin lain.
- Perbarui dengan `php artisan boost:update`.

---

## 2. Struktur & arsitektur

```
app/
  Console/Commands/CreateAdmin.php   # marketplace:create-admin
  Http/Controllers/
    AuthController.php               # login, register (buyer/seller), logout
    MarketplaceController.php        # katalog, keranjang, checkout, pesanan, bukti bayar, ticket
    SellerController.php             # CRUD produk, publish, pesanan penjual
    AdminController.php              # verifikasi bayar, akun, pickup, laporan
  Http/Middleware/EnsureRole.php     # alias `role:` — cek peran + is_active
  Models/                            # User, Seller, Project, Category, Product, CartItem,
                                     # Order, OrderItem, Payment, Ticket
  Support/Money.php                  # Money::format(int) → "Rp 25.000"
config/marketplace.php               # data rekening transfer (dari env PAYMENT_*)
database/migrations/2026_10_07_000001_create_marketplace_tables.php
database/seeders/MarketplaceSeeder.php
resources/views/{layouts,auth,marketplace,seller,admin}/
routes/web.php
tests/Feature/MarketplaceTest.php
```

Pola yang dipakai:

- **Controller tebal, tanpa service layer.** Logika bisnis ditulis langsung di controller. Otorisasi kepemilikan memakai `abort_unless(...)` di controller, belum memakai Policy. Validasi memakai `$request->validate()` inline, belum memakai Form Request.
- **Peran** disimpan di kolom string `users.role`: `buyer`, `seller`, atau `admin`. Route dilindungi dengan `middleware(['auth', 'role:<peran>'])`. `EnsureRole` juga menolak akun nonaktif, serta penjual yang belum punya baris `sellers`.
- **Status** (pesanan, produk, pembayaran) disimpan sebagai string biasa, belum memakai Enum.
- **Uang** disimpan sebagai `unsignedInteger` dalam rupiah bulat. Ada batas total 4.294.967.295 saat checkout.
- **Konkurensi**: checkout, unggah bukti, verify, reject, dan pickup berjalan di dalam `DB::transaction` dengan `lockForUpdate()`.
- **File**:
  - gambar produk disimpan di disk `public` (`storage/app/public/products`),
  - bukti transfer disimpan di disk `local` yang privat (`storage/app/private/payment-proofs`) dan hanya bisa diunduh admin lewat route `admin.payments.proof`.

---

## 3. Model data

```
users 1─1 sellers ─* products *─1 categories
              │          │
              *─1 projects ─* products
users(buyer) 1─* cart_items *─1 products
users(buyer) 1─* orders 1─* order_items *─1 products (nullable), *─1 sellers
                 orders 1─1 payments
                 orders 1─1 tickets
```

| Tabel | Kolom penting | Catatan |
|---|---|---|
| `users` | `role`, `is_active`, `phone` | Default `role=buyer`. |
| `sellers` | `user_id` (unique), `project_id`, `store_name`, `description` | `description` belum dipakai di UI. |
| `projects` | `class_name`, `grade`, `title`, `introduction`, `story`, `outcome`, `image`, `is_published` | Hanya diisi lewat seeder. `image` belum dipakai. |
| `categories` | `name`, `slug` | Diisi seeder: Handmade, Food, Stationery, Tech. |
| `products` | `seller_id`, `project_id`, `category_id`, `slug` (unique), `price`, `stock`, `status` | `status`: `draft` atau `published`. |
| `cart_items` | `user_id` + `product_id` (unique), `quantity` | |
| `orders` | `order_code` (`FX-00000001`), `status`, `total_price`, `pickup_date` | |
| `order_items` | `product_name`, `price_at_order`, `subtotal`, `seller_id` | Snapshot nama dan harga saat dipesan. |
| `payments` | `proof_image`, `method`, `status`, `verified_by`, `verified_at`, `notes` | `status`: `pending`, `verified`, atau `rejected`. Satu per pesanan. |
| `tickets` | `ticket_code` (`FXT-` + 12 karakter), `issued_at`, `picked_up_at`, `picked_up_by` | Satu per pesanan, sekali pakai. |

### Siklus status pesanan

```
pending_payment ──(buyer upload bukti)──► waiting_verification
waiting_verification ──(admin verify)──► paid  (+ ticket dibuat)
waiting_verification ──(admin reject)──► cancelled  (+ stok dikembalikan)
paid ──(admin validasi ticket)──► picked_up
```

Stok dikurangi saat **checkout**, dan dikembalikan hanya saat pembayaran **ditolak**.

---

## 4. Fitur yang sudah ada

### Publik

- Beranda: showcase proyek kelas + katalog produk (published dan stok > 0), dengan pencarian nama, filter kategori, dan paginasi 12 produk.
- Halaman detail produk (`/products/{slug}`) dan detail proyek kelas (`/projects/{id}`).
- Health check di `/up`.

### Autentikasi

- Registrasi sebagai **buyer** atau **seller**. Seller wajib mengisi nama toko dan bisa menautkan proyek kelas. Peran admin tidak bisa dipilih saat registrasi.
- Login dengan opsi *remember*, regenerasi session, dan penolakan akun nonaktif. Pengguna diarahkan sesuai peran.
- Logout.

### Pembeli

- Keranjang: tambah, ubah jumlah, hapus, dengan validasi stok.
- Checkout dengan tanggal pickup (≥ hari ini). Pesanan baru dibuat dengan status `pending_payment`.
- Checkout dan unggah bukti dinonaktifkan (HTTP 503) bila rekening belum dikonfigurasi.
- Unggah bukti transfer (JPG/PNG/PDF, maks 5 MB) dan metode transfer.
- Riwayat dan detail pesanan, termasuk catatan penolakan dari admin.
- Ticket pickup setelah pembayaran diverifikasi.

### Penjual

- Dashboard ringkasan dan pesanan terbaru.
- CRUD produk dengan unggah gambar dan slug unik otomatis. Produk baru berstatus `draft`.
- Publish produk (butuh stok ≥ 1).
- Produk yang sudah pernah dipesan tidak bisa dihapus.
- Daftar item pesanan berisi produk milik penjual, beserta status bayar dan pickup.

### Admin

- Dashboard statistik.
- Review pembayaran: unduh bukti, **Verify** (menerbitkan ticket) atau **Reject** dengan alasan (mengembalikan stok).
- Kelola akun buyer dan seller (aktif/nonaktif).
- Validasi ticket pickup sekali pakai.
- Laporan harian: pesanan, lunas, batal, diambil, pendapatan, dan produk terjual.
- Command `marketplace:create-admin`.

### Cakupan test (`tests/Feature/MarketplaceTest.php`)

Test sudah mencakup:

- tampilan beranda,
- registrasi seller,
- login dan logout,
- alur lengkap checkout → bukti → verify → ticket sekali pakai,
- reject yang mengembalikan stok,
- isolasi data antar buyer dan seller,
- render halaman per peran,
- checkout saat stok habis,
- checkout tanpa konfigurasi rekening.

---

## 5. Keterbatasan yang diketahui

Daftar ini disusun dari membaca kode:

1. **Stok tertahan tanpa batas waktu.** Pesanan `pending_payment` yang tidak pernah dibayar tetap memegang stok selamanya. Belum ada pembatalan otomatis, dan pembeli juga tidak bisa membatalkan pesanannya sendiri.
2. **Tidak ada unggah ulang bukti.** Pembayaran yang ditolak langsung membatalkan pesanan, jadi pembeli tidak bisa memperbaiki bukti yang salah dan harus checkout ulang.
3. **Tidak ada unpublish.** Untuk menyembunyikan produk, penjual harus mengubah stok menjadi 0.
4. **Belum ada reset password dan verifikasi email.** Belum ada juga *throttling* login.
5. **Proyek kelas dan kategori** hanya bisa dikelola lewat seeder atau database, belum ada UI admin. Kolom `projects.image` dan `sellers.description` belum dipakai.
6. **Tidak ada notifikasi** (email/WhatsApp) saat status pesanan berubah.
7. **Laporan** hanya per hari, berdasarkan tanggal pesanan dibuat, dan belum bisa diekspor.
8. **Error UX:** `updateCart` memakai `abort_if(..., 422)` untuk stok berlebih, sehingga pengguna melihat halaman error, bukan pesan validasi di form.
9. **File statis lama** di root (`index.html`, `login.html`, `product.html`, `products.js`, `about.html`, `FORTEXIVsI.html`, `dashboard_ujicoba`) dan folder `.laravel-scaffold/` tidak dipakai oleh aplikasi Laravel.
10. **Belum ada fitur customer service** di dalam aplikasi. Admin membantu lewat kanal luar dan banyak kasus harus dieskalasi ke tim teknis. Lihat [bagian 7](#7-rancangan-fitur-customer-service).

---

## 6. Ide pengembangan

### Prioritas tinggi (memperbaiki alur yang ada)

- **Pembatalan otomatis pesanan kedaluwarsa.** Buat command terjadwal (`routes/console.php`, `Schedule::command(...)->hourly()`) yang membatalkan `pending_payment` lebih lama dari N jam dan mengembalikan stok. Tambahkan juga tombol "Batalkan pesanan" untuk pembeli.
- **Unggah ulang bukti.** Saat ditolak, kembalikan pesanan ke `pending_payment` (bukan `cancelled`) dan izinkan beberapa `payments` per pesanan, atau hapus bukti lama.
- **Keamanan akun.** Tambahkan rate limiting login (`RateLimiter`/`throttle`), reset password, dan verifikasi email.
- **Unpublish produk** untuk penjual.
- **Ganti `abort_if` di `updateCart`** dengan `ValidationException` agar pesan tampil di form.

### Kualitas kode

- Ubah status menjadi **PHP Enum** (`OrderStatus`, `PaymentStatus`, `ProductStatus`, `UserRole`) dengan cast di model.
- Pindahkan pemeriksaan kepemilikan ke **Policy** (`ProductPolicy`, `OrderPolicy`, `CartItemPolicy`).
- Pindahkan validasi ke **Form Request** (`StoreProductRequest`, `CheckoutRequest`, dan lainnya).
- Ekstrak alur checkout, verify, dan reject ke **Action/Service class** agar bisa dipakai ulang dan dites terpisah.
- Tambahkan **factory** untuk Product, Order, dan model lain (saat ini hanya ada `UserFactory`).
- Bersihkan file statis lama di root setelah dipastikan tidak dibutuhkan.

### Fitur baru

- **Panel admin untuk proyek kelas dan kategori**: CRUD, unggah gambar proyek, publish/unpublish.
- **Notifikasi** lewat email (Mailable + queue `database` yang sudah dikonfigurasi) atau WhatsApp saat pembayaran diverifikasi atau ditolak, dan pengingat sehari sebelum tanggal pickup.
- **QR code pada ticket**, dipindai admin di halaman *Validate pickup*.
- **Laporan rentang tanggal**, rekap per penjual atau per proyek kelas, dan ekspor CSV/Excel.
- **Rekap pendapatan penjual**: total penjualan lunas per penjual untuk pencairan dana (*settlement*).
- **Pengaturan jadwal pickup**: hari/jam operasional, hari libur, dan kuota per hari.
- **Profil toko**: memakai `sellers.description`, logo, dan halaman publik toko.
- **Galeri gambar produk** (beberapa gambar), varian (ukuran/warna), dan ulasan pembeli setelah `picked_up`.
- **Payment gateway** (misalnya virtual account/QRIS) sebagai alternatif transfer manual, dengan webhook yang mengubah status menjadi `paid`.
- **Audit log** untuk aksi admin (verify, reject, nonaktifkan akun, validasi pickup).
- **Lokalisasi**: UI saat ini campuran bahasa Inggris (label) dan Indonesia (pesan). Gunakan file `lang/` agar konsisten.

Setiap fitur atau perubahan perilaku sebaiknya disertai feature test di `tests/Feature/`, mengikuti pola `MarketplaceTest.php`.

---

## 7. Rancangan fitur Customer Service

Saat ini admin membantu pengguna lewat kanal di luar aplikasi (WhatsApp/email), dengan prosedur di [PANDUAN_ADMIN_CS.md](PANDUAN_ADMIN_CS.md). Panduan itu juga menunjukkan banyak kasus yang harus diteruskan ke tim teknis karena belum ada alatnya di UI. Bagian ini merancang fitur CS di dalam aplikasi. CS **memakai peran `admin` yang sama**, tanpa peran baru.

### 7.1 Prioritas 1 — inti

**a. Tiket bantuan (helpdesk)**

- Pembeli dan penjual membuat tiket dari menu **Help** dengan:
  - kategori: Akun, Checkout, Pembayaran, Pickup, Produk, Refund, Lainnya,
  - subjek dan pesan,
  - pesanan terkait (opsional, dipilih dari pesanan milik pengguna),
  - lampiran (JPG/PNG/PDF, maks 5 MB, disimpan di disk `local` yang privat seperti bukti transfer).
- Percakapan berbentuk thread antara pengguna dan admin.
- Status tiket: `open` → `in_progress` → `waiting_user` → `resolved` → `closed`. Tiket `resolved` otomatis menjadi `closed` setelah N hari tanpa balasan.
- Admin melihat antrean tiket, bisa memfilter (status, kategori, peran pelapor), mengambil tiket (*assign to me*), dan mengubah status.

```
support_tickets:  id, code (CS-00000001), user_id, order_id (nullable), category, subject,
                  status, priority, assigned_to (nullable, users.id), resolved_at, closed_at, timestamps
support_messages: id, support_ticket_id, user_id, body, attachment (nullable),
                  is_internal (bool — catatan internal admin, tidak terlihat pengguna), timestamps
```

Route yang diusulkan:

```
GET/POST  /help                          (auth)        daftar & buat tiket milik sendiri
GET       /help/{ticket:code}            (auth, pemilik)
POST      /help/{ticket:code}/messages   (auth, pemilik)
GET       /admin/support                 (role:admin)  antrean
GET       /admin/support/{ticket:code}   (role:admin)
POST      /admin/support/{ticket:code}/messages
PATCH     /admin/support/{ticket:code}   (status, assigned_to, priority)
GET       /admin/support/attachments/{message}   unduh lampiran
```

**b. Pencarian & detail pesanan untuk admin**

Saat ini admin hanya melihat pesanan yang sudah punya bukti transfer (*Payment review*). Diusulkan:

- `GET /admin/orders`: cari berdasarkan kode pesanan, kode ticket, email/nama pembeli, atau status, termasuk pesanan `pending_payment`.
- `GET /admin/orders/{order:order_code}`: item, penjual, status pembayaran, bukti, ticket (kapan dan oleh siapa divalidasi), serta tiket bantuan terkait.

**c. Detail pengguna untuk admin**

`GET /admin/users/{user}` menampilkan profil, status, toko dan proyek (penjual), riwayat pesanan, produk (penjual), dan riwayat tiket bantuan.

**d. Aksi bantuan admin**

Aksi berikut menggantikan eskalasi ke tim teknis:

| Aksi | Catatan implementasi |
|---|---|
| Kirim tautan reset password | Butuh fitur reset password (`Password::sendResetLink`). Admin **tidak pernah** melihat atau mengetik password pengguna. |
| Batalkan pesanan `pending_payment` | Dalam `DB::transaction` + `lockForUpdate`: kembalikan stok, set status `cancelled`, simpan alasan. Pakai logika yang sama dengan `AdminController::reject`, idealnya diekstrak ke Action class. |
| Izinkan unggah ulang bukti | Kembalikan pesanan ke `pending_payment` dan tandai payment lama sebagai `rejected` (perlu mengizinkan beberapa payment per pesanan). |
| Ubah peran buyer → seller | Buat baris `sellers`. Untuk seller → buyer, tolak jika sudah punya produk atau pesanan. |
| Ubah nama toko / proyek kelas penjual | Form sederhana di detail pengguna. |

**e. Notifikasi**

Laravel Notification (kanal `mail` + `database`) dikirim lewat queue `database` yang sudah dikonfigurasi:

- ke pengguna: tiket dibalas, status tiket berubah, pembayaran diverifikasi/ditolak, pengingat pickup;
- ke admin: tiket baru, dan badge jumlah tiket `open` di navigasi.

### 7.2 Prioritas 2 — memperlancar layanan

- **Pusat bantuan / FAQ** (`/help/faq`): jawaban untuk masalah paling umum (lihat tabel di `PANDUAN_ADMIN_CS.md`), ditampilkan sebelum pengguna membuat tiket untuk mengurangi jumlah tiket.
- **Tombol "Butuh bantuan?" kontekstual**: di halaman detail pesanan dan halaman produk penjual. Tombol ini membuka form tiket dengan pesanan atau kategori sudah terisi.
- **Template balasan** (*canned responses*) yang bisa dikelola admin.
- **Catatan internal** pada tiket (`is_internal`) untuk koordinasi antar admin.
- **Pratinjau ticket pickup**: tampilkan pesanan, nama pembeli, dan tanggal pickup **sebelum** konfirmasi. Saat ini *Validate ticket* langsung menandai `picked_up`.
- **Pencatatan refund manual**: tabel `refunds` (order, nominal, rekening tujuan, status, bukti pengembalian, diproses oleh) agar refund di luar aplikasi tetap tercatat.
- **Audit log** semua aksi admin, termasuk aksi CS di atas.

### 7.3 Prioritas 3 — lanjutan

- Dashboard metrik CS: jumlah tiket per kategori, waktu respons pertama, waktu penyelesaian.
- Penilaian kepuasan (1–5) setelah tiket `resolved`.
- Integrasi WhatsApp untuk notifikasi atau pembuatan tiket.
- Live chat. Biasanya belum perlu untuk skala sekolah; tiket + notifikasi sudah memadai.

### 7.4 Catatan implementasi

- Pakai **Policy** (`SupportTicketPolicy`): pemilik hanya bisa melihat tiketnya sendiri, dan admin bisa melihat semua. Pengguna tidak boleh melihat pesan `is_internal`.
- Tiket hanya boleh ditautkan ke pesanan milik pelapor. Untuk penjual, ke pesanan yang berisi produknya.
- Gunakan **Enum** untuk `SupportTicketStatus` dan `SupportTicketCategory`.
- Feature test minimal:
  - membuat tiket,
  - isolasi tiket antar pengguna,
  - pesan internal tidak bocor,
  - pembatalan pesanan oleh admin mengembalikan stok tepat sekali,
  - notifikasi terkirim (`Notification::fake()`).

### 7.5 Analisis: fitur admin yang mempercepat customer service

Analisis ini disusun dari pengecekan langsung halaman dan route admin yang ada (`resources/views/admin/*`, `AdminController`). Tujuannya: setiap keluhan di [PANDUAN_ADMIN_CS.md](PANDUAN_ADMIN_CS.md) yang bertanda **[Tim teknis]** bisa diselesaikan admin sendiri, dan admin bisa melihat konteks keluhan tanpa meminta tangkapan layar dari pengguna.

**Celah pada halaman admin saat ini:**

| Halaman | Celah yang ditemukan | Dampak ke CS |
|---|---|---|
| *Manage accounts* | Tidak ada pencarian atau filter (hanya paginasi 25). Kolom `phone` ada di database tetapi **tidak ditampilkan**. Tidak ada tanggal daftar maupun alasan penonaktifan. | Mencari pengguna berarti membuka halaman satu per satu. Admin tidak bisa langsung menghubungi pengguna. |
| *Payment review* | Tidak ada filter status, bukti hanya bisa **diunduh** (tanpa pratinjau), dan tidak menampilkan item pesanan, tanggal pickup, maupun kontak pembeli. | Setiap verifikasi butuh unduh file dan menebak isi pesanan. |
| *Validate pickup* | Validasi langsung menandai pesanan *Picked up* tanpa pratinjau. | Salah ketik kode ticket milik orang lain bisa langsung menyelesaikan pesanan yang salah. |
| Pesanan | **Tidak ada halaman pesanan untuk admin.** Pesanan `pending_payment` sama sekali tidak terlihat. | Admin buta terhadap pesanan yang belum dibayar, padahal stoknya tertahan. |
| Produk | **Admin tidak punya akses ke produk.** | Produk yang tidak pantas atau salah harga tidak bisa di-unpublish oleh admin. |

**Fitur yang diusulkan**, diurutkan dari dampak terbesar dibanding usahanya:

| # | Fitur | Masalah CS yang diselesaikan | Usaha |
|---|---|---|---|
| 1 | **Pencarian global admin**: satu kotak pencarian untuk email, nama, no. HP, kode pesanan `FX-`, dan kode ticket `FXT-`. | Titik awal setiap keluhan: "saya Budi, pesanan saya…" | Kecil |
| 2 | **Halaman detail pesanan admin** + daftar pesanan dengan filter status, termasuk `pending_payment`. Menampilkan item, pembayaran (pratinjau bukti), ticket (siapa dan kapan validasi), serta **timeline status**. | Hampir semua keluhan pembayaran dan pickup. | Sedang |
| 3 | **Halaman detail pengguna**: profil + no. HP, status, riwayat pesanan, produk (jika penjual), dan catatan internal admin. | Konteks lengkap tanpa bertanya ulang ke pengguna. | Sedang |
| 4 | **Aksi pesanan oleh admin**: batalkan pesanan `pending_payment` (stok kembali), **ubah tanggal pickup**, izinkan unggah ulang bukti. | Kasus "minta batal", "tidak bisa datang tanggal itu", "salah unggah bukti". | Sedang |
| 5 | **Pratinjau sebelum konfirmasi pickup**: tampilkan nama pembeli, item, dan tanggal pickup, lalu tombol *Serahkan barang*. | Mencegah pesanan orang lain ditandai sudah diambil. | Kecil |
| 6 | **Perbaikan Payment review**: filter status, pratinjau gambar/PDF inline, item pesanan, kontak pembeli, dan **template alasan penolakan** (nominal kurang, rekening salah, bukti tidak terbaca). | Verifikasi lebih cepat, alasan penolakan konsisten. | Kecil |
| 7 | **Daftar pickup hari ini** (pesanan `paid` dengan `pickup_date` = hari ini), bisa dicetak per penjual atau kelas. | Persiapan barang dan antrean pengambilan. | Kecil |
| 8 | **Widget "perlu perhatian" di dashboard**: bukti menunggu lebih dari 24 jam, `pending_payment` lebih dari 48 jam (stok tertahan), pickup yang terlewat, dan akun yang baru dinonaktifkan. | CS proaktif sebelum pengguna mengeluh. | Kecil |
| 9 | **Bantuan akun**: kirim tautan reset password, ubah peran buyer↔seller (dengan pengecekan), ubah nama toko atau proyek, dan **alasan wajib** saat menonaktifkan akun. | Semua kasus akun yang sekarang harus ke tim teknis. | Sedang |
| 10 | **Moderasi produk**: daftar semua produk, unpublish, dan ubah stok dengan alasan. | Keluhan produk salah, habis, atau tidak pantas. | Kecil |
| 11 | **Terbitkan ulang kode ticket**: kode lama dibatalkan, misalnya bila tangkapan layar ticket tersebar. | Keamanan pickup. | Kecil |
| 12 | **Pengumuman/banner** yang diatur admin, misalnya "Pickup libur tanggal…" atau "Verifikasi pembayaran maks. 1×24 jam". | Mengurangi pertanyaan berulang. | Kecil |
| 13 | **Audit log** semua aksi admin di atas: siapa, kapan, apa, dan alasannya. | Akuntabilitas, terutama untuk uang dan pembatalan. | Sedang |
| 14 | **Pembagian hak akses admin** (CS / keuangan / pickup) memakai Gate atau Policy, tetap di peran `admin` dengan kolom kemampuan tambahan. | Saat jumlah admin bertambah. | Sedang |

Saran urutan pengerjaan:

- **Tahap 1 (cepat, dampak besar):** nomor 1, 5, 6, 7, 8. Sebagian besar hanya query dan view baru, tanpa perubahan alur bisnis.
- **Tahap 2:** nomor 2, 3, 4, 9, 13. Tahap ini menghilangkan hampir semua eskalasi **[Tim teknis]**.
- **Tahap 3:** tiket bantuan (7.1a), lalu nomor 10–12 dan 14.

Setiap aksi yang mengubah pesanan (batal, ubah tanggal, unggah ulang) harus berjalan dalam `DB::transaction` dengan pengembalian stok **tepat sekali**, dan wajib disertai feature test serta skenario e2e (lihat bagian 9).

---

## 8. SQLite untuk konkurensi tinggi

Banyak aplikasi web melayani ribuan pengguna bersamaan dengan SQLite. Kuncinya ada pada cara aplikasi **menulis**: SQLite tetap hanya mengizinkan **satu penulis pada satu waktu**, tetapi bila setiap penulisan sangat singkat dan antre dengan benar (bukan gagal), satu penulis bisa melayani ribuan transaksi kecil per detik.

### 8.1 Bedakan "pengguna bersamaan" dan "penulisan bersamaan"

- **Ribuan pengguna bersamaan** sebagian besar sedang membaca: membuka beranda, melihat produk. Dengan WAL, pembaca tidak pernah memblokir penulis, dan penulis tidak memblokir pembaca.
- **Penulisan yang benar-benar bersamaan** jauh lebih sedikit. Misalnya 2.000 pengguna aktif yang masing-masing checkout sekali dalam 10 menit hanya menghasilkan sekitar 3–4 checkout per detik.
- Transaksi checkout aplikasi ini berlangsung **sekitar 3–5 ms** (terukur dengan WAL). Jadi satu penulis secara teori menampung lebih dari 200 checkout per detik **asal transaksi mengantre, bukan saling membatalkan**.

Masalah "locked" di aplikasi ini **bukan karena SQLite kehabisan kapasitas**. Penyebabnya transaksi `DEFERRED` yang saling bertabrakan. Penjelasan dan hasil pengukurannya ada di [PANDUAN_DEVOPS.md bagian 7](PANDUAN_DEVOPS.md#7-catatan-khusus-sqlite-transaksi-bersamaan).

### 8.2 Teknik yang dipakai aplikasi SQLite berkonkurensi tinggi

| # | Teknik | Mengapa | Status di aplikasi ini |
|---|---|---|---|
| 1 | **`BEGIN IMMEDIATE`** untuk transaksi tulis | Kunci tulis diambil di awal, sehingga transaksi lain **menunggu** (busy_timeout) alih-alih deadlock. Rails 8 menjadikan ini bawaan untuk SQLite. | **Belum.** Masih `DEFERRED`. |
| 2 | **WAL** (`journal_mode=wal`) | Pembaca dan penulis berjalan paralel, dan commit jauh lebih cepat. | **Belum.** |
| 3 | **`busy_timeout`** yang wajar (3–10 dtk) | Transaksi yang antre menunggu, bukan langsung error. | Bawaan PDO 60 dtk (terlalu lama untuk request web). |
| 4 | **`synchronous=NORMAL`** (bersama WAL) | fsync hanya saat checkpoint, sehingga commit jauh lebih cepat. Tetap aman jika aplikasi crash; hanya transaksi terakhir yang bisa hilang jika **listrik/OS mati**. | **Belum.** |
| 5 | **Transaksi tulis sesingkat mungkin** | Lama kunci tulis menentukan kapasitas. Siapkan data dan validasi **sebelum** `DB::transaction`. Jangan upload file, kirim email, atau memanggil API di dalam transaksi. | Sebagian besar sudah. `uploadPayment` menyimpan file di dalam transaksi, jadi sebaiknya dipindah ke luar. |
| 6 | **Pisahkan beban tulis yang tidak penting** | Session (ditulis **setiap request**), cache, dan queue memakai database yang sama dengan pesanan, sehingga semuanya berebut satu kunci tulis. Pindahkan ke file SQLite terpisah atau driver lain. | **Belum.** Ketiganya `database`. |
| 7 | **Retry dengan jeda** sebagai jaring pengaman | Dengan IMMEDIATE, kegagalan hanya terjadi jika antrean melebihi busy_timeout. `DB::transaction($callback, 3)` mengulang otomatis untuk error `database is locked`. | Belum (1 percobaan). |
| 8 | **Index yang tepat** | Query di dalam transaksi tulis harus cepat. Full table scan memperpanjang kunci. | Sudah cukup (`cart_items` unik per user-produk, `orders.status`, `products.status`). |
| 9 | **Checkpoint WAL terkendali** | Pembaca yang sangat lama mencegah checkpoint, sehingga file `-wal` membengkak. Batasi dengan `journal_size_limit`, dan jalankan `PRAGMA wal_checkpoint(TRUNCATE)` lewat scheduler bila perlu. | — |
| 10 | **Tuning cache dan memori** | `cache_size`, `mmap_size`, dan `temp_store=memory` mengurangi I/O disk. | — |
| 11 | **Disk lokal SSD/NVMe**, satu server | Penguncian SQLite bergantung pada filesystem lokal. Jangan dipakai di NFS/SMB. | Lihat PANDUAN_DEVOPS. |
| 12 | **Backup kontinu** (misalnya Litestream ke object storage) | Menutup kelemahan "satu file di satu server". | Belum (backup harian di PANDUAN_DEVOPS). |

### 8.3 Konfigurasi yang diusulkan

`config/database.php`. Connector SQLite Laravel 13 mendukung `busy_timeout`, `journal_mode`, `synchronous`, `transaction_mode`, dan `pragmas`:

```php
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    'busy_timeout' => env('DB_BUSY_TIMEOUT', 5000),
    'journal_mode' => env('DB_JOURNAL_MODE', 'wal'),
    'synchronous' => env('DB_SYNCHRONOUS', 'normal'),
    'transaction_mode' => env('DB_TRANSACTION_MODE', 'IMMEDIATE'),
    'pragmas' => [
        'cache_size' => -20000,          // ~20 MB page cache per koneksi
        'temp_store' => 'memory',
        'mmap_size' => 134217728,        // 128 MB
        'journal_size_limit' => 67108864, // batasi file -wal ke 64 MB setelah checkpoint
    ],
],

// Database terpisah untuk beban tulis "ramai tapi tidak kritis"
'sqlite_runtime' => [
    'driver' => 'sqlite',
    'database' => env('DB_RUNTIME_DATABASE', database_path('runtime.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => false,
    'busy_timeout' => 5000,
    'journal_mode' => 'wal',
    'synchronous' => 'normal',
    'transaction_mode' => 'IMMEDIATE',
],
```

Lalu di `.env`, arahkan session, cache, dan queue ke database terpisah. Variabel-variabel ini sudah didukung oleh `config/session.php`, `config/cache.php`, dan `config/queue.php`:

```env
SESSION_CONNECTION=sqlite_runtime
DB_CACHE_CONNECTION=sqlite_runtime
DB_QUEUE_CONNECTION=sqlite_runtime
DB_RUNTIME_DATABASE=/var/www/fortexiv/shared/database/runtime.sqlite
```

Tabel `sessions`, `cache`, dan `jobs` harus dibuat di `runtime.sqlite` (misalnya migrasi terpisah dengan `--database=sqlite_runtime`, atau `--path` khusus). Alternatif yang lebih sederhana adalah `SESSION_DRIVER=file`, yang tidak memakai kunci database sama sekali.

Perubahan kode pendukung:

- `MarketplaceController::uploadPayment`: simpan file **sebelum** `DB::transaction`, dan hapus file itu jika transaksi gagal.
- Transaksi kritis (checkout, verifikasi, pickup): `DB::transaction(fn () => ..., 3)` sebagai jaring pengaman.
- `lockForUpdate()` biarkan saja. Di SQLite tidak berpengaruh, tetapi langsung berfungsi jika kelak pindah ke MySQL/PostgreSQL.

### 8.4 Hasil yang diharapkan dan cara memverifikasi

Dari pengukuran (PANDUAN_DEVOPS 7.2), `IMMEDIATE` + WAL + busy_timeout menghasilkan **0 kegagalan** pada 20 proses checkout tanpa jeda maupun pada ~40 checkout/dtk + ~200 request/dtk, dengan p95 di bawah 200 ms. Sebagai perbandingan, konfigurasi saat ini gagal 3–16%.

Setelah menerapkan perubahan, verifikasi dengan:

1. Feature test yang sudah ada dan e2e test (bagian 9) tetap lulus.
2. Uji beban di server staging, misalnya dengan [k6](https://k6.io/) atau `ab`. Skenarionya login, tambah ke keranjang, lalu checkout dari banyak pengguna sekaligus. Pastikan tidak ada `database is locked` di `storage/logs`.
3. Pantau ukuran `database.sqlite-wal` dan latensi p95 di production.

### 8.5 Batas wajar SQLite

SQLite tetap pilihan tepat selama:

- aplikasi berjalan di **satu server**;
- beban tulis terus-menerus di bawah sekitar ratusan transaksi kecil per detik;
- tidak ada transaksi tulis panjang (impor/ekspor besar sebaiknya dibagi per batch kecil).

Jika salah satu syarat itu terlampaui, pindah ke MySQL/PostgreSQL. Lihat [PANDUAN_DEVOPS.md 7.4](PANDUAN_DEVOPS.md#74-kapan-pindah-ke-mysqlmariadbpostgresql).

---

## 9. E2E test (Playwright)

E2E test berada di folder `e2e/`, **terpisah dari aplikasi dan di-ignore oleh git** (`/e2e/` di `.gitignore`). Test berjalan sepenuhnya terisolasi:

- server PHP sendiri di port **8001** (server dev di 8000 tidak terganggu),
- database `e2e/.data/e2e.sqlite`, dibuat ulang setiap kali test dijalankan,
- folder storage `e2e/.data/storage` (lewat `LARAVEL_STORAGE_PATH`), sehingga bukti transfer uji tidak tercampur dengan storage dev,
- rekening transfer uji (`PAYMENT_*`) di-set otomatis,
- request ke Google Fonts diblokir, sehingga test tidak bergantung pada internet.

### Menjalankan

```powershell
cd e2e
npm install
npx playwright install chromium   # sekali saja
npx playwright test               # semua test, headless
npx playwright test --headed      # lihat browser-nya
npx playwright test --ui          # mode interaktif
npx playwright show-report        # laporan HTML + trace kegagalan
```

PHP yang dipakai: `E2E_PHP`, lalu `C:/xampp/php84/php.exe` jika ada, lalu `php`. Aplikasi butuh PHP ≥ 8.4.1.

### Struktur

| File | Isi |
|---|---|
| `playwright.config.ts` | 1 worker (server PHP bawaan single-thread, data dipakai bersama), trace dan screenshot saat gagal. |
| `support/serve.mjs` | Reset `.data`, seed, lalu jalankan `php -S` dengan env e2e. |
| `support/seed.php` | `migrate:fresh --seed`, lalu membuat akun uji dan produk uji. Menolak berjalan jika `DB_DATABASE` bukan database e2e. |
| `support/helpers.ts` | `login`, `pageAs` (konteks browser per peran), `stockOf`, `row`, dan file PNG bukti transfer. |
| `tests/01-guest.spec.ts` | Beranda, pencarian/filter, detail produk & proyek, 404 draft, redirect halaman terlindungi. |
| `tests/02-auth.spec.ts` | Daftar pembeli/penjual, email duplikat, login gagal, redirect per peran, 403 antar peran. |
| `tests/03-seller.spec.ts` | Tambah draft → publish → tampil, edit, publish stok 0 ditolak (422), hapus, validasi server. |
| `tests/04-purchase-flow.spec.ts` | Alur penuh: keranjang → checkout → bukti → verify → ticket → pickup → ticket sekali pakai → laporan. |
| `tests/05-payment-rejection.spec.ts` | Reject dengan alasan → pesanan batal → stok kembali. |
| `tests/06-admin-accounts.spec.ts` | Disable/enable akun, admin tidak muncul di daftar, navigasi dashboard. |

Akun uji (password `Password123!`): `admin@e2e.test`, `seller@e2e.test` (Toko Kelas 1), `buyer@e2e.test`, `buyer2@e2e.test`.

### Bug yang ditemukan e2e test

Saat validasi pickup, `AdminController::pickup` mem-flash **model Eloquent** ke session. Karena session Laravel 13 diserialisasi sebagai JSON (`config/session.php` → `serialization: json`), model berubah menjadi array, dan halaman *Validate pickup* error 500 **setelah** pesanan sudah ditandai diambil. Feature test tidak menangkapnya karena memakai `SESSION_DRIVER=array`.

Sudah diperbaiki: controller kini mem-flash `['order_code', 'buyer_name']`. `MarketplaceTest` juga diberi assertion regresi. **Pelajaran:** jangan menyimpan objek ke session/flash, simpan nilai skalar saja.
