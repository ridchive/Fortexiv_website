### 4A. Sistem pembayaran Midtrans QRIS dinamis

- [ ] **[GAP BRD][P0] Tetapkan desain pembayaran: satu order buyer, satu transaksi Midtrans**
  - Setiap checkout—termasuk keranjang lintas kelas—membuat satu **pesanan buyer** dan satu kewajiban pembayaran ke Midtrans.
  - Grup kelas tetap hanya untuk produksi, pickup, refund, dan payout; jangan membuat buyer membayar QR terpisah per kelas.
  - Simpan snapshot item, varian, harga, jumlah, total, data pengambil, persetujuan ToS, serta batas bayar saat order dibuat.
  - Pastikan total yang dikirim ke Midtrans identik dengan total snapshot order; jika tidak sama, jangan buat transaksi.

- [ ] **[P0] Tetapkan metode integrasi QRIS dinamis**
  - Gunakan Midtrans **Core API QRIS** untuk membuat QR pembayaran unik per transaksi.
  - Jangan memakai QRIS statis/rekening manual sebagai sumber kebenaran status pembayaran.
  - Tampilkan QRIS dinamis, nominal, kode order, waktu kedaluwarsa, dan instruksi scan pada halaman pembayaran buyer.
  - Simpan referensi transaksi Midtrans, `order_id` Midtrans, `transaction_id`, nominal, tipe pembayaran, status gateway, waktu dibuat, dan waktu settlement.
  - Jangan menyimpan Server Key Midtrans di frontend, database publik, log browser, atau repository.

