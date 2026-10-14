# Panduan Admin Customer Service — FORTEXIV Marketplace

Panduan ini untuk admin yang bertugas membantu **pembeli** dan **penjual** yang mengalami kesulitan. Akun yang dipakai sama dengan akun admin biasa. Panduan operasional harian (verifikasi pembayaran, validasi pickup, laporan) ada di [PANDUAN_ADMIN.md](PANDUAN_ADMIN.md).

Rujukan untuk pengguna yang perlu diarahkan: [PANDUAN_PEMBELI.md](PANDUAN_PEMBELI.md) dan [PANDUAN_PENJUAL.md](PANDUAN_PENJUAL.md).

## Tanda dalam panduan ini

| Tanda | Arti |
|---|---|
| **[Bisa langsung]** | Bisa diselesaikan admin lewat aplikasi, atau cukup dengan mengarahkan pengguna. |
| **[Tim teknis]** | Belum ada fitur di aplikasi. Teruskan ke pengembang/operator server. |
| **[Kebijakan]** | Perlu keputusan sekolah atau pengelola, di luar aplikasi. |

> **Catatan:** aplikasi belum memiliki fitur tiket bantuan atau chat. Untuk sementara, terima keluhan lewat kanal sekolah (WhatsApp, email, atau meja layanan), lalu catat di log bantuan (lihat bagian akhir). Rancangan fitur CS di dalam aplikasi ada di [DOKUMENTASI_PENGEMBANG.md](DOKUMENTASI_PENGEMBANG.md#7-rancangan-fitur-customer-service).

---

## 1. Prinsip melayani

1. **Identifikasi pengguna.** Minta **email akun** dan, jika terkait pesanan, **kode pesanan** (`FX-00000001`) atau **kode ticket** (`FXT-...`).
2. **Jangan pernah meminta password** pengguna.
3. **Jangan bagikan data pengguna lain**, misalnya bukti transfer, email, atau nomor HP.
4. **Cek sebelum bertindak.** Aksi *Verify*, *Reject*, dan *Validate ticket* **tidak bisa dibatalkan** dari aplikasi.
5. **Catat setiap kasus** di log bantuan.

## 2. Alat yang tersedia untuk admin

| Halaman | Yang bisa dilihat/dilakukan |
|---|---|
| **Admin dashboard** | Ringkasan dan pembayaran yang menunggu verifikasi. |
| **Payment review** | Semua pesanan **yang sudah mengunggah bukti**: kode pesanan, pembeli, metode, total, status bayar, bukti, dan alasan penolakan. Verify/Reject. |
| **Manage accounts** | Semua pembeli dan penjual: email, peran, toko, status aktif. Enable/Disable. |
| **Validate pickup** | Validasi ticket. **Langsung menandai pesanan sebagai diambil.** |
| **Daily reports** | Rekap pesanan per tanggal. |

Batasan alat admin saat ini:

- Admin **tidak bisa melihat detail pesanan** yang masih *Pending payment* (belum ada bukti), dan tidak bisa mencari pesanan berdasarkan kode. Minta pengguna mengirim tangkapan layar halaman pesanannya.
- Admin **tidak bisa** mereset password, mengubah peran, mengubah email, atau membatalkan pesanan lewat aplikasi.

---

## 3. Masalah umum pembeli

### Akun & masuk

| Keluhan | Penyebab | Penanganan |
|---|---|---|
| "Email atau password tidak cocok" | Salah ketik, atau email belum terdaftar. | **[Bisa langsung]** Cek di *Manage accounts* apakah email terdaftar. Jika belum, arahkan ke **Join marketplace**. |
| Lupa password | Fitur reset password belum ada. | **[Tim teknis]** Verifikasi identitas pengguna, lalu minta tim teknis mengganti password. |
| "Akun ini telah dinonaktifkan" | Akun di-*Disable* admin. | **[Bisa langsung]** Pastikan alasan penonaktifan sudah selesai, lalu klik **Enable** di *Manage accounts*. |
| Terlanjur daftar sebagai pembeli, ingin jadi penjual | Peran tidak bisa diubah di UI, dan satu email hanya untuk satu akun. | **[Bisa langsung]** Sarankan daftar ulang sebagai penjual dengan email lain. **[Tim teknis]** jika harus memakai email yang sama. |

### Keranjang & checkout

| Keluhan | Penyebab | Penanganan |
|---|---|---|
| "Jumlah melebihi stok yang tersedia" | Jumlah di keranjang melebihi stok. | **[Bisa langsung]** Minta kurangi jumlah. |
| Halaman error saat mengubah jumlah di keranjang | Jumlah baru melebihi stok. | **[Bisa langsung]** Minta kembali ke halaman sebelumnya dan isi jumlah yang lebih kecil. |
| "Stok salah satu produk berubah" | Stok berkurang sebelum checkout. | **[Bisa langsung]** Minta periksa keranjang, sesuaikan jumlah, lalu checkout ulang. |
| Halaman error **503** / *"Manual bank transfer details are not configured yet"* | Data rekening sekolah belum diisi di server. | **[Tim teknis]** Isi `PAYMENT_BANK_NAME`, `PAYMENT_ACCOUNT_NAME`, dan `PAYMENT_ACCOUNT_NUMBER`. **Ini memblokir semua pembeli**, jadi tangani segera. |
| Tanggal pickup tidak bisa dipilih | Tanggal sebelum hari ini ditolak. | **[Bisa langsung]** Pilih hari ini atau setelahnya. |
| Produk tidak ditemukan | Produk masih draft atau stoknya 0. | **[Bisa langsung]** Jelaskan bahwa produk sedang tidak tersedia. Hubungi penjual jika perlu. |

### Pembayaran

| Keluhan | Penyebab | Penanganan |
|---|---|---|
| Gagal unggah bukti | Format bukan JPG/PNG/PDF, atau ukuran > 5 MB. | **[Bisa langsung]** Minta kompres atau tangkap layar ulang. |
| Salah unggah bukti (status *Waiting verification*) | Bukti hanya bisa dikirim sekali. | **[Bisa langsung]** Minta pengguna mengirim bukti yang benar lewat kanal CS. Cocokkan dengan mutasi rekening, lalu **Verify** jika dana sudah masuk. Jika tidak bisa dicocokkan, **Reject** dengan alasan jelas dan minta checkout ulang. |
| Sudah transfer, status masih *Waiting verification* | Belum diproses admin. | **[Bisa langsung]** Cek di *Payment review*, cocokkan dengan mutasi rekening, lalu proses. |
| Sudah transfer, tapi lupa unggah bukti (status *Pending payment*) | — | **[Bisa langsung]** Arahkan ke **My orders → Details**, lalu unggah bukti. |
| Pesanan *Cancelled* / pembayaran ditolak | Admin menolak bukti. | **[Bisa langsung]** Jelaskan alasan dari *Admin note*. Pesanan tidak bisa dihidupkan kembali, jadi pengguna perlu checkout ulang. Jika dana **sudah benar-benar masuk**, lanjutkan ke baris berikutnya. |
| Minta uang kembali (refund) | Fitur refund belum ada. | **[Kebijakan]** Refund dilakukan manual oleh bendahara/pengelola di luar aplikasi. Catat kode pesanan, nominal, dan rekening pengembalian. |
| Ingin membatalkan pesanan yang belum dibayar | Pembeli belum bisa membatalkan sendiri, dan stoknya tertahan. | **[Tim teknis]** Minta pembatalan dan pengembalian stok. |

### Pengambilan barang

| Keluhan | Penyebab | Penanganan |
|---|---|---|
| Ticket hilang | — | **[Bisa langsung]** Ticket bisa dibuka lagi: **My orders → Details → View pickup ticket**. |
| Tombol *View pickup ticket* tidak ada | Pembayaran belum diverifikasi. | **[Bisa langsung]** Cek status di *Payment review* dan proses jika masih *Pending*. |
| "Ticket tidak valid atau sudah pernah dipakai" | Kode salah ketik, atau ticket sudah dipakai. | **[Bisa langsung]** Cocokkan kode dengan layar pembeli. Jika pembeli bilang belum pernah mengambil, catat kasusnya dan teruskan ke **[Tim teknis]** untuk mengecek siapa dan kapan ticket divalidasi. |
| "Pesanan ini belum lunas atau sudah selesai diambil" | Status bukan *Paid*. | **[Bisa langsung]** Cek status pembayaran di *Payment review*. |
| Datang di luar tanggal pickup | Sistem tidak membatasi validasi berdasarkan tanggal. | **[Kebijakan]** Tentukan apakah tetap dilayani. |

> **Penting:** *Validate ticket* langsung menandai pesanan sebagai **Picked up**. Validasi hanya saat barang benar-benar diserahkan, dan **cek kecocokan nama pembeli** pada hasil validasi.

---

## 4. Masalah umum penjual

| Keluhan | Penyebab | Penanganan |
|---|---|---|
| Halaman error **403** saat membuka *Seller dashboard* | Akun dinonaktifkan, atau profil toko tidak ada. | **[Bisa langsung]** Cek status di *Manage accounts* dan aktifkan jika perlu. Jika kolom *Store* kosong (`—`), teruskan ke **[Tim teknis]**. |
| Pendaftaran gagal, *store name* wajib | Penjual wajib mengisi **Store / class name**. | **[Bisa langsung]** Arahkan untuk mengisi kolom tersebut. |
| Produk tidak tampil di Marketplace | Status masih **Draft**, atau stoknya 0. | **[Bisa langsung]** Arahkan ke **Manage products → Publish**, dan pastikan stok ≥ 1. |
| Tidak bisa *Publish*: "Tambahkan stok…" | Stok 0. | **[Bisa langsung]** **Edit** produk dan isi stok. |
| Harga ditolak | Harga harus angka bulat tanpa titik atau koma. | **[Bisa langsung]** Contoh: `25000`, bukan `25.000`. |
| Gambar gagal diunggah | Format bukan JPG/PNG/WebP, atau ukuran > 5 MB. | **[Bisa langsung]** Minta kompres atau ganti format. |
| Tidak bisa menghapus produk | Produk sudah pernah dipesan. | **[Bisa langsung]** Ubah **Stock** menjadi `0` agar produk tidak tampil. |
| Ingin menyembunyikan produk sementara | Belum ada tombol *unpublish*. | **[Bisa langsung]** Ubah stok menjadi `0`, lalu kembalikan stoknya saat ingin dijual lagi. |
| Stok berkurang padahal belum dibayar | Stok dipesan saat checkout. Stok kembali hanya jika pembayaran ditolak. | **[Bisa langsung]** Jelaskan alurnya. Jika pesanan lama tidak pernah dibayar, teruskan ke **[Tim teknis]** untuk dibatalkan. |
| Kapan dana penjualan diterima? | Pencairan dana belum ada di aplikasi. | **[Kebijakan]** Ikuti prosedur pencairan sekolah. Gunakan *Daily reports* dan halaman *Orders & buyers* penjual sebagai rujukan. |
| Ingin ganti nama toko / proyek kelas | Belum ada UI. | **[Tim teknis]** |

---

## 5. Eskalasi ke tim teknis

Kirim informasi berikut:

```
Tanggal/jam     :
Email pengguna  :
Peran           : pembeli / penjual
Kode pesanan    : FX-........   (jika ada)
Kode ticket     : FXT-.......   (jika ada)
Masalah         :
Langkah yang sudah dicoba :
Tangkapan layar : (lampirkan)
Permintaan      : mis. reset password / batalkan pesanan / ubah peran
```

Tim teknis dapat melakukan perubahan lewat `php artisan tinker` atau database. Detailnya ada di [DOKUMENTASI_PENGEMBANG.md](DOKUMENTASI_PENGEMBANG.md).

## 6. Log bantuan (sementara)

Sampai fitur CS tersedia di aplikasi, catat setiap kasus di spreadsheet bersama dengan kolom berikut:

| No | Tanggal | Pengguna (email) | Peran | Kode pesanan/ticket | Kategori | Ringkasan | Tindakan | Status (Baru/Proses/Eskalasi/Selesai) | Petugas |
|---|---|---|---|---|---|---|---|---|---|

Kategori yang disarankan: **Akun**, **Checkout**, **Pembayaran**, **Pickup**, **Produk (penjual)**, **Refund**, **Lainnya**.
