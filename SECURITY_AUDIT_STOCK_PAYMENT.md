# Laporan Audit Keamanan & Integritas Transaksi (Security & Concurrency Audit)

**Aplikasi:** FORTEXIV E-Commerce Pipeline  
**Fokus Audit:** Stock Management, Cart & Checkout Concurrency, Order Expiration, Manual Payment, & Admin Fulfillment  
**Tanggal Audit:** 22 November 2026  
**Auditor:** Principal Security Researcher & Laravel Architect  

---

## 1. Ringkasan Eksekutif (Executive Summary)

Audit ini mengevaluasi ketahanan alur transaksi e-commerce pada FORTEXIV terhadap serangan race condition (konkurensi tinggi), desinkronisasi stok, manipulasi integritas pembayaran, dan celah logika alur pesanan (*business logic flaws*).

| Metrik Evaluasi | Skor / Penilaian | Catatan Utama |
|:---|:---:|:---|
| **Resilience Score** | **7.8 / 10** | Arsitektur dasar transaksi tangguh dengan *pessimistic locking*. |
| **Integritas Stok (Single Item)**| **Kuat (Robust)** | Mekanisme `lockForUpdate()` mencegah *negative stock* saat 2 pembeli checkout bersamaan. |
| **Integritas Finansial** | **Kuat (Robust)** | Harga dan subtotal dihitung murni di server (kebal manipulasi parameter POST). |
| **Multi-Tenancy Seller** | **Kuat (Robust)** | Verifikasi penyerahan barang dan QR pickup divalidasi ketat per `seller_id`. |
| **Celah Kritis (Critical)** | **0** | Tidak ada celah eksekusi kode (RCE) atau bypass otentikasi. |
| **Celah Tinggi (High)** | **1** | Alur penolakan bukti bayar oleh admin merusak batas waktu (*immediate auto-cancel*). |
| **Celah Menengah (Medium)** | **2** | Potensi *Deadlock* pada checkout multi-produk & *Single Point of Failure* pada Cron. |
| **Celah Rendah (Low)** | **2** | TOCTOU pada *lazy generation* tiket di request GET & akumulasi file bukti transfer lama. |

---

## 2. Hasil Pengujian Skenario Utama (Concurrency & Logic Simulation)

### Skenario 1: 2 Pembeli Checkout Produk Stok Sisa 1 di Detik yang Sama
* **Langkah:** User A dan User B mengirim request `POST /checkout` untuk produk yang sama (stok = 1) pada milidetik yang identik.
* **Hasil Uji:** **LULUS (Aman)**.
* **Analisis Kode:** Pada `MarketplaceController@checkout`, pemotongan stok berada di dalam `DB::transaction` menggunakan:
  ```php
  $product = Product::query()->whereKey($item['product_id'])->lockForUpdate()->first();
  if (! $product || ... || $product->stock < $item['quantity']) {
      throw ValidationException::withMessages(['cart' => 'Stok atau ketersediaan produk berubah...']);
  }
  $product->decrement('stock', $item['quantity']);
  ```
  Di mesin MySQL (InnoDB), transaksi pertama memegang *exclusive row lock*. Transaksi kedua dipaksa antre (*lock-wait*). Setelah transaksi pertama commit (stok berkurang jadi 0), transaksi kedua membaca stok terbaru (0), kondisi `0 < 1` bernilai true, lalu transaksi kedua di-rollback dengan error validasi. **Stok tidak pernah menjadi minus (-1).**

---

### Skenario 2: Pembeli Mengunggah Bukti Bayar Tepat di Detik Batas Waktu Berakhir (00:00)
* **Langkah:** Pembeli mengunggah bukti bayar pada detik terakhir bersamaan dengan command `marketplace:cancel-expired-manual-orders` yang dieksekusi scheduler.
* **Hasil Uji:** **LULUS (Aman)**.
* **Analisis Kode:** Kedua proses (`uploadPayment` dan `CancelOrder`) membungkus modifikasi dengan `Order::lockForUpdate()`. 
  - Jika `uploadPayment` memperoleh kunci lebih dulu, status berubah menjadi `waiting_verification`. Saat cron berjalan berikutnya, cron memfilter `where('status', 'pending_payment')` sehingga pesanan tidak dibatalkan.
  - Jika cron memperoleh kunci lebih dulu, status diubah menjadi `cancelled` dan stok dikembalikan. Saat request upload pembeli membaca order, `abort_unless($lockedOrder->status === 'pending_payment')` akan menolak request dengan HTTP 422. Tidak ada kondisi setengah-batal.

---

