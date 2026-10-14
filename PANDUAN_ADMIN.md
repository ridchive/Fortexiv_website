# Panduan Admin — FORTEXIV Marketplace

Admin memverifikasi pembayaran transfer manual, memvalidasi ticket saat pembeli mengambil barang, mengelola akun pembeli dan penjual, dan melihat laporan harian.

Untuk menangani keluhan dan kesulitan pembeli atau penjual, lihat [PANDUAN_ADMIN_CS.md](PANDUAN_ADMIN_CS.md).

## Persiapan awal (sekali saja)

### 1. Membuat akun admin

Akun admin **tidak bisa dibuat lewat halaman pendaftaran**. Buat dari terminal di folder project:

```sh
php artisan marketplace:create-admin "Nama Admin" admin@sekolah.sch.id
```

Anda akan diminta memasukkan password, **minimal 12 karakter**. Jika email sudah terdaftar, akun tersebut diubah menjadi admin dan password-nya diganti.

> Di komputer pengembangan ini, gunakan PHP 8.4: `C:\xampp\php84\php.exe artisan marketplace:create-admin ...`

### 2. Mengisi rekening tujuan transfer

Pembeli **tidak bisa checkout** sebelum data rekening diisi. Isi di file `.env`:

```env
PAYMENT_BANK_NAME="Bank BCA"
PAYMENT_ACCOUNT_NAME="Koperasi Sekolah"
PAYMENT_ACCOUNT_NUMBER="1234567890"
```

Jika config di-cache (`php artisan config:cache`), jalankan `php artisan config:clear` atau cache ulang setelah mengubah `.env`.

## Alur pesanan dari sisi admin

```
Pembeli checkout (Pending payment)
→ Pembeli unggah bukti (Waiting verification)
→ Admin Verify → Paid + ticket terbit   |   Admin Reject → Cancelled + stok kembali
→ Pembeli datang dengan ticket → Admin Validate ticket → Picked up
```

## 1. Admin dashboard

Masuk lewat **Sign in**. Admin otomatis diarahkan ke **Admin dashboard**, yang menampilkan:

- total pesanan,
- jumlah pembayaran yang menunggu verifikasi,
- jumlah pembeli,
- jumlah produk terjual (pesanan *Paid* dan *Picked up*),
- daftar **Payments awaiting verification**.

Dari sini ada pintasan ke **Payment review**, **Validate pickup**, **Manage accounts**, dan **Daily reports**.

## 2. Memverifikasi pembayaran (Payment review)

1. Buka **Payment review**.
2. Klik **Download receipt** untuk mengunduh bukti transfer pembeli.
3. Cocokkan dengan mutasi rekening: nominal sama dengan **Total**, dan dana sudah masuk.
4. Ambil keputusan:
   - **Verify**: pesanan menjadi **Paid** dan **ticket pickup** (`FXT-...`) otomatis terbit untuk pembeli.
   - **Reject**: isi alasan di kolom **Reason** (wajib, maksimal 255 karakter), lalu klik **Reject**. Pesanan menjadi **Cancelled**, **stok produk dikembalikan**, dan alasan Anda tampil kepada pembeli sebagai *Admin note*.

Setiap pembayaran hanya bisa diproses satu kali. Keputusan Verify atau Reject **tidak bisa dibatalkan** dari aplikasi, jadi periksa dengan teliti.

## 3. Memvalidasi pengambilan barang (Validate pickup)

1. Buka **Validate pickup**.
2. Minta pembeli menunjukkan ticket, lalu ketik **Ticket code** (format `FXT-XXXXXXXXXXXX`). Huruf besar atau kecil tidak berpengaruh.
3. Klik **Validate ticket**.
4. Jika valid, nama pembeli dan pesanan ditampilkan, dan pesanan ditandai **Picked up**. Serahkan barang.

Ticket ditolak dengan pesan *"Ticket tidak valid atau sudah pernah dipakai"* bila kodenya salah atau sudah pernah digunakan. Ticket hanya berlaku sekali.

## 4. Mengelola akun (Manage accounts)

1. Buka **Manage accounts** untuk melihat semua pembeli dan penjual: nama, email, peran, toko, dan status.
2. Klik **Disable** untuk menonaktifkan akun. Pengguna tersebut tidak bisa masuk lagi, tetapi riwayat pesanan dan pickup-nya tetap tersimpan.
3. Klik **Enable** untuk mengaktifkan kembali.

Akun admin tidak tampil di daftar ini dan tidak bisa dinonaktifkan dari sini.

## 5. Laporan harian (Daily reports)

1. Buka **Daily reports**.
2. Pilih **Date**, lalu klik **View**. Tanpa tanggal, laporan menampilkan hari ini.
3. Laporan berisi data **pesanan yang dibuat pada tanggal tersebut**:
   - total pesanan,
   - pesanan lunas (*Paid* + *Picked up*),
   - pesanan dibatalkan,
   - pesanan sudah diambil,
   - pendapatan (dari pesanan lunas),
   - jumlah produk terjual.

## Status pesanan

| Status | Arti | Tindakan admin |
|---|---|---|
| **Pending payment** | Menunggu pembeli mengunggah bukti. | — |
| **Waiting verification** | Bukti terkirim. | Verify atau Reject |
| **Paid** | Lunas, ticket terbit. | Validasi ticket saat pickup |
| **Picked up** | Barang sudah diambil. | — |
| **Cancelled** | Pembayaran ditolak, stok kembali. | — |
