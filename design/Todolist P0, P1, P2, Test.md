File ini hanya berdasarkan dari TODO.md, rencana-fitur-admin-seller-buyer.md dan FORTEXIV-dokumentasi-handoff-fungsional.md, Hanya perubahan struktur text agar bisa dimasukkan ke Notion

## Legenda

- **[TEMUAN]**: terbukti dari E2E/UAT atau audit kode pada dokumen `TODO.md`.
- **[GAP BRD]**: kebutuhan eksplisit di BRD/handoff tetapi belum tersedia.
- **[USULAN]**: fitur dari dokumen rencana; masih perlu persetujuan bila bukan kebutuhan BRD yang eksplisit.
- **[KEPUTUSAN]**: perlu keputusan bisnis terlebih dahulu; jangan mengunci perilaku sendiri.

## P0 — Wajib sebelum PO dibuka

### 1. Autentikasi, peran, dan akses seller

- [ ] **[TEMUAN][KEPUTUSAN] Tetapkan model registrasi seller**

  - Saat ini siapa pun dapat memilih peran seller saat registrasi dan langsung memiliki akses seller.
  - Putuskan salah satu: akun seller hanya dibuat admin, atau registrasi seller boleh tetapi wajib berstatus menunggu persetujuan.
  - Dokumentasikan penanggung jawab persetujuan dan kriteria kelayakannya.

- [ ] **[TEMUAN][GAP BRD] Batasi publikasi produk sampai seller/produk disetujui**

  - Seller saat ini dapat menerbitkan produk sendiri, padahal BRD mengharuskan produk ditinjau dan disetujui sebelum tampil publik.
  - Pastikan produk seller yang belum disetujui tidak muncul di katalog, artikel proyek, pencarian, maupun jalur pembelian.

- [ ] **[GAP BRD] Terapkan alur review produk**

  - Gunakan status: `Draft` → `Menunggu Review` → `Tayang` atau `Perlu Revisi`.
  - Ubah aksi seller dari “Publish” menjadi “Ajukan Review”.
  - Admin dapat melihat pratinjau produk, menyetujui, atau meminta revisi dengan alasan wajib.
  - Seller melihat status dan catatan revisi secara jelas.
  - Tambahkan pengujian bahwa produk belum disetujui tidak dapat dibeli atau diakses publik.

- [ ] **[KEPUTUSAN] Tetapkan aturan perubahan produk yang sudah tayang**

  - Putuskan apakah perubahan nama, harga, stok, cerita, atau media harus kembali melalui review.
  - Pastikan perubahan setelah pesanan dibuat tidak mengubah snapshot/histori pesanan buyer.

### 2. Konten proyek, artikel, dan katalog

- [ ] **[GAP BRD] Bangun CMS artikel proyek kelas untuk admin**

  - Saat ini proyek/artikel dan kategori hanya dapat diisi melalui seeder/database; admin belum dapat mengelolanya.
  - Sediakan buat, revisi, pratinjau, draft, dan publikasi artikel proyek.
  - Field minimal mengikuti BRD: kelas, angkatan bila dipakai, judul, pengantar, tujuan, cerita, proses, hasil, gambar sampul, galeri, serta status publikasi.
  - Pastikan hanya artikel yang disetujui dan dipublikasikan tampil pada jalur publik.

- [ ] **[GAP BRD] Kelola cerita/deskripsi produk yang bermakna**

  - Sediakan editor cerita produk, terpisah dari sekadar nama, harga, foto, dan stok.
  - Pastikan setiap produk publik terkait dengan kelas dan artikel proyek yang relevan.
  - Tampilkan tautan dua arah: artikel → produk terkait dan produk → artikel proyek asal.

- [ ] **[GAP BRD] Kelola kategori produk**

  - Sediakan CRUD kategori dengan slug yang konsisten.
  - Cegah penghapusan kategori yang masih digunakan produk.
  - Uji bahwa filter kelas dan kategori bekerja bersamaan sebagaimana diwajibkan BRD.

- [ ] **[KEPUTUSAN] Selesaikan konflik perilaku produk stok habis**

  - Dokumen TODO mencatat konflik: PRD meminta produk stok habis disembunyikan, sedangkan BRD/handoff meminta produk tetap terlihat dengan status “Stok habis”.
  - Aplikasi saat ini mengikuti BRD/handoff.
  - Tetapkan satu kebijakan final dan samakan katalog, detail produk, pencarian, artikel terkait, serta test case.

