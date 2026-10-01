# Spesifikasi Teknis: Route & Controller — Template
### Isi bagian [ISI DI SINI] dengan proyek kalian sendiri

> Kalau aplikasi kalian dibangun dengan Laravel + Blade (monolith, seperti pola Fase 5), dokumen ini berbentuk **peta route & controller** — bukan REST API dengan JSON. Kalau kalian membangun frontend terpisah (SPA/mobile), tambahkan kolom "Response JSON" di tiap tabel.

---

## Cara Mengisi

Untuk tiap Epic di backlog kalian (`01-product-backlog-prd-template.md`), daftar route yang dibutuhkan:
- **Method**: GET (menampilkan/mengambil data) atau POST/PUT/PATCH/DELETE (mengubah data)
- **Route**: URL-nya
- **Middleware**: siapa yang boleh akses (`auth`, `role:seller`, dsb.)
- **Controller@method**: nama controller & fungsi yang menangani
- **Deskripsi**: apa yang terjadi

**CONTOH satu baris (dari Aplikasi Contoh, Epic Order & Pembayaran):**

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| POST | `/checkout` | auth, role:buyer | `OrderController@store` | Buat order dari isi keranjang; kurangi stok; status awal "menunggu pembayaran" |

---

## 📝 ISI DI SINI — Route Proyek Kalian

### Epic: _______

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| | | | | |
| | | | | |
| | | | | |

### Epic: _______

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| | | | | |
| | | | | |

### Epic: _______

| Method | Route | Middleware | Controller@method | Deskripsi |
|---|---|---|---|---|
| | | | | |
| | | | | |

*(tambahkan tabel per Epic sebanyak yang dibutuhkan — samakan urutan dengan Epic di PRD)*

---

## Aturan Otorisasi Ringkas

**CONTOH:**

| Siapa | Boleh akses |
|---|---|
| Publik | `/products`, `/login`, `/register` |
| Buyer | + `/cart`, `/checkout`, `/orders/*` miliknya sendiri |
| Seller | `/seller/products/*` miliknya sendiri saja |
| Admin | Semua route `/admin/*` |

> ⚠️ Ingat: middleware role saja **tidak cukup** untuk mencegah, misalnya, seller A mengedit produk seller B. Itu tugas **Policy** — dicek di controller untuk tiap route yang membawa `{id}` milik user tertentu.

> 📝 **ISI DI SINI:**

| Siapa | Boleh akses |
|---|---|
| Publik | |
| _______ | |
| _______ | |
| Admin | |

---

## Lampiran

Peta route lengkap Aplikasi Contoh (8 Epic, semua endpoint) ada di `agile-lengkap/03-api-technical-spec.md`.
