# Spesifikasi Teknis: Route & Controller — Aplikasi Contoh
### Versi Lengkap

> **Catatan pendekatan:** Aplikasi Contoh dibangun sebagai aplikasi Laravel + Blade (monolith, seperti pola di modul Fase 5), bukan aplikasi SPA yang butuh REST API terpisah. Karena itu, "API spec" di sini berbentuk **peta route & controller** — kontrak yang sama fungsinya dengan API spec (menentukan siapa memanggil apa, dengan input/output apa), tapi mengikuti cara kerja Laravel: route → controller → Blade view. Kalau di masa depan tim menambah aplikasi mobile terpisah, tabel ini bisa langsung diadaptasi jadi endpoint JSON (tambahkan kolom "Response JSON").

Middleware peran: `auth` (harus login), `role:admin`, `role:seller`, `role:buyer` — dipasang lewat Middleware/Policy Laravel (lihat modul Fase 5, Pertemuan 6).

---

## 1. Autentikasi (Epic 1)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/register` | guest | `Auth\RegisterController@create` | Tampilkan form daftar |
| POST | `/register` | guest | `Auth\RegisterController@store` | Simpan user baru (role default `buyer`) |
| GET | `/login` | guest | `Auth\LoginController@create` | Tampilkan form login |
| POST | `/login` | guest | `Auth\LoginController@store` | Autentikasi, redirect sesuai role |
| POST | `/logout` | auth | `Auth\LoginController@destroy` | Akhiri sesi |

## 2. Katalog Produk (Epic 2 — publik/buyer)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/products` | — | `ProductController@index` | Daftar produk `published` & stok > 0; support query `?category=&search=` |
| GET | `/products/{slug}` | — | `ProductController@show` | Detail satu produk |

## 3. Manajemen Produk (Epic 3 — seller)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/seller/products` | auth, role:seller | `Seller\ProductController@index` | Daftar produk milik seller yang login |
| GET | `/seller/products/create` | auth, role:seller | `Seller\ProductController@create` | Form tambah produk |
| POST | `/seller/products` | auth, role:seller | `Seller\ProductController@store` | Simpan produk baru (status awal `draft`) |
| GET | `/seller/products/{id}/edit` | auth, role:seller, **Policy: pemilik produk** | `Seller\ProductController@edit` | Form edit — hanya pemilik produk |
| PUT | `/seller/products/{id}` | auth, role:seller, Policy | `Seller\ProductController@update` | Update info/harga/stok |
| PATCH | `/seller/products/{id}/publish` | auth, role:seller, Policy | `Seller\ProductController@publish` | Ubah status `draft` → `published` |
| DELETE | `/seller/products/{id}` | auth, role:seller, Policy | `Seller\ProductController@destroy` | Hapus/arsipkan produk |

## 4. Keranjang (Epic 4 — buyer)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/cart` | auth, role:buyer | `CartController@index` | Tampilkan isi keranjang |
| POST | `/cart` | auth, role:buyer | `CartController@store` | Tambah produk ke keranjang (`product_id`, `quantity`) |
| PATCH | `/cart/{id}` | auth, role:buyer, Policy | `CartController@update` | Ubah jumlah item |
| DELETE | `/cart/{id}` | auth, role:buyer, Policy | `CartController@destroy` | Hapus item dari keranjang |

## 5. Order & Pembayaran (Epic 5 — buyer & admin)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| POST | `/checkout` | auth, role:buyer | `OrderController@store` | Buat order dari isi keranjang; kurangi stok; status `pending_payment` |
| GET | `/orders` | auth, role:buyer | `OrderController@index` | Riwayat & status pesanan milik buyer sendiri |
| GET | `/orders/{order_code}` | auth, role:buyer, Policy | `OrderController@show` | Detail satu order |
| POST | `/orders/{id}/payment-proof` | auth, role:buyer, Policy | `PaymentController@store` | Upload bukti transfer; status order → `waiting_verification` |
| GET | `/admin/payments` | auth, role:admin | `Admin\PaymentController@index` | Daftar pembayaran menunggu verifikasi |
| POST | `/admin/payments/{id}/verify` | auth, role:admin | `Admin\PaymentController@verify` | Set `verified`; picu penerbitan ticket; order → `paid` |
| POST | `/admin/payments/{id}/reject` | auth, role:admin | `Admin\PaymentController@reject` | Set `rejected`; kembalikan stok; order → `cancelled` |

## 6. Digital Ticket (Epic 6 — buyer)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/orders/{order_code}/ticket` | auth, role:buyer, Policy | `TicketController@show` | Tampilkan/unduh ticket (kode unik, daftar produk, tanggal pickup) |

## 7. Pickup (Epic 7 — admin/seller)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/admin/pickup` | auth, role:admin\|seller | `PickupController@index` | Form input/cari `ticket_code` |
| POST | `/admin/pickup/validate` | auth, role:admin\|seller | `PickupController@validate` | Cek `ticket_code`; kalau valid & belum dipakai → set `picked_up_at`, `picked_up_by`; order → `picked_up` |

## 8. Admin & Laporan (Epic 8)

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| GET | `/admin/users` | auth, role:admin | `Admin\UserController@index` | Daftar seller & buyer |
| PATCH | `/admin/users/{id}/toggle-active` | auth, role:admin | `Admin\UserController@toggleActive` | Aktif/nonaktifkan akun |
| GET | `/admin/reports/daily` | auth, role:admin | `Admin\ReportController@daily` | Laporan harian: total order, pembayaran sukses/batal, produk terjual, revenue, status pickup — filter `?date=` |

---

## 9. Aturan Otorisasi Ringkas

| Siapa | Boleh akses |
|---|---|
| **Publik (belum login)** | `/products`, `/products/{slug}`, `/login`, `/register` |
| **Buyer** | Semua di atas + `/cart`, `/checkout`, `/orders/*` miliknya sendiri |
| **Seller** | `/seller/products/*` miliknya sendiri saja (ditegakkan lewat Policy, bukan hanya middleware role) |
| **Admin** | Semua route `/admin/*` |

> ⚠️ Middleware `role:seller` saja **tidak cukup** untuk mencegah seller A mengedit produk seller B — itu tugas **Policy** (`ProductPolicy@update`), dicek di controller tiap route yang membawa `{id}` milik seller. Lihat modul Fase 5, Pertemuan 6, untuk pola Policy di Laravel.