### 3. Pengaturan event dan jendela PO

- [ ] **[TEMUAN][GAP BRD] Pindahkan pengaturan event dari `.env` ke panel admin**

  - Saat ini jadwal PO, batas bayar, lokasi Hari H, instruksi pickup, rekening, banner, dan versi ToS masih bergantung pada `.env`.
  - Buat halaman Pengaturan Acara yang dapat dikelola admin berwenang.
  - Minimal mencakup waktu buka/tutup PO, batas bayar, tanggal/lokasi Hari H, instruksi pickup, rekening/instruksi pembayaran, pengumuman, dan versi ToS.
  - Tampilkan status yang mudah dipahami, misalnya “PO akan buka dalam 3 hari”.

- [ ] **[GAP BRD] Validasi urutan waktu event**

  - Validasi hubungan waktu: buka PO < tutup PO ≤ batas bayar < Hari H.
  - Sediakan aksi darurat “Tutup PO sekarang” dengan konfirmasi, alasan, dan audit trail.
  - Pastikan konten tetap dapat dibaca ketika PO tutup, tetapi semua tindakan pembelian baru dinonaktifkan dengan penjelasan.

- [ ] **[TEMUAN] Jalankan UAT-09 untuk periode PO**

  - Uji kondisi sebelum PO dibuka, saat PO dibuka, dan setelah PO tutup.
  - Buktikan bahwa buyer tidak dapat membuat pesanan baru ketika PO belum buka/tutup.
  - Verifikasi copy jadwal dan CTA tertutup konsisten di beranda, katalog, detail produk, keranjang, dan checkout.

### 4. Pembayaran, batas bayar, dan stok

- [ ] **[TEMUAN][GAP BRD] Kedaluwarsakan pesanan belum dibayar secara otomatis**

  - Saat ini stok berkurang ketika checkout tetapi pesanan belum dibayar tidak pernah kedaluwarsa.
  - Jalankan proses terjadwal untuk mengubah pesanan lewat batas bayar menjadi `Kedaluwarsa` dan mengembalikan stok secara aman.
  - Tampilkan pesanan yang akan kedaluwarsa dalam 24 jam pada dashboard admin.
  - Uji pengembalian stok, idempotensi proses, dan bahwa pesanan kedaluwarsa tidak memperoleh QR pickup atau masuk rekap produksi.

- [ ] **[KEPUTUSAN] Tetapkan batas bayar**

  - Putuskan apakah batas bayar bersifat tanggal global atau durasi per pesanan, misalnya 24 jam setelah checkout.
  - Tetapkan perlakuan reservasi stok dan pesanan yang sedang menunggu verifikasi pembayaran.
  - Sinkronkan keputusan ini ke copy checkout, detail pesanan, email pengingat, dan scheduler.

- [ ] **[TEMUAN][KEPUTUSAN] Tentukan alur ketika bukti bayar ditolak**

  - Saat ini bukti bayar ditolak langsung membatalkan pesanan dan mengembalikan stok.
  - Usulan: status kembali menjadi `Menunggu Pembayaran`, alasan penolakan terlihat, dan buyer dapat unggah ulang sampai batas bayar.
  - Jangan menerapkan upload ulang sebelum pemilik bisnis menyetujui kebijakan ini.
  - Jika disetujui, hanya kedaluwarsa atau keputusan admin berwenang yang dapat membatalkan pesanan sepenuhnya.

- [ ] **[USULAN] Tambahkan pembatalan mandiri untuk pesanan yang belum dibayar**

  - Buyer dapat membatalkan pesanan sebelum pembayaran valid.
  - Stok dikembalikan dan status/riwayat pembatalan tercatat.
  - Pesanan yang sudah dibayar tetap mengikuti kebijakan pembatalan/refund, bukan dibatalkan otomatis.

### 5. Notifikasi buyer dan operasional

