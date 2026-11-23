# Laporan Evaluasi & Tindak Lanjut Audit Keamanan Transaksi (FORTEXIV)

**Dokumen Acuan:** `SECURITY_AUDIT_STOCK_PAYMENT.md` (Audit 22 November 2026)  
**Target Sistem:** FORTEXIV E-Commerce Pipeline (`Checkout`, `Stock Management`, `Manual Payment`, `Admin Verification`, & `Ticket Issuance`)  
**Status Review:** Hasil Filter & Koreksi Teknis Tim

---

## 1. Ringkasan Eksekutif & Keputusan Filter

Berdasarkan hasil audit keamanan dan konkurensi pada `SECURITY_AUDIT_STOCK_PAYMENT.md`, fondasi transaksi inti FORTEXIV sudah tangguh (**Skor Ketahanan: 7.8 / 10**) karena terlindungi oleh mekanisme *pessimistic locking* (`lockForUpdate()`) dan kalkulasi harga sisi server.

Setelah memfilter seluruh temuan dan menyesuaikannya dengan arsitektur aktual repositori FORTEXIV (pengaturan *dual-cap* `PurchaseWindow`, struktur *shared hosting* cPanel, serta integritas transaksi database), berikut adalah keputusan tindak lanjut untuk ke-5 temuan audit:

| Kode | Judul Temuan | Tingkat Bahaya | Keputusan Tim | Catatan Teknis Kunci |
|:---|:---|:---:|:---|:---|
| **VULN-01** | Penolakan admin (`reject`) tidak memperpanjang `expires_at` | **HIGH** | **WAJIB UBAH** | Wajib menyelaraskan durasi & aturan *dual-cap* dengan `config/marketplace.php` serta `PurchaseWindow::paymentDeadline()`. |
| **VULN-02** | Potensi *database deadlock* pada checkout keranjang multi-produk | **MEDIUM** | **WAJIB UBAH** | Tambahkan pengurutan deterministik (`sortBy('product_id')`) sebelum `lockForUpdate()` di checkout maupun pengembalian stok. |
| **VULN-03** | Ketergantungan mutlak pada Cron Server untuk rilis stok | **MEDIUM** | **UBAH TERBATAS** | Hindari pemindaian global di halaman Katalog publik; terapkan *lazy cancellation* ringan pada detail order / `uploadPayment` atau kunci dengan `Cache::lock`. |
| **VULN-04** | *Race condition* (TOCTOU) penerbitan tiket di rute `GET /orders/{order}` | **LOW** | **UBAH** | Gunakan `createOrFirst()` atau `firstOrCreate()` dengan penanganan `UniqueConstraintViolationException` agar bebas Error 500 saat multi-tab dibuka. |
| **VULN-05** | Akumulasi file bukti transfer lama di penyimpanan (*storage leak*) | **LOW** | **UBAH** | Hapus file bukti transfer lama **setelah** file baru tersimpan dan transaksi database sukses ter-commit. |

---

## 2. Evaluasi & Koreksi Teknis per Temuan

### 1. [VULN-01] Siklus Penolakan Admin Mengunci Pesanan ke Status Batal Otomatis
* **Severity:** `HIGH`
* **Status:** **Wajib Diubah (Dengan Koreksi Implementasi)**
* **Lokasi Terkait:** `app/Http/Controllers/AdminController.php` (`reject()`), `config/marketplace.php`, dan `app/Models/PurchaseWindow.php`
* **Analisis Masalah:**  
  Saat admin menolak bukti pembayaran yang buram atau salah nominal, status pesanan dikembalikan ke `pending_payment` tanpa memperbarui kolom `expires_at`. Karena proses verifikasi admin umumnya terjadi berjam-jam setelah pembeli membuat pesanan, nilai `expires_at` lama sudah berada di masa lalu. Akibatnya, jadwal cron `marketplace:cancel-expired-manual-orders` langsung membatalkan pesanan tersebut dan mengembalikan stok sebelum pembeli sempat mengunggah ulang bukti transfer.