- [ ] **[P0] Bangun status transaksi internal dan pemetaan status Midtrans**
  - Gunakan status buyer yang mudah dipahami: `Menunggu Pembayaran`, `Dibayar`, `Gagal`, `Kedaluwarsa`, `Direfund Sebagian`, dan `Direfund Penuh`.
  - Petakan status gateway secara eksplisit: `pending` → Menunggu Pembayaran; `settlement` → Dibayar; `deny`/`cancel` → Gagal; `expire` → Kedaluwarsa.
  - Perlakukan status `capture` hanya sebagai berhasil bila aturan metode dan fraud status memang mengizinkannya.
  - Jangan mengandalkan redirect, tampilan QR, atau callback JavaScript sebagai bukti pembayaran. Webhook Midtrans yang tervalidasi adalah sumber status utama. [Webhook Midtrans](https://docs.midtrans.com/docs/https-notification-webhooks)

- [ ] **[P0] Buat endpoint webhook pembayaran Midtrans yang aman**
  - Terima notifikasi server-to-server dari Midtrans melalui endpoint khusus.
  - Verifikasi `signature_key`, `order_id`, `status_code`, dan `gross_amount` menggunakan Server Key sebelum mengubah status order.
  - Cocokkan `order_id` dan nominal notifikasi dengan transaksi internal; tolak dan catat ketidaksesuaian.
  - Buat pemrosesan idempoten: webhook yang dikirim ulang tidak boleh membuat pembayaran, QR pickup, stok, notifikasi, atau audit log ganda.
  - Simpan payload webhook secara aman untuk audit dan debugging, tanpa mengekspos secret.
  - Batasi akses endpoint secara layak, gunakan HTTPS, dan catat kegagalan validasi sebagai insiden keamanan.

- [ ] **[P0] Aktifkan pesanan untuk produksi dan pickup hanya setelah settlement**
  - Saat webhook valid menyatakan `settlement`, tandai pembayaran valid.
  - Setelah pembayaran valid, baru masukkan item ke rekap produksi seller sesuai kelasnya.
  - Setelah pembayaran valid, baru aktifkan tiket/QR pickup buyer.
  - Seller tidak boleh dapat mengubah status pembayaran atau menandai pembayaran sebagai valid.
  - Pastikan transaksi `pending`, gagal, dibatalkan, atau kedaluwarsa tidak masuk produksi dan tidak layak pickup.

- [ ] **[P0] Bangun halaman pembayaran QRIS untuk buyer**
  - Tampilkan ringkasan order, seluruh grup kelas, total, QRIS dinamis, batas bayar, status terkini, dan bantuan.
  - Sediakan tombol “Cek status pembayaran” yang meminta status terbaru ke backend; backend dapat melakukan rekonsiliasi ke Midtrans bila diperlukan.
  - Setelah buyer membayar, tampilkan keadaan “Pembayaran sedang dikonfirmasi” sampai webhook valid diterima.
  - Setelah settlement, arahkan buyer ke detail pesanan dan QR pickup.
  - Jika QR kedaluwarsa, tampilkan alasan yang jelas dan tindakan sesuai kebijakan—misalnya buat ulang pembayaran bila order masih valid.

- [ ] **[P0][KEPUTUSAN] Tetapkan masa berlaku QRIS dan batas bayar**
  - Putuskan apakah QRIS mengikuti batas bayar global event atau batas per order.
  - Tetapkan perilaku bila QRIS expired tetapi jendela PO masih terbuka: buat transaksi QRIS baru untuk order yang sama, atau wajib checkout ulang.
  - Jika membuat ulang QR, gunakan referensi transaksi Midtrans baru dan simpan relasinya dengan satu order internal.
  - Jangan pernah menganggap percobaan pembayaran yang gagal sebagai pembatalan final bila masih ada kemungkinan settlement dari percobaan lain.

- [ ] **[P0] Tangani percobaan bayar berulang dan pembayaran ganda**
  - Satu order boleh memiliki beberapa percobaan pembayaran bila belum berhasil/expired, tetapi hanya satu pembayaran sukses yang boleh diterima sebagai pembayaran order.
  - Jika terjadi pembayaran ganda, tandai sebagai exception finance; jangan menambah nilai order atau menghasilkan QR pickup tambahan.
  - Buat alur investigasi, refund kelebihan, bukti keputusan, dan audit trail.
  - Uji webhook gagal lebih dahulu lalu settlement berhasil, webhook duplikat, dan notifikasi datang tidak berurutan. Midtrans memang dapat mengirim notifikasi untuk beberapa percobaan pada satu order. [Panduan multiple payment attempts](https://docs.midtrans.com/docs/snap-advanced-feature)

- [ ] **[P0] Integrasikan kedaluwarsa order, stok, dan transaksi QRIS**
  - Bila batas bayar terlewati tanpa settlement, ubah order menjadi `Kedaluwarsa`, kembalikan stok sekali saja, dan cegah QR pickup.
  - Bila Midtrans mengirim status `expire`, sinkronkan ke order internal tanpa menunggu scheduler.
  - Scheduler internal tetap diperlukan sebagai pengaman bila notifikasi gateway terlambat/gagal.
  - Pastikan order yang sudah settlement tidak dapat dikedaluwarsakan oleh scheduler.

- [ ] **[P0] Bangun admin payment monitoring dan rekonsiliasi**
  - Admin/finance melihat daftar transaksi dengan pencarian kode order, buyer, status, nominal, waktu dibuat, dan waktu settlement.
  - Tampilkan perbedaan antara status internal dan status gateway sebagai antrean rekonsiliasi.
  - Sediakan aksi cek ulang status ke Midtrans dari backend untuk transaksi yang bermasalah.
  - Semua override manual—misalnya menandai pembayaran valid, gagal, refund, atau koreksi nominal—harus dibatasi peran, memerlukan alasan, dan masuk audit log.
  - Seller hanya melihat bahwa item kelasnya sudah dibayar/valid untuk produksi, tanpa akses detail pembayaran kelas lain.

- [ ] **[P0] Siapkan konfigurasi dan lingkungan Midtrans**
  - Pisahkan kredensial Sandbox dan Production.
  - Simpan Merchant ID, Client Key, Server Key, URL webhook, dan konfigurasi expiry sebagai secret environment yang dikelola deployment.
  - Konfigurasikan URL webhook/notification dan lakukan uji sandbox sebelum production.
  - Dokumentasikan prosedur rotasi key, pengecekan dashboard Midtrans, serta tindakan ketika webhook tidak masuk.
  - Pastikan akun Midtrans dan QRIS merchant sudah aktif untuk transaksi production sebelum PO dibuka.

- [ ] **[P0] Lengkapi pengujian pembayaran Midtrans**
  - Uji pembuatan QRIS dinamis dengan nominal yang tepat.
  - Uji status `pending` tidak membuat order layak produksi/pickup.
  - Uji `settlement` valid mengaktifkan produksi, notifikasi, dan QR pickup tepat satu kali.
  - Uji `deny`, `cancel`, dan `expire` tidak mengaktifkan pickup serta mengembalikan stok sesuai aturan.
  - Uji signature webhook tidak valid, nominal tidak cocok, `order_id` tidak ditemukan, dan webhook duplikat.
  - Uji keranjang lintas kelas: satu QRIS dibayar sekali, seller A/B hanya melihat rekap kelas masing-masing.
  - Uji pembayaran ganda, QR yang expired, retry pembayaran, serta kegagalan koneksi antara Midtrans dan aplikasi.
  - Uji end-to-end di Sandbox sebelum kredensial production dipakai.

### Penyesuaian terhadap task yang sudah ada

- [ ] **Ubah task “upload ulang bukti bayar” menjadi tidak relevan untuk alur QRIS Midtrans**
  - Hapus upload bukti transfer manual dari alur utama apabila QRIS dinamis menjadi satu-satunya metode pembayaran.
  - Pertahankan hanya bila bisnis sengaja menyediakan metode pembayaran manual sebagai fallback terpisah.
  - Jika fallback manual tetap ada, pisahkan state, UI, otorisasi finance, serta rekonsiliasinya dari transaksi Midtrans.

- [ ] **Perbarui notifikasi transaksi**
  - Ganti notifikasi “bukti bayar diverifikasi/ditolak” menjadi notifikasi `QRIS dibuat`, `pembayaran berhasil`, `pembayaran gagal`, dan `pembayaran kedaluwarsa`.
  - Notifikasi sukses hanya dikirim setelah settlement dari webhook tervalidasi, bukan saat buyer menutup halaman pembayaran atau kembali dari redirect.

- [ ] **Perbarui UAT pembayaran**
  - Tambahkan skenario buyer membayar QRIS dari perangkat lain.
  - Tambahkan skenario browser buyer ditutup sebelum/selama pembayaran, tetapi webhook settlement tetap mengaktifkan order.
  - Tambahkan skenario QRIS kedaluwarsa, retry, webhook duplikat, pembayaran ganda, dan nominal/order tidak cocok.
  - Tambahkan skenario admin/finance melakukan rekonsiliasi tanpa memberi seller kemampuan mengubah payment status.