- [ ] **[TEMUAN][GAP BRD] Implementasikan notifikasi transaksi inti**

  - Saat ini hanya email verifikasi dan reset password; belum ada notifikasi pesanan maupun pembayaran.
  - Kirim email untuk: checkout berhasil, pengingat batas bayar, pembayaran diverifikasi, pembayaran ditolak, pesanan kedaluwarsa, pengingat H-1 Hari H, grup diserahkan, dan pesanan selesai.
  - Beri admin indikator/notifikasi saat bukti bayar baru diunggah.
  - Gunakan antrean pengiriman; pengiriman email saat ini sinkron.

- [ ] **[GAP BRD] Pastikan scheduler dan worker notifikasi berjalan di lingkungan produksi**

  - Siapkan scheduler untuk kedaluwarsa pesanan dan pengingat.
  - Siapkan worker antrean untuk email/notifikasi.
  - Tambahkan pemantauan kegagalan pengiriman dan prosedur pengulangan yang aman.

- [ ] **[KEPUTUSAN] Tetapkan kanal notifikasi**

  - BRD menyebut kanal notifikasi sebagai keputusan terbuka.
  - Putuskan apakah fase awal cukup email atau membutuhkan WhatsApp/integrasi lain.
  - Tetapkan persetujuan komunikasi buyer dan informasi kontak yang boleh digunakan.

### 6. Perbaikan UX dan bahasa

- [ ] **[TEMUAN] Perbaiki pesan validasi persetujuan ToS**

  - Checkout tanpa mencentang ToS kini menampilkan pesan Inggris generik: “The accepted terms field is required.”
  - Tampilkan pesan Indonesia yang spesifik: “Setujui Terms of Service sebelum mengonfirmasi pesanan.”
  - Tambahkan pesan untuk validasi `required`, bukan hanya `accepted`.
  - Kembalikan assertion E2E/UAT agar secara eksplisit memverifikasi copy Indonesia.

- [ ] **[TEMUAN] Perbaiki pesan untuk kode pickup yang digunakan ulang**

  - Kode pickup yang seluruh grupnya sudah diserahkan kini memberi pesan seolah pesanan belum dibayar/tidak siap pickup.
  - Ubah urutan validasi agar status fulfillment diperiksa sebelum status pesanan.
  - Tampilkan pesan tepat: “Grup kelas ini sudah diserahkan.”
  - Tambahkan feature test dan perketat assertion E2E/UAT.

- [ ] **[TEMUAN][GAP BRD] Standarkan bahasa antarmuka ke Bahasa Indonesia**

  - Halaman buyer sudah Indonesia, tetapi autentikasi, seller, admin, dan sebagian validasi masih Inggris.
  - Tetapkan Bahasa Indonesia sebagai locale aplikasi dan sediakan terjemahan validasi.
  - Terjemahkan label dan aksi seperti Sign in, Create account, Verify, Reject, Disable, serta Download receipt.
  - Audit empty state, error state, loading state, notifikasi, dan email agar konsisten.

## P1 — Operasional selama PO dan Hari H

### 7. Pusat pesanan dan verifikasi pembayaran admin

- [ ] **[TEMUAN][GAP BRD] Buat pusat pesanan admin**

  - Saat ini admin tidak dapat mencari, memfilter, atau membuka detail pesanan secara memadai.
  - Sediakan pencarian berdasarkan kode pesanan, nama/email buyer, dan nomor WhatsApp.
  - Sediakan filter status, kelas, dan rentang tanggal.
  - Detail pesanan harus menampilkan grup kelas, item, pembayaran/bukti bayar, status tiap grup, histori tindakan, dan kontak pengambil.
  - Terapkan pembatasan akses agar data buyer tidak terbuka kepada seller kelas lain.

- [ ] **[USULAN] Percepat antrean verifikasi pembayaran**

  - Sediakan tab `Menunggu`, `Diverifikasi`, dan `Ditolak`.
  - Tampilkan bukti bayar secara inline bersama total yang seharusnya dibayar.
  - Tambahkan aksi “Verifikasi dan lanjut ke berikutnya”.
  - Sediakan alasan penolakan siap pakai dan catatan bebas.

### 8. Audit trail dan kontrol tindakan manual

- [ ] **[TEMUAN][GAP BRD] Implementasikan audit log tindakan material**
  - Saat ini hanya sebagian pelaku seperti `verified_by` dan `handed_over_by` yang tercatat.
  - Catat pelaku, waktu, aksi, objek, alasan, serta nilai sebelum/sesudah bila relevan.
  - Cakup verifikasi/penolakan bayar, handover/koreksi, resolusi grup, perubahan user, perubahan pengaturan event, review konten/produk, refund, dan payout.
  - Tampilkan riwayat di detail pesanan serta halaman riwayat yang dapat difilter.