* **Koreksi pada Rekomendasi Laporan Awal:**  
  Laporan audit mencontohkan kunci konfigurasi `config('marketplace.payment_timeout_minutes', 10)`. Pada codebase aktual FORTEXIV, konfigurasi batas waktu memakai `config('marketplace.order_timeout_hours', 12)` dan menerapkan batas maksimum ganda (*dual-cap*) terhadap `PurchaseWindow::paymentDeadline()`.
* **Solusi Final:**
  * Perpanjang `expires_at` saat `AdminController@reject` dijalankan menggunakan durasi yang konsisten dengan konfigurasi proyek (misalnya menambah masa tenggang baru dari `now()` sesuai konfigurasi timeout/grace period).
  * Pastikan aturan interaksi antara masa tenggang unggah ulang dan jadwal tutup bayar PO (`paymentDeadline`) terdefinisi jelas sehingga pembeli memiliki waktu yang wajar untuk mengunggah ulang bukti.

---

### 2. [VULN-02] Potensi Database Deadlock pada Transaksi Keranjang Multi-Produk
* **Severity:** `MEDIUM`
* **Status:** **Wajib Diubah**
* **Lokasi Terkait:** `app/Http/Controllers/MarketplaceController.php` (`checkout()`) & `app/Actions/CancelOrder.php`
* **Analisis Masalah:**  
  Penguncian baris produk (`Product::whereKey(...)->lockForUpdate()`) dilakukan mengikuti urutan array item keranjang yang dikirim oleh pengguna. Jika dua pembeli melakukan checkout secara bersamaan untuk pasangan produk yang sama namun dengan urutan berbeda (`[Produk A, Produk B]` vs `[Produk B, Produk A]`), mesin database InnoDB berisiko mengalami *circular lock-wait* (*deadlock* error `1213`) yang memicu HTTP 500.
* **Solusi Final:**
  * Urutkan daftar item berdasarkan `product_id` secara menaik (*ascending*) sebelum memulai penguncian baris di `MarketplaceController@checkout`:
    ```php
    $items = collect($items)->sortBy('product_id')->values();
    ```
  * Terapkan pengurutan `product_id` yang sama pada `CancelOrder` saat mengembalikan stok multi-produk agar arah penguncian baris selalu seragam di seluruh aplikasi.

---

### 3. [VULN-03] Ketergantungan Mutlak pada Cron Server untuk Pengembalian Stok
* **Severity:** `MEDIUM`
* **Status:** **Ubah Terbatas (Jangan Dipasang di Katalog Publik)**
* **Lokasi Terkait:** `app/Console/Commands/CancelExpiredManualOrders.php`, `routes/console.php`, `app/Http/Controllers/MarketplaceController.php`
* **Analisis Masalah:**  
  Pada lingkungan *shared hosting* cPanel, cron job `schedule:run` berisiko terhenti atau terlambat dieksekusi. Jika hanya mengandalkan cron, pesanan `pending_payment` yang sudah melewati `expires_at` akan menahan stok barang lebih lama dari seharusnya (*ghost out-of-stock*).
* **Koreksi pada Rekomendasi Laporan Awal:**  
  Menjalankan pembersihan seluruh pesanan kedaluwarsa di setiap kunjungan halaman **Katalog** publik tidak disarankan karena mengubah request baca (`GET`) menjadi operasi tulis dan penguncian tabel yang dapat memperlambat performa saat trafik ramai.
* **Solusi Final:**
  1. **Per-Order Lazy Check:** Jalankan pembatalan otomatis (`CancelOrder`) khusus untuk pesanan terkait saat pembeli mengakses detail pesanannya (`MarketplaceController@order`) atau saat `uploadPayment()` mendeteksi `$order->isExpired()`.
  2. **Throttled Global Fallback (Opsional):** Jika dibutuhkan pembersihan berkala tanpa cron cPanel, bungkus pemanggilan `CancelExpiredManualOrders` dengan `Cache::lock('lazy-cancel-expired-orders', 300)->get(...)` agar maksimal hanya dieksekusi satu kali setiap 5 menit.
  3. **Operasional Server:** Pastikan Cron Job Laravel tetap terpasang aktif di cPanel dan pertahankan tombol manual **"Batalkan & Kembalikan Stok"** pada halaman `/admin/payments`.