### Skenario 3: Admin Menekan Tombol Verifikasi Dua Kali / Multi-Tab Cepat
* **Langkah:** Admin membuka 2 tab atau melakukan double-click cepat pada tombol verifikasi bukti pembayaran.
* **Hasil Uji:** **LULUS (Idempotent)**.
* **Analisis Kode:** Pada `AdminController@verify`, status diperiksa dengan kunci eksklusif:
  ```php
  abort_unless(
      $lockedPayment->status === 'pending' && $order->status === 'waiting_verification',
      422,
      'Pembayaran ini sudah diproses atau bukan pembayaran manual.'
  );
  ```
  Request pertama berhasil mengubah pesanan menjadi `paid`. Request kedua membaca status baru dan langsung dibatalkan (HTTP 422). Tiket dan rekam dampak karbon tidak terduplikasi.

---

### Skenario 4: Manipulasi Harga & Jumlah Pembelian
* **Langkah:** Pembeli memanipulasi payload HTTP POST dengan menyisipkan parameter `price=0` atau `subtotal=1`.
* **Hasil Uji:** **LULUS (Kebal Manipulasi)**.
* **Analisis Kode:** Backend mengabaikan seluruh nilai harga dari request. Harga diambil langsung dari database (`$product->price`), dan total dihitung murni di server. Input `quantity` dibatasi secara ketat `min:1, max:100`.

---

## 3. Temuan Celah Keamanan & Kerentanan Bisnis (Vulnerabilities Found)

### 🔴 [VULN-01] Siklus Penolakan Admin Mengunci Pesanan ke Status Batal Otomatis (Severity: HIGH)
* **File Terkait:** `app/Http/Controllers/AdminController.php` (baris 150–170)
* **Akar Masalah:**
  Ketika admin menolak bukti pembayaran yang buram atau tidak sesuai via fungsi `reject()`, sistem mengembalikan status pesanan ke `pending_payment`:
  ```php
  $lockedPayment->update(['status' => 'rejected', ...]);
  $order->update(['status' => 'pending_payment']);
  ```
  Namun, kolom `$order->expires_at` **TIDAK diperbarui atau diperpanjang**.
* **Dampak Bisnis:**
  Biasanya admin memverifikasi pesanan beberapa menit atau jam setelah pesanan dibuat (melebihi batas awal 10 menit). Karena `expires_at` masih menggunakan timestamp masa lalu, maka dalam hitungan detik berikutnya:
  1. Cron scheduler `marketplace:cancel-expired-manual-orders` langsung membatalkan pesanan secara otomatis dan merestorasi stoknya ke katalog.
  2. Saat pembeli menerima email penolakan dan membuka link untuk unggah ulang bukti, pesanan sudah hangus dan ditolak dengan HTTP 422 (*Batas waktu pembayaran telah berakhir*).
* **Rekomendasi Perbaikan:**
  Berikan perpanjangan batas waktu baru (misal 10 menit sejak penolakan) saat admin me-reject pembayaran:
  ```php
  $timeoutMinutes = config('marketplace.payment_timeout_minutes', 10);
  $order->update([
      'status' => 'pending_payment',
      'expires_at' => now()->addMinutes($timeoutMinutes),
  ]);
  ```

---

### 🟡 [VULN-02] Potensi Database Deadlock pada Transaksi Keranjang Multi-Produk (Severity: MEDIUM)
* **File Terkait:** `app/Http/Controllers/MarketplaceController.php` (baris 370–405)
* **Akar Masalah:**
  Pada checkout keranjang, sistem mengunci produk satu per satu sesuai urutan array `$items` yang masuk:
  ```php
  foreach ($items as $item) {
      $product = Product::query()->whereKey($item['product_id'])->lockForUpdate()->first();
      ...
  }
  ```
* **Dampak Bisnis:**
  Jika User A checkout dengan keranjang `[Produk 1, Produk 2]` dan User B checkout bersamaan dengan keranjang `[Produk 2, Produk 1]`, User A mengunci Produk 1 dan menunggu Produk 2, sedangkan User B mengunci Produk 2 dan menunggu Produk 1. MySQL InnoDB akan mendeteksi *deadlock cycle* dan mematikan salah satu transaksi dengan error `1213 Deadlock found when trying to get lock`. Pengguna akan mengalami HTTP 500.
* **Rekomendasi Perbaikan:**
  Urutkan selalu item berdasarkan ID produk secara deterministik (*ascending*) sebelum mengunci baris database:
  ```php
  $items = $items->sortBy(fn ($item) => $item['product_id']);
  ```

---

