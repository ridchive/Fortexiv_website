# Sitemap & Wireframe — Aplikasi Contoh
### Versi Lengkap

Acuan halaman sebelum menulis Blade view. Dikunci bersamaan dengan ERD di **Sprint 0** (lihat `00-panduan-metode-agile.md`).

---

## 1. Sitemap per Peran

```
PUBLIK (belum login)
├── / (Beranda → redirect ke /products)
├── /products                    Katalog produk
├── /products/{slug}             Detail produk
├── /login
└── /register

BUYER (setelah login)
├── /cart                        Keranjang
├── /checkout                    Konfirmasi pesanan sebelum submit
├── /orders                      Riwayat & status pesanan saya
├── /orders/{order_code}         Detail satu pesanan
│     └── payment-proof (upload) — bagian dari halaman detail, bukan halaman terpisah
└── /orders/{order_code}/ticket  Digital ticket (lihat/unduh)

SELLER (setelah login)
├── /seller (dashboard)          Ringkasan: jumlah produk, order masuk
├── /seller/products             Daftar produk saya
├── /seller/products/create      Tambah produk
└── /seller/products/{id}/edit   Edit produk

ADMIN (setelah login)
├── /admin (dashboard)           Ringkasan cepat: order menunggu verifikasi, laporan hari ini
├── /admin/users                 Kelola akun seller & buyer
├── /admin/payments              Verifikasi bukti transfer
├── /admin/pickup                Validasi ticket saat hari-H
└── /admin/reports/daily         Laporan harian
```

---

## 2. Wireframe Halaman Kunci

### 2.1 Katalog Produk (`/products`)

```
┌──────────────────────────────────────────────────┐
│ Toko Online        [Cari produk...] [🔍]  [Login] │
├──────────────────────────────────────────────────┤
│ Kategori: [Semua ▾]                               │
├───────────────┬───────────────┬───────────────────┤
│ [gambar]      │ [gambar]      │ [gambar]           │
│ Nama Produk   │ Nama Produk   │ Nama Produk        │
│ Rp 15.000     │ Rp 20.000     │ Rp 10.000          │
│ oleh: Toko A  │ oleh: Toko B  │ oleh: Toko A       │
├───────────────┴───────────────┴───────────────────┤
│                  [ 1  2  3  → ]  (pagination)      │
└──────────────────────────────────────────────────┘
```

### 2.2 Detail Produk (`/products/{slug}`)

```
┌──────────────────────────────────────────────────┐
│ [ ← Kembali ]                                     │
├───────────────┬────────────────────────────────────┤
│               │ Nama Produk                        │
│   [gambar     │ oleh: Toko A                        │
│    besar]     │ Rp 15.000                           │
│               │ Stok tersisa: 12                    │
│               │                                      │
│               │ Deskripsi produk lengkap di sini... │
│               │                                      │
│               │ Jumlah: [ - ] 1 [ + ]                │
│               │ [ Tambah ke Keranjang ]              │
└───────────────┴────────────────────────────────────┘
```

### 2.3 Keranjang (`/cart`)

```
┌──────────────────────────────────────────────────┐
│ Keranjang Saya                                    │
├──────────────────────────────────────────────────┤
│ [x] Nama Produk A   Rp 15.000  Jumlah:[-]2[+]  30.000 │
│ [x] Nama Produk B   Rp 20.000  Jumlah:[-]1[+]  20.000 │
├──────────────────────────────────────────────────┤
│                                   Total: Rp 50.000 │
│                          [ Lanjut ke Checkout ]    │
└──────────────────────────────────────────────────┘
```

### 2.4 Detail Order + Upload Bukti (`/orders/{order_code}`)