### 9. Seller: produksi, stok, dan kesiapan kelas

- [ ] **[GAP BRD] Buat rekap produksi seller**

  - Tampilkan total kuantitas per produk hanya dari pesanan dengan pembayaran valid.
  - Bedakan kuantitas dibayar, menunggu verifikasi, dan sudah diserahkan.
  - Sediakan ekspor CSV atau tampilan cetak untuk kebutuhan produksi.
  - Uji seller hanya dapat melihat data kelasnya sendiri.

- [ ] **[USULAN] Perkaya dashboard kelas**

  - Tampilkan omzet terverifikasi, pesanan/grup dibayar, grup belum diambil, produk stok menipis/habis, status PO, dan Hari H.
  - Buat daftar tindakan: produk perlu revisi, stok habis, atau grup belum diserahkan.
  - Tandai angka sebelum pembayaran final sebagai belum final.

- [ ] **[USULAN] Sediakan pengelolaan stok cepat**

  - Izinkan perubahan stok dari daftar produk dengan kontrol otorisasi dan audit trail.
  - Beri peringatan stok menipis.
  - Sediakan `Arsipkan` produk sebagai alternatif mengubah stok menjadi nol untuk produk yang sudah memiliki riwayat pesanan.

- [ ] **[GAP BRD] Sediakan pelaporan insiden kelas**

  - Seller dapat melaporkan stok kurang, barang rusak, atau kendala per produk/grup.
  - Laporan masuk antrean admin untuk keputusan refund, penggantian, penyerahan parsial, atau resolusi lain.
  - Seller tidak boleh mengubah refund, status pembayaran, atau resolusi akhir sendiri.

### 10. Pickup per grup kelas dan Hari H

- [ ] **[GAP BRD] Implementasikan pencarian fallback pesanan di meja pickup**

  - Selain QR, petugas dapat mencari melalui kode order atau nama buyer sesuai kebijakan privasi.
  - Catat penggunaan fallback, pelaku, waktu, alasan, dan hasil verifikasi.
  - Jangan tampilkan detail grup kelas yang bukan kewenangan petugas.

- [ ] **[GAP BRD] Tambahkan pemindai QR kamera**

  - Sediakan pemindaian QR dengan kamera perangkat petugas, bukan hanya memasukkan kode manual.
  - Setelah scan, tampilkan konteks pesanan dan hanya grup kelas yang berwenang.
  - Pemindaian tidak boleh otomatis menjadi handover; petugas harus mengonfirmasi barang fisik telah diserahkan.

- [ ] **[TEMUAN] Jalankan UAT-45 pada pemindaian kamera perangkat nyata**

  - Uji QR di layar ponsel buyer dan hasil cetak.
  - Uji kamera ponsel petugas, izin kamera, kondisi pencahayaan, QR tidak terbaca, serta koneksi gagal.
  - Catat prosedur fallback dan rekonsiliasi agar tidak terjadi handover ganda.

- [ ] **[GAP BRD] Sediakan daftar pickup kelas**

  - Seller/petugas melihat daftar grup kelasnya: pengambil, item, kuantitas, dan status.
  - Tambahkan pencarian nama/kode dalam batas kewenangan.
  - Sediakan versi cetak sebagai cadangan operasional saat koneksi bermasalah.

- [ ] **[GAP BRD] Tambahkan koreksi handover dan resolusi per grup**

  - Admin berwenang dapat mengembalikan handover yang salah ke status pending dengan alasan wajib.
  - Sediakan resolusi per grup: pembatalan, refund, atau penyelesaian lain yang disetujui.
  - Pengembalian stok dan nominal refund hanya berlaku pada grup/kelas terdampak.
  - Pesanan buyer selesai hanya setelah seluruh grup diserahkan atau berstatus final melalui resolusi.
  - Semua koreksi dan resolusi wajib masuk audit log.

