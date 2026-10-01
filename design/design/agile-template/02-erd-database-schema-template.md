# ERD & Database Schema — Template
### Isi bagian [ISI DI SINI] dengan proyek kalian sendiri

> Kunci dokumen ini **sebelum** mulai coding fitur (idealnya di Sprint 0). Mengubah struktur tabel di tengah sprint itu mahal — banyak kode sudah bergantung padanya. Kalau terpaksa berubah, umumkan ke seluruh tim dulu.

---

## Cara Mengisi

1. Daftar dulu semua "benda"/konsep utama di aplikasi kalian (di Aplikasi Contoh: user, produk, order, pembayaran, ticket...).
2. Untuk tiap benda, tentukan jadi satu tabel, lalu daftar kolom apa saja yang dibutuhkan.
3. Gambar relasi antar tabel (1-ke-1, 1-ke-banyak, banyak-ke-banyak).
4. Cek ulang: ada tempat di mana harga/jumlah bisa "berubah sendiri" tanpa disadari? (contoh: kalau harga produk diubah seller, apakah riwayat order lama ikut berubah? — biasanya jawabannya harus TIDAK, lihat catatan desain di contoh.)

---

## CONTOH

### Diagram Relasi

```
users (1)───(1) sellers
  │
  │ (1)───(banyak) products
  │
  │ (1)───(banyak) orders ──(1)───(banyak) order_items ──(banyak)─ products
  │                  │
  │                  ├──(1)──(1) payments
  │                  └──(1)──(1) tickets
```

### Definisi Tabel (contoh: `orders` dan `order_items`)

**`orders`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_code | VARCHAR(20), UNIQUE | contoh: ORD-00001 |
| buyer_id | BIGINT, FK → users.id | |
| status | ENUM(...) | pending_payment / waiting_verification / paid / cancelled / picked_up |
| total_price | INTEGER | |
| pickup_date | DATE | |

**`order_items`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_id | BIGINT, FK → orders.id | |
| product_id | BIGINT, FK → products.id | |
| quantity | INTEGER | |
| price_at_order | INTEGER | **disalin saat order dibuat** — supaya perubahan harga produk nanti tidak mengubah riwayat order lama |

### Catatan Desain (contoh)

- Stok dikurangi **saat order dibuat**, bukan saat pembayaran diverifikasi — supaya dua buyer tidak sama-sama dapat stok terakhir yang sama.
- Harga disalin ke `order_items.price_at_order` — jangan ambil harga dari tabel produk saat menampilkan riwayat.

---

## 📝 ISI DI SINI — Proyek Kalian

### Diagram Relasi

```
(gambar/tulis relasi antar tabel kalian di sini)
```

### Daftar Tabel

**Tabel: `_______`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | | |
| | | |
| | | |

**Tabel: `_______`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | | |
| | | |
| | | |

**Tabel: `_______`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | | |
| | | |
| | | |

*(tambahkan tabel sebanyak yang dibutuhkan)*

### Catatan Desain Proyek Kalian

Pertanyaan pemandu — jawab tiap satu:
- Data apa yang **tidak boleh berubah** walau data aslinya berubah nanti (seperti harga di contoh)? `_______`
- Ada proses "rebutan sumber daya terbatas" seperti stok? Kapan pengurangannya terjadi? `_______`
- Kolom mana yang berisi data sensitif dan perlu perhatian ekstra (password, bukti pembayaran, dsb.)? `_______`

---

## Lampiran

Versi lengkap skema Aplikasi Contoh (semua 7 tabel + kerangka migration Laravel) ada di `agile-lengkap/02-erd-database-schema.md` — buka kalau butuh contoh lebih detail.
