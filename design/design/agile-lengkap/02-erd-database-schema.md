# ERD & Database Schema — Aplikasi Contoh
### Versi Lengkap, siap dijadikan migration Laravel

Dikunci di **Sprint 0** (lihat `00-panduan-metode-agile.md` Bagian 9) — perubahan struktur tabel setelah Sprint 1 dimulai sebaiknya diumumkan ke seluruh tim dulu, sama seperti Aturan Kelas di modul MarketDay.

---

## 1. Diagram Relasi (ERD)

```
users (1)───(1) sellers
  │
  │ (1)───(banyak) products ───(banyak)┐
  │                                     │
  │ (1)───(banyak) orders               │
  │                  │                  │
  │                  │(1)──(banyak) order_items ──(banyak)─┘
  │                  │
  │                  │(1)──(1) payments
  │                  │
  │                  │(1)──(1) tickets
  │
  └── categories (1)───(banyak) products
```

**Cara baca:** satu `user` dengan role `seller` punya satu baris `sellers` (profil toko). Satu `seller` punya banyak `products`. Satu `user` dengan role `buyer` punya banyak `orders`. Satu `order` punya banyak `order_items` (karena satu pesanan bisa berisi banyak produk, bahkan dari seller berbeda), dan tepat satu `payments` serta satu `tickets`.

---

## 2. Definisi Tabel

### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK, auto increment | |
| name | VARCHAR(100) | |
| email | VARCHAR(100), UNIQUE | |
| password | VARCHAR(255) | disimpan hasil hash (`bcrypt`), bukan teks asli |
| phone | VARCHAR(30), nullable | |
| role | ENUM('admin','seller','buyer') | default `buyer` |
| is_active | BOOLEAN | default `true`; admin bisa nonaktifkan akun (US-19) tanpa menghapus riwayat |
| created_at, updated_at | TIMESTAMP | |

### `sellers`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| user_id | BIGINT, FK → users.id, UNIQUE | relasi 1-1, hanya user berperan `seller` |
| store_name | VARCHAR(100) | |
| description | VARCHAR(255), nullable | |
| created_at, updated_at | TIMESTAMP | |

### `categories`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| name | VARCHAR(50), UNIQUE | |

### `products`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| seller_id | BIGINT, FK → sellers.id | |
| category_id | BIGINT, FK → categories.id, nullable | |
| name | VARCHAR(100) | |
| slug | VARCHAR(120), UNIQUE | untuk URL produk yang rapi |
| description | TEXT, nullable | |
| image | VARCHAR(255), nullable | path file gambar |
| price | INTEGER (rupiah, tanpa desimal) | |
| stock | INTEGER | default 0 |
| status | ENUM('draft','published','archived') | default `draft`; hanya `published` & stock > 0 tampil ke buyer |
| created_at, updated_at | TIMESTAMP | |

### `orders`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_code | VARCHAR(20), UNIQUE | contoh: `ORD-00001`, dibuat setelah baris tersimpan (pola sama seperti `kode_pesanan` di modul MarketDay) |
| buyer_id | BIGINT, FK → users.id | |
| status | ENUM('pending_payment','waiting_verification','paid','cancelled','picked_up') | default `pending_payment` |
| total_price | INTEGER | jumlah semua `order_items.subtotal` |
| pickup_date | DATE | tanggal hari-H yang ditentukan |
| created_at, updated_at | TIMESTAMP | |

### `order_items`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_id | BIGINT, FK → orders.id | |
| product_id | BIGINT, FK → products.id | |
| seller_id | BIGINT, FK → sellers.id | disalin dari produk, memudahkan laporan per seller tanpa join berlapis |
| quantity | INTEGER | |
| price_at_order | INTEGER | **harga disalin saat order dibuat** — kalau seller mengubah harga produk nanti, riwayat order lama tidak ikut berubah |
| subtotal | INTEGER | `quantity * price_at_order` |

### `payments`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_id | BIGINT, FK → orders.id, UNIQUE | |
| proof_image | VARCHAR(255) | path bukti transfer yang diunggah buyer |
| method | VARCHAR(50), nullable | contoh: "Transfer BCA", "QRIS" |
| status | ENUM('pending','verified','rejected') | default `pending` |
| verified_by | BIGINT, FK → users.id, nullable | admin yang memverifikasi |
| verified_at | TIMESTAMP, nullable | |
| notes | VARCHAR(255), nullable | alasan penolakan, kalau ada |

### `tickets`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT, PK | |
| order_id | BIGINT, FK → orders.id, UNIQUE | |
| ticket_code | VARCHAR(30), UNIQUE | kode unik untuk validasi pickup |
| issued_at | TIMESTAMP | diisi otomatis saat `payments.status` berubah jadi `verified` |
| picked_up_at | TIMESTAMP, nullable | |
| picked_up_by | BIGINT, FK → users.id, nullable | admin/seller yang memvalidasi saat hari-H |

---

## 3. Catatan Desain Penting

- **Stok dikurangi saat checkout (order dibuat), bukan saat admin memverifikasi pembayaran** — supaya dua buyer tidak sama-sama "berhasil" memesan stok terakhir sambil menunggu verifikasi. Kalau pembayaran ditolak (`payments.status = rejected`), stok produk **dikembalikan** dan `orders.status` menjadi `cancelled`.
- **`price_at_order` wajib disalin ke `order_items`** — jangan mengambil harga dengan join ke `products.price` saat menampilkan riwayat order, karena harga produk bisa berubah kapan saja.
- **`ticket_code` hanya boleh dipakai sekali** — begitu `picked_up_at` terisi, sistem menolak validasi ulang dengan kode yang sama (cegah barang diambil dua kali).
- **`order_items.seller_id` sengaja didenormalisasi** (disalin dari `products.seller_id`) supaya query laporan per-seller (US-11, US-20) tidak perlu join tiga tabel.
- Satu `orders` bisa berisi produk dari **beberapa seller berbeda** — saat pickup, seller hanya perlu melihat baris `order_items` miliknya sendiri, bukan seluruh isi order.

## 4. Contoh Migration Laravel (kerangka)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('email', 100)->unique();
    $table->string('password');
    $table->string('phone', 30)->nullable();
    $table->enum('role', ['admin', 'seller', 'buyer'])->default('buyer');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_code', 20)->unique();
    $table->foreignId('buyer_id')->constrained('users');
    $table->enum('status', ['pending_payment', 'waiting_verification', 'paid', 'cancelled', 'picked_up'])
          ->default('pending_payment');
    $table->unsignedInteger('total_price');
    $table->date('pickup_date');
    $table->timestamps();
});
```

Tabel lain (`sellers`, `categories`, `products`, `order_items`, `payments`, `tickets`) mengikuti pola yang sama — ubah nama kolom & tipe sesuai definisi Bagian 2.