- [ ] **[KEPUTUSAN] Tetapkan desain operasional venue dan fallback identitas**

  - Putuskan: check-in QR sentral lalu menuju meja kelas, scan langsung di tiap meja kelas, atau model lain.
  - Putuskan apakah wakil buyer diperbolehkan mengambil dan bukti otorisasi yang diperlukan.
  - Tetapkan prosedur saat QR tidak terbaca, perangkat/koneksi gagal, buyer no-show, barang kurang, salah, atau rusak.

### 11. Buyer: kejelasan status dan bantuan

- [ ] **[USULAN] Tambahkan countdown dan instruksi bayar yang jelas**

  - Pada detail pesanan, tampilkan batas bayar, countdown, langkah transfer, tombol salin rekening, dan salin nominal.
  - Bedakan badge status yang memerlukan tindakan dari status informatif.
  - Sediakan filter “Perlu tindakan” pada Pesanan Saya.

- [ ] **[USULAN] Sediakan kontak dan bantuan kontekstual**

  - Tambahkan tombol WhatsApp admin yang menyertakan kode pesanan secara otomatis jika kanal tersebut disetujui.
  - Tampilkan FAQ/kebijakan yang relevan dengan status pesanan, misalnya menunggu bayar, ditolak, atau pickup.
  - Jangan membocorkan data pesanan melalui tautan bantuan.

- [ ] **[USULAN] Tingkatkan QR buyer untuk Hari H**

  - Sediakan mode layar penuh dengan kecerahan tinggi.
  - Tampilkan status per grup dan zona/meja kelas bila sudah ditetapkan.
  - Sediakan unduh gambar/PDF sebagai cadangan jika sesuai keputusan keamanan.

### 12. Pelaporan operasional

- [ ] **[GAP BRD] Sediakan laporan dan ekspor per kelas**

  - Buat laporan per periode dan per kelas: pesanan, omzet, kuantitas terjual, refund, sudah/belum diambil.
  - Sediakan ekspor pesanan, rekap produksi, dan daftar pengambil Hari H.
  - Pastikan angka laporan membedakan pesanan dibayar, belum dibayar, kedaluwarsa, direfund, dan resolved.

- [ ] **[GAP BRD] Bangun dashboard Hari H**

  - Tampilkan progress grup diserahkan vs tersisa per kelas.
  - Tampilkan daftar belum diambil untuk tindak lanjut sesuai kebijakan no-show.
  - Gunakan data real-time atau mekanisme refresh yang tidak menimbulkan handover ganda.

## P2 — Pasca-Hari H dan pengembangan lanjutan

### 13. Settlement dan payout per kelas

- [ ] **[GAP BRD] Implementasikan settlement dan payout independen per kelas**

  - Tampilkan omzet bersih per kelas: pembayaran valid dikurangi refund/penyesuaian.
  - Tampilkan status grup, isu terbuka, statement, kelayakan payout, dan riwayat aksi.
  - Izinkan kelas A yang bersih diproses tanpa menunggu masalah kelas B selesai.
  - Pastikan payout tidak mengubah status pesanan buyer.

- [ ] **[KEPUTUSAN] Tetapkan aturan finance sebelum payout dibangun**

  - Putuskan rumus payout, potongan/biaya, rekening tujuan, jadwal pencairan, bukti payout, dan pihak penyetuju.
  - Tetapkan kapan kelas dinilai bersih dan durasi periode komplain/resolusi pasca Hari H.

### 14. Peran tim dan administrasi user

- [ ] **[USULAN] Tambahkan akun petugas pickup dan finance**

  - Petugas pickup hanya dapat mengakses meja pickup/grup yang ditugaskan.
  - Finance hanya dapat mengakses verifikasi pembayaran, refund, settlement, dan payout sesuai kewenangan.
  - Uji pemisahan hak akses terhadap seller, admin, petugas pickup, dan finance.

- [ ] **[USULAN] Dukungan multi-akun untuk satu kelas**

  - Satu kelas dapat memiliki lead dan beberapa petugas pickup tanpa berbagi password.
  - Tetapkan hak tiap anggota dan jejak audit individual.

- [ ] **[USULAN] Lengkapi manajemen user**

  - Tambahkan pencarian/filter user, detail aktivitas terkait, kirim ulang verifikasi email, dan reset password manual sesuai kebijakan.
  - Pastikan pengaktifan/nonaktif user tercatat dalam audit log.

### 15. Konten dan pengalaman lanjutan