---

### 4. [VULN-04] Time-of-Check to Time-of-Use (TOCTOU) pada Penerbitan Tiket di Rute GET
* **Severity:** `LOW`
* **Status:** **Ubah**
* **Lokasi Terkait:** `app/Http/Controllers/MarketplaceController.php` (rute detail pesanan `/orders/{order}`)
* **Analisis Masalah:**  
  Pengecekan `if (! $order->ticket()->exists())` yang diikuti `$order->ticket()->create(...)` pada request `GET` rentan memicu pelanggaran indeks unik (`1062 Duplicate entry for key 'tickets_order_id_unique'`) apabila pembeli membuka atau me-refresh dua tab halaman pesanan secara bersamaan sesaat setelah admin memverifikasi pembayaran.
* **Solusi Final:**
  * Ganti pola *check-then-create* dengan operasi atomik `createOrFirst()` (atau `firstOrCreate()` yang dibungkus `try-catch` `UniqueConstraintViolationException` lalu me-refresh relasi `$order->load('ticket')`).

---

### 5. [VULN-05] Akumulasi File Bukti Transfer Lama di Disk (Storage Leak)
* **Severity:** `LOW`
* **Status:** **Ubah (Dengan Urutan Penghapusan yang Aman)**
* **Lokasi Terkait:** `app/Http/Controllers/MarketplaceController.php` (`uploadPayment()`)
* **Analisis Masalah:**  
  Saat pembeli mengunggah ulang bukti transfer setelah pembayaran ditolak, path file di tabel `payments` diperbarui namun file bukti fisik lama tidak dihapus dari disk privat, sehingga perlahan menghabiskan kuota penyimpanan *shared hosting*.
* **Koreksi pada Rekomendasi Laporan Awal:**  
  Jangan menghapus file lama *sebelum* penyimpanan file baru dan transaksi database selesai. Jika proses simpan atau commit database gagal di tengah jalan, file bukti transfer sebelumnya akan hilang permanen.
* **Solusi Final:**
  * Simpan path file lama ke variabel sementara (`$oldProofPath`), simpan file baru dan selesaikan `DB::transaction()`, lalu hapus `$oldProofPath` **hanya setelah** transaksi database berhasil ter-commit.

---

## 3. Ringkasan Prioritas Implementasi

1. **Prioritas 1 (`VULN-01`):** Perbaiki perpanjangan `expires_at` pada `AdminController@reject` sesuai konfigurasi `config/marketplace.php` dan aturan `PurchaseWindow`.
2. **Prioritas 2 (`VULN-02`):** Tambahkan pengurutan `sortBy('product_id')` sebelum loop `lockForUpdate()` di `MarketplaceController@checkout` dan `CancelOrder`.
3. **Prioritas 3 (`VULN-04`):** Ubah pembuatan tiket cadangan pada `MarketplaceController@order` menjadi atomik (`createOrFirst()` / penanganan duplikasi unik).
4. **Prioritas 4 (`VULN-05`):** Bersihkan file bukti transfer lama setelah `DB::transaction()` sukses pada `MarketplaceController@uploadPayment`.
5. **Prioritas 5 (`VULN-03`):** Terapkan *per-order lazy cancellation* saat pembeli membuka detail order yang kedaluwarsa serta pastikan cron cPanel aktif.
6. **Verifikasi Akhir:** Tambahkan skenario pengujian otomatis pada `tests/Feature/MarketplaceTest.php` untuk menguji perpanjangan waktu setelah `reject`, idempotensi penerbitan tiket, serta pembersihan file lama, lalu jalankan seluruh suite `php artisan test`.