### 🟡 [VULN-03] Ketergantungan Mutlak pada Cron Server untuk Pengembalian Stok (Severity: MEDIUM)
* **File Terkait:** `app/Console/Commands/CancelExpiredManualOrders.php` & `routes/console.php`
* **Akar Masalah:**
  Pengembalian stok pesanan kedaluwarsa hanya dilakukan oleh perintah cron `marketplace:cancel-expired-manual-orders`.
* **Dampak Bisnis:**
  Pada shared hosting cPanel, apabila daemon/cron job `schedule:run` mati, tidak terpasang, atau terhenti karena limit resource hosting, pesanan yang kedaluwarsa akan tetap berstatus `pending_payment` di database. Stok barang yang ditinggalkan pembeli (*abandoned cart*) akan terkunci selamanya (*ghost out-of-stock*) dan tidak pernah kembali dijual di katalog.
* **Rekomendasi Perbaikan:**
  Terapkan *Lazy Cancellation*: Saat pembeli atau sistem mendeteksi pesanan yang diakses sudah melewati `expires_at`, trigger pembatalan dan restorasi stok secara langsung tanpa harus menunggu putaran cron berikutnya.

---

### 🔵 [VULN-04] Time-of-Check to Time-of-Use (TOCTOU) pada Penerbitan Tiket di Rute GET (Severity: LOW)
* **File Terkait:** `app/Http/Controllers/MarketplaceController.php` (baris 540–547)
* **Akar Masalah:**
  Pada rute GET detail pesanan `/orders/{order}`, terdapat mekanisme pembuatan tiket otomatis jika pembayaran sudah lunas namun tiket belum ada:
  ```php
  if (in_array(...) && ! $order->ticket()->exists()) {
      $order->ticket()->create([
          'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
          'issued_at' => now(),
      ]);
  }
  ```
  Pada database, kolom `order_id` pada tabel `tickets` memiliki indeks `UNIQUE`.
* **Dampak Bisnis:**
  Jika pembeli membuka dua tab pesanan sekaligus sesaat setelah diverifikasi admin, kedua tab bisa membaca `ticket()->exists()` bernilai `false` di saat bersamaan. Salah satu tab akan mengalami crash HTTP 500 akibat `Integrity constraint violation: 1062 Duplicate entry for key 'tickets_order_id_unique'`.
* **Rekomendasi Perbaikan:**
  Gunakan `firstOrCreate()` agar aman dari duplikasi konkurensi:
  ```php
  $order->ticket()->firstOrCreate([], [
      'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
      'issued_at' => now(),
  ]);
  ```

---

### 🔵 [VULN-05] Akumulasi File Bukti Transfer Lama di Disk / Storage Leak (Severity: LOW)
* **File Terkait:** `app/Http/Controllers/MarketplaceController.php` (baris 587–598)
* **Akar Masalah:**
  Ketika pembeli mengunggah ulang bukti transfer untuk pesanan yang ditolak, sistem memperbarui path file di database tetapi tidak menghapus file bukti transfer fisik yang lama di `storage/app/payment-proofs/`.
* **Dampak Bisnis:**
  Kapasitas disk hosting akan terus terkuras oleh file-file screenshot lama yang sudah tidak terpakai.
* **Rekomendasi Perbaikan:**
  Hapus file bukti lama sebelum menyimpan path yang baru:
  ```php
  if ($payment && $payment->proof_image && Storage::disk('local')->exists($payment->proof_image)) {
      Storage::disk('local')->delete($payment->proof_image);
  }
  ```

---

## 4. Matriks Prioritas Perbaikan (Action Plan)

| Prioritas | Kode Celah | Komponen | Estimasi Kompleksitas | Dampak Perbaikan |
|:---:|:---|:---|:---:|:---|
| **P1** | **VULN-01** | `AdminController@reject` | Rendah (3 baris kode) | Mencegah pembeli gagal unggah ulang saat pembayaran ditolak admin. |
| **P2** | **VULN-02** | `MarketplaceController@checkout` | Rendah (1 baris kode) | Menghilangkan risiko error 500 deadlock saat event checkout massal. |
| **P3** | **VULN-04** | `MarketplaceController@order` | Rendah (2 baris kode) | Menghilangkan error 500 duplicate ticket saat refresh halaman. |
| **P4** | **VULN-03** | `MarketplaceController@order` / Catalog | Menengah | Mencegah stok terkunci permanen saat cron server cPanel bermasalah. |
| **P5** | **VULN-05** | `MarketplaceController@uploadPayment` | Rendah (3 baris kode) | Menghemat kapasitas penyimpanan (*storage disk*) hosting. |