- [ ] **[GAP BRD] Kelola FAQ, ToS, bantuan, dan pengumuman dari admin**

  - Sediakan editor konten statis dengan hak akses sesuai peran.
  - Versikan ToS; perubahan harus menaikkan `terms_version`.
  - Catat versi ToS yang disetujui buyer ketika checkout.
  - Pastikan copy kebijakan konsisten di footer, checkout, detail pesanan, FAQ, dan halaman bantuan.

- [ ] **[USULAN] Tambahkan galeri produk**

  - Dukung hingga lima foto, foto utama, dan optimasi ukuran untuk mobile.
  - Tetapkan batas ukuran, format, serta review media.

- [ ] **[USULAN] Lengkapi profil buyer**

  - Sediakan perubahan nama, nomor WhatsApp, dan password.
  - Isi otomatis data pengambil saat checkout sesuai kebijakan privasi.

- [ ] **[USULAN][KEPUTUSAN] Wishlist, pengingat PO buka, dan guest checkout**

  - Wishlist/pengingat PO buka dapat dipertimbangkan setelah kanal notifikasi disetujui.
  - Guest checkout memerlukan keputusan eksplisit terkait verifikasi identitas, keamanan QR, dan pemulihan akses pesanan.

- [ ] **[USULAN] Peningkatan katalog**

  - Tambahkan urutkan produk, label hampir habis, produk terkait, serta berbagi artikel/produk.
  - Jangan mengubah prioritas pengalaman utama: artikel proyek tetap dominan, katalog tetap mudah diakses.

## Regression test dan kriteria rilis

- [ ] **[TEMUAN] Pertahankan suite E2E yang sudah lulus**

  - Saat ini E2E tercatat 73/73 lulus; jalankan kembali setelah tiap modul P0/P1 selesai.
  - Jangan menganggap catatan perbaikan selektor test sebagai bug aplikasi.

- [ ] **[TEMUAN] Tutup seluruh UAT yang masih `fixme`**

  - UAT-24: kedaluwarsa pesanan belum dibayar dan pengembalian stok.
  - UAT-25: buyer mengajukan masalah dan melihat resolusi.
  - UAT-35: review produk seller.
  - UAT-36: rekap produksi seller.
  - UAT-37: kesiapan Hari H dan insiden seller.
  - UAT-38: payout kelas A/B secara independen.
  - UAT-44: pencarian fallback pickup dan pencatatan penggunaan.
  - UAT-46: koreksi handover dan resolusi/refund per grup.
  - UAT-51: CMS artikel proyek dan cerita produk.
  - UAT-52: pengaturan PO/Hari H/lokasi oleh admin.
  - UAT-53: admin mengelola produk semua kelas.
  - UAT-54: audit log tindakan manual.
  - UAT-55: FAQ, ToS berversi, dan pengumuman.

- [ ] **[GAP BRD] Jalankan simulasi end-to-end dengan peran nyata**

  - Gunakan buyer, seller kelas A, seller kelas B, petugas pickup, admin, dan finance—bukan satu akun admin.
  - Uji order lintas kelas, pembayaran, QR, handover grup A sebelum B, QR yang dipakai ulang, fallback offline, barang kurang/rusak, refund/resolusi, serta payout kelas A saat kelas B bermasalah.
  - Catat prasyarat, langkah, hasil harapan, hasil aktual, pemilik tindak lanjut, dan keputusan lulus/tidak lulus untuk setiap skenario.

- [ ] **[GAP BRD] Tetapkan daftar keputusan terbuka sebelum penguncian rilis**

  - Metode dan konfirmasi pembayaran.
  - Batas bayar serta aturan reservasi stok.
  - Scope checkout lintas kelas pada rilis pertama.
  - Aturan QR, kedaluwarsa QR, dan fallback identitas.
  - Wakil buyer dan bukti otorisasi.
  - Model venue/pickup.
  - Pembatalan, refund, fulfilment parsial, penggantian, no-show, dan late pickup.
  - Waktu pesanan valid menjadi referensi produksi.
  - Field wajib artikel/produk dan aturan media/kategori.
  - Otoritas edit/review konten setelah publikasi atau setelah ada pesanan.
  - Rumus, otoritas, dan jadwal payout.
  - Retensi/privasi data buyer, akses seller/petugas, aksesibilitas, bahasa tambahan, dan perangkat Hari H.