```
┌──────────────────────────────────────────────────┐
│ Pesanan #ORD-00042          Status: Menunggu Bayar │
├──────────────────────────────────────────────────┤
│ Nama Produk A  x2                       Rp 30.000 │
│ Nama Produk B  x1                       Rp 20.000 │
├──────────────────────────────────────────────────┤
│ Total: Rp 50.000     Tanggal Pickup: 12 Okt 2026  │
├──────────────────────────────────────────────────┤
│ Upload Bukti Transfer:                            │
│ [ Pilih File ]  belum ada file dipilih            │
│ [ Kirim Bukti Pembayaran ]                        │
└──────────────────────────────────────────────────┘
```

### 2.5 Digital Ticket (`/orders/{order_code}/ticket`)

```
┌──────────────────────────────────────────────────┐
│                 🎟  DIGITAL TICKET                 │
├──────────────────────────────────────────────────┤
│  Kode Ticket : ORD-00042-TKT                       │
│  Atas Nama   : (nama buyer)                       │
│  Pickup      : 12 Oktober 2026                    │
├──────────────────────────────────────────────────┤
│  Nama Produk A  x2                                │
│  Nama Produk B  x1                                │
├──────────────────────────────────────────────────┤
│         [ Unduh / Cetak Ticket ]                  │
└──────────────────────────────────────────────────┘
```

### 2.6 Dashboard Seller (`/seller`)

```
┌──────────────────────────────────────────────────┐
│ Dashboard Toko A               [+ Tambah Produk]  │
├──────────────────────────────────────────────────┤
│ Produk Aktif: 8      Order Masuk: 5               │
├──────────────────────────────────────────────────┤
│ Produk        Harga     Stok    Status            │
│ Produk A      15.000    12      Published         │
│ Produk B      20.000    0       Habis              │
│ Produk C      10.000    5       Draft (belum tayang)│
└──────────────────────────────────────────────────┘
```

### 2.7 Verifikasi Pembayaran — Admin (`/admin/payments`)

```
┌──────────────────────────────────────────────────┐
│ Verifikasi Pembayaran                             │
├──────────────────────────────────────────────────┤
│ #ORD-00042  Rp 50.000  [Lihat Bukti]               │
│            [ ✓ Verifikasi ]  [ ✗ Tolak ]          │
├──────────────────────────────────────────────────┤
│ #ORD-00043  Rp 30.000  [Lihat Bukti]               │
│            [ ✓ Verifikasi ]  [ ✗ Tolak ]          │
└──────────────────────────────────────────────────┘
```

### 2.8 Validasi Pickup — Admin/Seller (`/admin/pickup`)

```
┌──────────────────────────────────────────────────┐
│ Validasi Pickup                                   │
├──────────────────────────────────────────────────┤
│ Masukkan Kode Ticket: [______________] [ Cek ]    │
├──────────────────────────────────────────────────┤
│ ✓ Ticket valid — ORD-00042-TKT                     │
│   Nama Produk A x2, Nama Produk B x1              │
│   [ Tandai Sudah Diambil ]                        │
└──────────────────────────────────────────────────┘
```

### 2.9 Laporan Harian — Admin (`/admin/reports/daily`)

```
┌──────────────────────────────────────────────────┐
│ Laporan Harian     Tanggal: [12 Okt 2026 ▾]       │
├──────────────────────────────────────────────────┤
│ Total Order           : 45                        │
│ Pembayaran Sukses     : 40                        │
│ Order Dibatalkan      : 5                          │
│ Produk Terjual        : 88 item                   │
│ Revenue               : Rp 2.100.000              │
│ Sudah Pickup          : 30 / 40                    │
└──────────────────────────────────────────────────┘
```

---

## 3. Catatan Desain Tampilan

- Semua wireframe di atas untuk **layar desktop**; versi HP menumpuk kolom secara vertikal (kartu produk jadi 1 kolom, bukan 3).
- Warna & branding belum ditentukan di tahap ini — wireframe fokus ke **struktur & isi**, bukan estetika.
- Tombol aksi penting (Tambah ke Keranjang, Kirim Bukti, Verifikasi) selalu ditempatkan mudah dijangkau ibu jari di versi HP (bawah layar, bukan di pojok atas).
