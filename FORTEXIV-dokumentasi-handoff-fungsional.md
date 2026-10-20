# FORTEXIV — Handoff Kebutuhan Fungsional Website & Operasional

**Versi:** 1.0  
**Tujuan dokumen:** arahan bersama untuk tim produk, desain, developer, admin operasional, seller/kelas, dan finance sebelum menentukan solusi teknis.  
**Bahasa operasional:** Bahasa Indonesia.  
**Batas dokumen:** kebutuhan pengalaman pengguna, konten, aturan bisnis, dan alur operasional. Dokumen ini sengaja **tidak** menetapkan database, API, framework, penyedia pembayaran, atau arsitektur teknis.

---

## 1. Ringkasan produk

FORTEXIV adalah marketplace pre-order (PO) berbasis event untuk karya dan produk kelas. Pengalaman utamanya adalah **artikel proyek kelas terlebih dahulu**: pengunjung memahami cerita, proses, dan konteks proyek sebuah kelas, lalu dapat melihat serta membeli produk yang terkait. Katalog/marketplace tetap tersedia sebagai jalur langsung untuk pengguna yang sudah ingin belanja.

Semua kelas yang ikut memakai **satu jendela PO bersama**. Tidak ada pengiriman. Pesanan yang sudah dibayar diambil pada Hari H acara. Seorang buyer boleh membeli produk dari beberapa kelas dalam satu pesanan; untuk produksi dan pengambilan, isi pesanan itu dipisah menjadi grup per seller/kelas. Satu grup dapat diserahkan secara mandiri. Pesanan buyer baru selesai apabila semua grup telah diserahkan atau memiliki penyelesaian akhir yang disetujui.

### Prinsip pengalaman

- Cerita proyek kelas bukan dekorasi: artikel proyek wajib terhubung dengan produk terkait.
- Marketplace adalah jalur sekunder, tetapi selalu mudah diakses dari navigasi utama.
- Buyer mendapat satu ringkasan pesanan dan satu kewajiban pembayaran yang jelas, meskipun membeli dari beberapa kelas.
- Operasional Hari H mengutamakan penyerahan yang akurat per buyer dan per kelas, bukan sekadar satu kali pemindaian QR.
- Dana buyer, refund, dan payout seller diperlakukan sebagai proses yang berbeda.
- Semua keputusan material dan override harus dapat ditelusuri oleh pihak yang berwenang.

---

## 2. Keputusan yang sudah dikonfirmasi

| Area | Keputusan FORTEXIV |
|---|---|
| Posisi website | Artikel proyek kelas adalah pengalaman utama; katalog/marketplace bersifat sekunder tetapi dapat diakses langsung. |
| Relasi konten | Setiap proyek kelas memiliki artikel; artikel tersebut terhubung ke satu atau lebih produk kelas tersebut. |
| Informasi produk | Produk harus memiliki cerita/deskripsi yang bermakna, bukan hanya nama, harga, dan foto. |
| Pencarian katalog | Produk dapat difilter berdasarkan **kelas** dan **kategori**. |
| Periode penjualan | Semua seller memakai satu jendela PO bersama. |
| Pemenuhan pesanan | Tidak ada pengiriman. Pengambilan dilakukan di event/Hari H. |
| Keranjang & checkout | Keranjang dapat berisi produk dari beberapa kelas; **Beli Sekarang** berlaku untuk satu produk. |
| QR pengambilan | QR mewakili pesanan buyer dan menampilkan grup per seller/kelas. |
| Penyerahan | Tiap grup seller/kelas dapat diserahkan secara independen. |
| Penyelesaian pesanan | Pesanan buyer selesai hanya jika semua grup telah diserahkan atau memperoleh resolusi akhir yang disetujui. |
| Dana & payout | Payout seller terpisah dari pesanan/transaksi buyer; satu kelas yang sudah bersih dapat dicairkan tanpa menunggu masalah kelas lain. |
| Tata kelola | Terms of Service (ToS) tersedia; pengujian berulang sebelum acara wajib dilakukan. |

---

## 3. Istilah yang dipakai secara konsisten

| Istilah | Arti operasional |
|---|---|
| **Artikel proyek** | Halaman editorial yang menceritakan proyek suatu kelas dan mengarahkan ke produk terkait. |
| **Kelas / seller** | Unit penjual resmi; dalam dokumen ini keduanya merujuk pada pemilik produk/proyek yang sama. |
| **Produk** | Barang yang dapat dipesan dari sebuah kelas melalui FORTEXIV. |
| **Jendela PO** | Periode tunggal saat pembeli boleh membuat pesanan baru. |
| **Pesanan buyer** | Satu pesanan checkout milik buyer; dapat berisi item dari beberapa kelas. |
| **Grup kelas** | Bagian operasional dari pesanan buyer yang berisi item milik satu seller/kelas. |
| **Final resolution / resolusi akhir** | Keputusan resmi untuk grup yang tidak dapat diserahkan, misalnya refund yang selesai atau pembatalan yang disetujui. |
| **Hari H** | Hari acara/pengambilan pesanan. |
| **Payout** | Pencairan hasil bersih untuk satu kelas setelah settlement kelas tersebut layak ditutup. |

### Model alur singkat

```text
Artikel proyek kelas
  -> Produk terkait
  -> Keranjang buyer (dapat lintas kelas)
  -> Satu pesanan & pembayaran buyer
  -> Grup operasional per kelas
  -> Penyerahan mandiri setiap grup pada Hari H
  -> Pesanan selesai hanya jika semua grup final
  -> Payout diproses terpisah per kelas yang sudah bersih
```

---

## 4. Aktor, tujuan, dan batas kewenangan

| Aktor | Tujuan utama | Boleh melakukan | Tidak boleh melakukan sendiri |
|---|---|---|---|
| Pengunjung | Memahami acara dan menemukan proyek/produk | Membaca artikel, katalog, FAQ, ToS | Membuat atau mengubah pesanan tanpa proses yang ditetapkan |
| Buyer | Memesan, membayar, dan mengambil produk | Mengelola pesanan miliknya, melihat QR/status, mengajukan masalah | Menetapkan pembayaran, refund, atau handover sebagai final |
| Tim seller/kelas | Menawarkan proyek/produk dan memenuhi pesanan kelasnya | Menyiapkan konten, melihat kebutuhan kelasnya, menyiapkan/menyerahkan grupnya | Melihat data kelas lain; mengubah transaksi paid atau payout sendiri |
| Admin konten/operasional | Menjaga acara berjalan tertib | Review konten, mengelola jendela PO, menangani exception, mengatur Hari H | Mencairkan payout tanpa kewenangan finance yang berlaku |
| Finance | Menjaga pencatatan dana dan settlement | Rekonsiliasi pembayaran, refund, statement, payout | Menjadikan payout satu kelas sebagai bukti semua pesanan lain selesai |
| Petugas pickup | Memastikan barang yang tepat diserahkan | Verifikasi QR/order, serah-terima grup yang menjadi tanggung jawabnya, catat masalah | Menyerahkan ulang grup yang telah tercatat diserahkan tanpa koreksi resmi |
| Sistem FORTEXIV | Menampilkan tindakan/status sesuai fase | Mencegah tindakan tidak valid, menyimpan riwayat, memberi informasi | Mengambil keputusan diskresioner terkait refund/payout |

---

## 5. Peta website dan navigasi

### 5.1 Sitemap publik dan buyer

```text
Beranda
├─ Jelajahi Proyek
│  ├─ Daftar artikel proyek
│  └─ Detail artikel proyek
│     └─ Detail produk terkait
├─ Katalog
│  └─ Detail produk
├─ Tentang Event / Cara Pengambilan
├─ FAQ
├─ Terms of Service
├─ Keranjang
├─ Checkout
├─ Pembayaran / Status pembayaran
└─ Akun Saya
   ├─ Pesanan Saya
   ├─ Detail pesanan & QR pickup
   ├─ Ajukan masalah / status resolusi
   └─ Profil
```

### 5.2 Sitemap seller/kelas

```text
Portal Seller
├─ Ringkasan kelas
├─ Artikel proyek
├─ Produk
│  ├─ Daftar / status produk
│  └─ Buat atau revisi produk
├─ Rekap PO & produksi
├─ Kesiapan Hari H
├─ Pickup grup kelas
├─ Insiden kelas
└─ Settlement & payout kelas
```

### 5.3 Sitemap admin dan operasional

```text
Portal Admin
├─ Dashboard acara
├─ Pengaturan acara & jendela PO
├─ Artikel proyek
│  ├─ Daftar / status artikel
│  └─ Buat atau revisi artikel
├─ Review produk
├─ Kelas / seller dan akses tim
├─ Pesanan & pembayaran
├─ Insiden, pembatalan, refund, dan resolusi akhir
├─ Pengaturan Hari H (zona, petugas, instruksi)
├─ Meja pickup / pencarian pesanan
├─ Settlement dan payout per kelas
├─ Konten statis (FAQ, ToS, pengumuman)
└─ Laporan & riwayat tindakan
```

### 5.4 Navigasi global

Header publik minimal menampilkan logo FORTEXIV, **Jelajahi Proyek**, **Katalog**, informasi event/FAQ, keranjang dengan indikator jumlah item, serta masuk/akun. Katalog tetap dapat dicapai dalam satu tindakan dari halaman publik mana pun. Footer memuat ToS, FAQ, informasi pengambilan, kontak bantuan, dan pengumuman kebijakan.

Saat PO telah tutup, navigasi ke konten tetap terbuka, sedangkan tombol transaksi berubah menjadi keadaan tertutup yang menjelaskan bahwa pemesanan baru tidak tersedia.

---

## 6. Spesifikasi halaman publik dan buyer

Status umum yang harus dipertimbangkan pada seluruh halaman: memuat, kosong, gagal memuat, konten tidak ditemukan, PO belum buka, PO sedang buka, dan PO telah tutup. Pesan harus menjelaskan tindakan berikutnya, bukan hanya menampilkan kode error.

### 6.1 Beranda

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memperkenalkan FORTEXIV, konteks event, dan mengarahkan pengunjung ke artikel proyek atau katalog. |
| Target | Pengunjung dan buyer. |
| Konten/section | Hero tentang event; status/jadwal PO; CTA **Jelajahi Proyek** dan **Lihat Katalog**; artikel proyek unggulan; kelas/kategori unggulan; produk pilihan; ringkasan cara pengambilan; FAQ singkat; pengumuman penting. |
| Kontrol | Buka artikel, buka katalog, filter/lihat semua, buka keranjang, masuk. |
| Tujuan navigasi | Daftar artikel, detail artikel, katalog, detail produk, FAQ, keranjang. |
| Keadaan khusus | Belum ada artikel/produk: tampilkan pesan editorial yang ramah tanpa ruang kosong. PO tutup: jadwal tetap terlihat, CTA belanja menjadi informasi PO tutup. Gagal muat: sediakan coba lagi. |
| Pemilik konten | Admin konten/operasional; kelas menyediakan materi yang telah disetujui. |
| Aturan bisnis | Artikel proyek mendapat penempatan lebih dominan dari produk; katalog tidak disembunyikan. Jadwal yang tampil berasal dari pengaturan acara yang disetujui. |
| Kriteria penerimaan | Pengunjung dapat memahami apa itu FORTEXIV, batas PO, dan cara pengambilan tanpa masuk akun; kedua jalur artikel dan katalog dapat diakses jelas. |

### 6.2 Daftar artikel proyek

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjadi pintu utama untuk menemukan proyek kelas melalui narasi. |
| Target | Pengunjung dan buyer. |
| Konten/section | Judul/penjelasan halaman; daftar kartu artikel berisi gambar, kelas, judul, ringkasan, kategori/tema bila digunakan, dan tautan baca; filter/penyortiran yang benar-benar tersedia. |
| Kontrol | Filter kelas/tema (jika disetujui), cari bila tersedia, buka artikel, reset filter. |
| Tujuan navigasi | Detail artikel, katalog dengan konteks filter yang sesuai bila relevan. |
| Keadaan khusus | Hasil filter kosong: jelaskan tidak ada proyek yang cocok dan tawarkan reset filter. Memuat: placeholder/skeleton. Gagal: tombol muat ulang. |
| Pemilik konten | Admin yang menyuusun dan membuat artikel |
| Aturan bisnis | Hanya artikel yang disetujui dan dipublikasikan yang terlihat publik. Artikel boleh tetap dibaca setelah PO tutup. |
| Kriteria penerimaan | Setiap kartu mengidentifikasi kelas dan mengarah ke satu artikel yang benar; filter tidak menampilkan konten yang tidak sesuai. |

### 6.3 Detail artikel proyek

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menceritakan nilai/proses proyek kelas dan menghubungkannya secara jelas ke produk. |
| Target | Pengunjung dan buyer. |
| Konten/section | Judul, kelas, penulis/tim bila diperbolehkan, gambar/galeri, cerita proyek, proses/inspirasi, informasi yang relevan, produk terkait, CTA ke katalog/produk, jadwal PO dan pickup ringkas. |
| Kontrol | Buka produk terkait, tambah produk ke keranjang dari kartu (bila varian tidak perlu dipilih), bagikan jika diputuskan, kembali ke daftar proyek. |
| Tujuan navigasi | Detail produk, katalog dengan filter kelas, daftar artikel, keranjang. |
| Keadaan khusus | Tidak ada produk terkait: artikel tetap dapat dibaca dan menjelaskan bahwa produk belum/ tidak tersedia. PO tutup: produk tetap dapat dilihat, pembelian dinonaktifkan dengan alasan. Artikel tidak ditemukan/tidak publik: halaman aman dengan jalan kembali. |
| Pemilik konten | Admin yang akan mengubah dan mengurusi artikel, bukan tim kelas |
| Aturan bisnis | Produk yang ditampilkan harus milik kelas/proyek terkait. Perubahan artikel setelah ada pesanan tidak boleh mengubah fakta pesanan buyer. |
| Kriteria penerimaan | Buyer dapat mengetahui kelas pemilik proyek dan menemukan seluruh produk terkait; artikel tidak berubah menjadi halaman katalog kosong. |

### 6.4 Katalog

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberi jalur belanja langsung untuk buyer yang sudah mengetahui kebutuhan produk. |
| Target | Pengunjung dan buyer. |
| Konten/section | Judul, status PO, filter **kelas** dan **kategori**, daftar produk, ringkasan filter aktif, tautan ke artikel proyek asal produk. Filter tambahan hanya dimasukkan bila telah dikonfirmasi. |
| Kontrol | Pilih/hapus filter kelas dan kategori, reset filter, buka detail produk, tambah ke keranjang jika aturan produk memungkinkan. |
| Tujuan navigasi | Detail produk, artikel proyek, keranjang. |
| Keadaan khusus | Hasil filter kosong; produk habis/tidak tersedia; PO tertutup; loading; gagal memuat. Produk dari kelas yang tidak aktif tidak boleh muncul sebagai dapat dibeli. |
| Pemilik konten | Produk dari tim kelas; status publikasi/partisipasi dikelola admin. |
| Aturan bisnis | Filter kelas dan kategori wajib berfungsi bersamaan. Katalog adalah jalur sekunder secara presentasi, tetapi tidak dibatasi aksesnya. |
| Kriteria penerimaan | Buyer dapat memfilter minimal menurut kelas dan kategori, membuka produk yang tepat, serta kembali ke cerita proyeknya. |

### 6.5 Detail produk

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberikan informasi cukup agar buyer dapat memutuskan pembelian dengan sadar. |
| Target | Pengunjung dan buyer. |
| Konten/section | Nama, visual, harga, pilihan/varian jika ada, ketersediaan sesuai aturan, jumlah, **cerita/deskripsi produk**, kelas pemilik, tautan ke artikel proyek, informasi PO, informasi pickup Hari H, kebijakan penting, produk terkait bila ada. |
| Kontrol | Pilih varian, atur jumlah, **Tambah ke Keranjang**, **Beli Sekarang**, buka artikel proyek, kembali ke katalog. |
| Tujuan navigasi | Keranjang, checkout Beli Sekarang, artikel proyek, katalog. |
| Keadaan khusus | Varian belum dipilih: CTA menjelaskan data yang kurang. Produk tidak tersedia/PO tertutup: tindakan membeli nonaktif dan alasan terlihat. Produk dihapus/tidak publik: tidak dapat dibeli. Loading/error/not found ditangani jelas. |
| Pemilik konten | Tim kelas menyusun; admin menyetujui sesuai kebijakan. |
| Aturan bisnis | Beli Sekarang membuat alur checkout untuk **satu produk ini**, sedangkan Tambah ke Keranjang dapat bergabung dengan produk kelas lain. Harga/deskripsi yang tampil saat checkout harus diverifikasi ulang. |
| Kriteria penerimaan | Buyer dapat mengetahui kelas, cerita, harga, dan cara pengambilan sebelum memilih beli; kedua CTA menghasilkan perilaku yang berbeda tetapi jelas. |

### 6.6 Keranjang

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Membantu buyer meninjau item dari satu atau banyak kelas sebelum checkout. |
| Target | Buyer; pengunjung dapat diminta masuk pada titik yang diputuskan. |
| Konten/section | Item dikelompokkan secara visual per kelas; produk/varian/jumlah/harga/subtotal; ringkasan total; jadwal PO/pickup; tautan ToS; pemberitahuan perubahan item yang relevan. |
| Kontrol | Ubah jumlah, hapus item, kembali belanja, lanjut checkout. |
| Tujuan navigasi | Katalog/detail produk, checkout, ToS. |
| Keadaan khusus | Kosong: CTA ke proyek dan katalog. Item menjadi tidak tersedia/PO tertutup: beri penjelasan dan cegah checkout item tidak valid. Harga/varian berubah: minta review sebelum checkout. Loading/error: jangan menampilkan total yang tidak pasti sebagai final. |
| Pemilik konten | Sistem menampilkan snapshot keranjang; produk dikelola seller/admin. |
| Aturan bisnis | Keranjang boleh lintas kelas. Struktur grup kelas ditampilkan sebagai transparansi operasional, bukan pesanan terpisah. Validasi final terjadi saat checkout. |
| Kriteria penerimaan | Buyer dapat membedakan item tiap kelas, mengubah keranjang, dan mengetahui bahwa pengambilan dapat terjadi per kelas. |

### 6.7 Checkout

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Mengonfirmasi pesanan dan persetujuan buyer sebelum kewajiban pembayaran dibuat. |
| Target | Buyer yang siap membeli. |
| Konten/section | Data buyer/pengambil sesuai kebijakan; ringkasan semua item dan grup kelas; total; batas PO dan batas bayar; tanggal/lokasi/instruksi pickup; ToS dan kebijakan pembatalan/refund; metode/instruksi pembayaran yang telah ditentukan. |
| Kontrol | Koreksi data, kembali ke keranjang, centang persetujuan wajib, buat pesanan/lakukan pembayaran sesuai alur yang disetujui. |
| Tujuan navigasi | Keranjang, ToS, status pembayaran, detail pesanan. |
| Keadaan khusus | PO tepat telah tutup: jangan membuat pesanan baru; tampilkan penjelasan. Ada item tidak valid: tunjukkan item penyebab dan arahkan revisi. Gagal membuat pesanan: jangan membiarkan buyer mengira pesanan berhasil; tawarkan coba lagi tanpa duplikasi. |
| Pemilik konten | Admin/finance untuk copy kebijakan dan instruksi; sistem untuk ringkasan order. |
| Aturan bisnis | ToS harus dapat diakses dan persetujuan dicatat sebelum buyer melanjutkan. Satu checkout membuat satu pesanan buyer yang dapat berisi banyak grup kelas. |
| Kriteria penerimaan | Buyer tidak dapat menyelesaikan checkout tanpa menyetujui ToS; jadwal pickup dan pemisahan grup kelas terlihat sebelum pembayaran. |

### 6.8 Pembayaran dan status pembayaran

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberi instruksi pembayaran yang jelas dan status yang dapat dipercaya. |
| Target | Buyer. |
| Konten/section | Nomor/ringkasan pesanan, total, instruksi/metode bayar yang disetujui, batas pembayaran, status pembayaran, bantuan bila gagal, konsekuensi bila tidak bayar. |
| Kontrol | Lanjutkan pembayaran sesuai metode, perbarui status bila relevan, kembali ke detail pesanan, hubungi bantuan. |
| Tujuan navigasi | Detail pesanan, FAQ/dukungan. |
| Keadaan khusus | Menunggu konfirmasi, gagal, kedaluwarsa, dibayar, refund penuh/sebagian bila terjadi. Pada keadaan gagal/expired, buyer tidak boleh menerima QR pickup sebagai pesanan layak ambil. |
| Pemilik konten | Finance/admin menentukan instruksi dan kebijakan; sistem menyajikan status. |
| Aturan bisnis | Konfirmasi pembayaran bukan keputusan seller. Detail exact metode dan tenggat pembayaran adalah **keputusan terbuka** sampai ditetapkan. |
| Kriteria penerimaan | Buyer selalu dapat melihat status terakhir dan tindakan berikutnya; status belum dibayar tidak tampak sebagai pesanan siap pickup. |

### 6.9 Pesanan Saya

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjadi pusat self-service buyer setelah checkout. |
| Target | Buyer yang masuk akun. |
| Konten/section | Daftar pesanan dengan nomor, waktu, total, status ringkas, jumlah grup, dan CTA detail; filter status bila diperlukan. |
| Kontrol | Buka detail, lanjut bayar, ajukan masalah sesuai kebijakan. |
| Tujuan navigasi | Detail pesanan, pembayaran, bantuan. |
| Keadaan khusus | Belum ada pesanan: CTA jelajahi proyek/katalog. Loading/error aman. Pesanan lama tetap menampilkan riwayat sesuai kebijakan retensi. |
| Pemilik konten | Sistem; buyer hanya melihat miliknya. |
| Aturan bisnis | Data pesanan buyer lain tidak pernah terlihat. Status ringkas tidak boleh menyembunyikan grup yang masih tertunda. |
| Kriteria penerimaan | Buyer dapat menemukan pesanan, membedakan yang butuh tindakan, dan menuju detail tanpa melihat data pihak lain. |

### 6.10 Detail pesanan & QR pickup

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjelaskan secara pasti apa yang telah dibayar, apa yang harus diambil, dan status setiap grup kelas. |
| Target | Buyer; tampilan staf yang sesuai hak akses dapat memakai informasi operasional serupa. |
| Konten/section | Nomor order; pembayaran; QR pickup saat eligible; daftar grup per kelas berisi item, status, lokasi/zona/jika tersedia, dan instruksi; total; jadwal; riwayat tindakan/resolusi yang relevan; kanal masalah. |
| Kontrol | Tampilkan QR, buka petunjuk pickup, ajukan masalah, lanjutkan pembayaran bila masih valid. |
| Tujuan navigasi | Halaman bantuan, status pembayaran, detail resolusi. |
| Keadaan khusus | Belum dibayar: QR tidak aktif/tidak tersedia. Sebagian diserahkan: grup selesai dan grup tersisa dibedakan tegas. Semua final: order diberi status selesai. QR gagal ditampilkan: sediakan order ID untuk fallback. |
| Pemilik konten | Sistem; lokasi/instruksi dari admin; status handover dari staf yang berwenang. |
| Aturan bisnis | Satu QR mewakili pesanan buyer, bukan izin sekali pakai untuk semua barang sekaligus. Handover satu grup tidak menyelesaikan grup lain. |
| Kriteria penerimaan | Buyer dapat mengetahui tepat kelas mana yang masih harus diambil dan tidak dapat menganggap order selesai hanya karena satu grup sudah diserahkan. |

### 6.11 Halaman informasi: Tentang Event, Cara Pengambilan, FAQ, dan ToS

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjawab pertanyaan sebelum transaksi dan mengurangi kebingungan Hari H. |
| Target | Semua pengguna. |
| Konten/section | Informasi acara; waktu/lokasi pickup; langkah penggunaan QR; kebijakan wakil pengambil bila disetujui; FAQ; ToS; kebijakan pembatalan/refund/no-show yang sudah final. |
| Kontrol | Cari/tautan topik, buka dokumen terkait, kembali ke beranda/katalog. |
| Tujuan navigasi | Beranda, checkout, bantuan. |
| Keadaan khusus | Bila kebijakan belum final, jangan tampilkan asumsi sebagai kebijakan. Tampilkan status/pengumuman admin yang disetujui. |
| Pemilik konten | Admin operasional/legal/penanggung jawab yang ditentukan. |
| Aturan bisnis | ToS tersedia sebelum checkout dan riwayat versi/persetujuannya dapat dibuktikan sesuai kebutuhan bisnis. |
| Kriteria penerimaan | Buyer dapat menemukan instruksi pickup dan ToS dari footer maupun checkout; informasi tidak bertentangan dengan halaman checkout/order. |

---

## 7. Spesifikasi portal seller/kelas

Portal seller hanya memperlihatkan data kelas yang ditugaskan kepada pengguna tersebut. Tampilan dan tindakan yang menyentuh pesanan paid, refund, atau payout harus tunduk pada kewenangan admin/finance.

### 7.1 Ringkasan kelas

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjadi halaman awal operasional kelas. |
| Target | Seller lead dan anggota seller berwenang. |
| Konten/section | Status artikel/produk, jadwal PO, indikator kebutuhan tindakan, rekap paid/confirmed yang diizinkan, kesiapan Hari H, insiden terbuka, status settlement/payout kelas. |
| Kontrol | Buka konten, rekap produksi, kesiapan, pickup, insiden, settlement sesuai hak. |
| Keadaan khusus | Tidak ada kelas yang ditugaskan: jelaskan bahwa akses belum diberikan. PO tutup: fokus bergeser ke rekap produksi. |
| Pemilik konten | Sistem; data berasal dari kelas/admin/finance. |
| Aturan bisnis | Angka hanya untuk kelas sendiri; status payout tidak boleh dianggap sebagai status semua buyer order. |
| Kriteria penerimaan | Seller segera tahu tindakan berikutnya dan tidak melihat data kelas lain. |

### 7.2 Artikel proyek: daftar, buat, revisi

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memungkinkan kelas menyusun cerita proyek untuk review admin. |
| Target | Seller lead/anggota yang diizinkan. |
| Konten/section | Daftar artikel dan status; editor berisi judul, ringkasan, isi cerita, visual, hubungan ke produk kelas, serta catatan review. Field final mengikuti keputusan konten. |
| Kontrol | Simpan draft, ajukan review, revisi, pratinjau bila disetujui, lihat alasan penolakan/revisi. |
| Tujuan navigasi | Daftar produk, preview artikel, dashboard. |
| Keadaan khusus | Belum ada artikel: CTA buat artikel. Needs revision: alasan admin harus jelas. Saat PO terbuka/pernah ada pesanan, perubahan material mengikuti review ulang. |
| Pemilik konten | Tim kelas menyusun; admin konten menyetujui/publikasi. |
| Aturan bisnis | Artikel publik hanya setelah persetujuan. Artikel harus dapat menghubungkan produk dari kelas sendiri saja. |
| Kriteria penerimaan | Kelas dapat mengirim artikel lengkap dan menerima alasan review; pengunjung tidak melihat draft/versi tidak disetujui. |

### 7.3 Produk: daftar, buat, revisi

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Mengelola daftar produk yang melekat pada proyek kelas. |
| Target | Seller berwenang. |
| Konten/section | Daftar produk dan status; form nama, kategori, gambar, harga, opsi/varian bila dipakai, deskripsi/cerita, ketersediaan sesuai keputusan, tautan artikel proyek, dan informasi wajib yang ditetapkan admin. |
| Kontrol | Simpan draft, ajukan review, revisi, nonaktifkan sesuai kewenangan, lihat catatan review. |
| Tujuan navigasi | Artikel terkait, rekap produksi, dashboard. |
| Keadaan khusus | Produk belum ada; produk perlu revisi; produk pernah dipesan; PO tertutup. Perubahan material setelah ada order tidak boleh mengubah snapshot order dan harus melalui aturan exception/review. |
| Pemilik konten | Tim kelas; admin review/publikasi. |
| Aturan bisnis | Setiap produk milik satu kelas dan terhubung ke artikel proyek yang relevan. Produk tidak tampil publik sebelum disetujui/dipublikasikan. |
| Kriteria penerimaan | Produk publik selalu memiliki kelas, kategori, deskripsi/cerita, dan artikel proyek yang benar; seller tak bisa mengubah pesanan paid melalui edit produk. |

### 7.4 Rekap PO & produksi

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberi referensi produksi yang akurat bagi satu kelas. |
| Target | Seller/kelas. |
| Konten/section | Ringkasan item/varian/jumlah yang valid, catatan yang diizinkan, status pembayaran yang relevan, waktu pembaruan, dan penanda bahwa angka final berlaku setelah fase yang ditetapkan. |
| Kontrol | Filter produk/varian, ekspor/cetak bila disetujui, buka insiden, tandai kesiapan sesuai hak. |
| Tujuan navigasi | Detail insiden, kesiapan Hari H. |
| Keadaan khusus | PO masih buka: angka diberi label belum final. Tidak ada pesanan valid: tampilkan nol secara eksplisit. Gagal memuat: jangan digunakan sebagai dasar produksi tanpa peringatan. |
| Pemilik konten | Sistem berdasarkan pesanan/payment yang valid; admin menetapkan fase final. |
| Aturan bisnis | Pesanan belum bayar, gagal, atau tidak valid tidak boleh masuk referensi produksi final. Seller hanya melihat produk/grup kelasnya. |
| Kriteria penerimaan | Setelah PO dan pembayaran dikunci sesuai kebijakan, seller memperoleh total yang konsisten dengan order paid untuk kelasnya. |

### 7.5 Kesiapan Hari H

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Membantu kelas menyatakan kesiapan atau melaporkan risiko lebih awal. |
| Target | Seller lead/anggota yang diizinkan. |
| Konten/section | Checklist kesiapan; zona/meja bila sudah ditetapkan; ringkasan grup; status siap/belum siap; catatan risiko; kontak eskalasi. |
| Kontrol | Tandai kesiapan, laporkan kekurangan/masalah kualitas, buka panduan Hari H. |
| Tujuan navigasi | Insiden kelas, rekap produksi, pickup. |
| Keadaan khusus | Belum ada zona: beri status menunggu pengaturan admin. Risiko terbuka: jangan izinkan ditutup secara diam-diam. |
| Pemilik konten | Seller mengisi; admin memantau dan mengambil tindakan. |
| Aturan bisnis | Kekurangan atau masalah kualitas dilaporkan sebagai insiden, bukan dengan mengubah pesanan buyer secara sepihak. |
| Kriteria penerimaan | Admin dapat mengetahui kelas yang berisiko sebelum Hari H; seller memiliki jalur eskalasi yang jelas. |

### 7.6 Pickup grup kelas

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Mendukung serah-terima kelompok pesanan yang menjadi tanggung jawab kelas. |
| Target | Petugas/seller yang ditetapkan. |
| Konten/section | Pencarian/scan yang diizinkan; identitas operasional minimum; item grup kelas sendiri; status grup; instruksi verifikasi; riwayat handover; tombol konfirmasi setelah barang benar-benar diberikan. |
| Kontrol | Scan/cari order, buka grup yang sesuai, konfirmasi handover, buat insiden, eskalasi. |
| Tujuan navigasi | Detail pickup, insiden, dashboard Hari H. |
| Keadaan khusus | QR tidak terbaca; grup sudah diserahkan; grup bukan milik kelas; belum dibayar; koneksi bermasalah; barang kurang/salah. Semua memiliki jalur exception, bukan tombol handover paksa. |
| Pemilik konten | Status dan catatan dikelola petugas berwenang; admin mengatur akses. |
| Aturan bisnis | Petugas hanya dapat menyerahkan grup kelasnya. Handover dicatat setelah barang fisik diberikan; grup tak dapat diserahkan dua kali tanpa koreksi berotorisasi. |
| Kriteria penerimaan | Handover satu grup memperbarui grup tersebut tanpa menutup grup kelas lain dalam pesanan yang sama. |

### 7.7 Insiden kelas dan settlement/payout

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberi visibilitas atas masalah dan hasil settlement kelas tanpa menyamakan keduanya dengan status order buyer. |
| Target | Seller lead; finance/admin sesuai hak. |
| Konten/section | Daftar insiden kelas dan statusnya; ringkasan settlement; komponen penyesuaian yang telah disetujui; status payout; statement yang tersedia. |
| Kontrol | Laporkan/tanggapi insiden sesuai hak, lihat statement, ajukan pertanyaan ke finance. |
| Tujuan navigasi | Detail insiden, dashboard kelas. |
| Keadaan khusus | Kelas belum layak payout: alasan dan tindakan/penanggung jawab tampil. Payout selesai: riwayat tetap ada. |
| Pemilik konten | Admin untuk keputusan insiden; finance untuk settlement/payout. |
| Aturan bisnis | Payout satu kelas dapat diproses bila kelas itu bersih, tanpa menunggu kelas lain. Seller tidak menyetujui payoutnya sendiri. |
| Kriteria penerimaan | Seller dapat membedakan grup/order bermasalah dari status payout kelas, serta mengetahui mengapa payout ditahan bila belum layak. |

---

## 8. Spesifikasi portal admin, finance, dan pickup

### 8.1 Dashboard acara

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memberi gambaran kendali operasional dari pra-PO sampai settlement. |
| Target | Admin berwenang; finance melihat bagian sesuai hak. |
| Konten/section | Status PO, artikel/produk menunggu review, ringkasan order/payment, kesiapan kelas, insiden, progres pickup, grup belum diambil, dan kelas layak/tertahan payout. |
| Kontrol | Buka area tindakan/detail, filter periode/kelas sesuai hak. |
| Keadaan khusus | Belum ada acara/data; data terlambat; error harus diberi timestamp pembaruan terakhir. |
| Pemilik konten | Sistem/agregasi operasional. |
| Aturan bisnis | Dashboard tidak menggantikan detail/audit untuk keputusan material. |
| Kriteria penerimaan | Admin dapat mengidentifikasi pekerjaan mendesak tanpa melihat data yang tidak diizinkan. |

### 8.2 Pengaturan acara & jendela PO

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menetapkan sumber kebenaran jadwal dan aturan acara. |
| Target | Admin tertentu. |
| Konten/section | Nama/identitas event, pembukaan/penutupan PO, batas bayar, Hari H, lokasi, instruksi pickup, kelas peserta, pengumuman, serta status fase acara. |
| Kontrol | Simpan draft, jadwalkan/buka/tutup sesuai otorisasi, ubah dengan alasan, kirim/siapkan pengumuman. |
| Keadaan khusus | Perubahan sesudah PO buka atau setelah ada order harus memperingatkan dampak dan meminta alasan/komunikasi; detail kebijakan perubahan adalah keputusan terbuka. |
| Pemilik konten | Admin operasional. |
| Aturan bisnis | Ada satu jendela PO yang berlaku untuk seluruh kelas peserta. Waktu yang ditampilkan website berasal dari pengaturan ini. |
| Kriteria penerimaan | Sistem menolak pembukaan transaksi di luar jendela PO; perubahan jadwal meninggalkan riwayat yang dapat diperiksa. |

### 8.3 Review dan Pengelolaan Artikel/Proyek

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjaga kelengkapan, relevansi, konsistensi, dan kualitas artikel proyek sebelum dan sesudah publikasi. |
| Target | Admin konten/operasional. |
| Konten/section | Daftar artikel; editor artikel; judul; gambar sampul; ringkasan; isi artikel; informasi kelas; dokumentasi proyek; produk terkait; status publikasi; dan pratinjau halaman. |
| Kontrol | Membuat, melihat, mengedit, menyimpan draft, menerbitkan, mengubah status publikasi, dan mengarsipkan artikel. |
| Keadaan khusus | Artikel yang sudah memiliki produk atau transaksi terkait harus dapat diperbarui tanpa mengubah riwayat transaksi. Artikel yang tidak lagi relevan dapat diarsipkan sesuai kebijakan. |
| Pemilik konten | Admin bertanggung jawab atas pembuatan, penyuntingan, dan pengelolaan artikel. |
| Aturan bisnis | Hanya Admin yang berwenang mengelola artikel. Artikel berstatus draft tidak ditampilkan kepada publik. Hanya artikel yang telah diterbitkan yang dapat diakses publik. |
| Kriteria penerimaan | Admin dapat membuat dan mengelola artikel secara mandiri, menghubungkan artikel dengan kelas dan produk terkait, serta mengatur status publikasi artikel. |

### 8.4 Pengelolaan dan Review Produk

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memastikan informasi produk lengkap, relevan, dan sesuai ketentuan sebelum ditampilkan kepada Buyer. |
| Target | Seller dan Admin operasional. |
| Konten/section | Daftar produk; nama produk; foto; deskripsi/cerita produk; harga; stok; kategori; kelas pemilik; status produk; antrean review; dan catatan revisi. |
| Kontrol Seller | Menambahkan produk, mengedit informasi produk miliknya, mengatur harga dan stok, mengajukan produk untuk review, serta melihat status dan catatan review. |
| Kontrol Admin | Memeriksa produk, menyetujui, meminta revisi, menolak, serta mengatur publikasi atau penarikan produk sesuai kewenangan. |
| Keadaan khusus | Perubahan material pada produk yang sudah memiliki pesanan harus ditangani dengan hati-hati dan tidak boleh diam-diam mengubah transaksi historis. |
| Pemilik konten | Seller bertanggung jawab atas informasi produk miliknya. Admin bertanggung jawab atas proses review dan keputusan publikasi produk. |
| Aturan bisnis | Seller hanya dapat mengelola produknya sendiri. Produk yang belum disetujui tidak ditampilkan sebagai produk publik yang dapat dibeli. Alasan revisi atau penolakan wajib disampaikan kepada Seller agar dapat ditindaklanjuti. |
| Kriteria penerimaan | Seller dapat mengelola produk miliknya dan melihat status review beserta alasannya. Admin dapat memeriksa produk, menentukan keputusan review, dan memastikan produk yang dipublikasikan memiliki informasi lengkap serta terhubung dengan kelas yang benar. |

### 8.5 Pesanan, pembayaran, pembatalan, refund, dan resolusi

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menangani siklus pesanan dan exception dengan jejak keputusan yang jelas. |
| Target | Admin dan finance sesuai kewenangan. |
| Konten/section | Pencarian order; detail buyer order; grup kelas; status payment; item; riwayat; permintaan pembatalan/refund; insiden; keputusan; nominal/resolusi; komunikasi buyer bila relevan. |
| Kontrol | Verifikasi sesuai peran, setujui/tolak permintaan, pilih resolusi, tandai refund diproses/selesai sesuai kewenangan, override terbatas dengan alasan. |
| Keadaan khusus | Satu grup bermasalah: grup lain tetap dapat diteruskan/handover. Pembayaran ganda/invalid/expired ditangani terpisah. |
| Pemilik konten | Sistem mencatat; admin/finance mengelola keputusan. |
| Aturan bisnis | Refund hanya memengaruhi kelas/grup yang terdampak. Resolusi akhir grup harus eksplisit sebelum pesanan buyer dapat selesai bila grup tidak diserahkan. Semua tindakan manual memerlukan alasan dan pelaku. |
| Kriteria penerimaan | Admin dapat melihat alasan dan riwayat status; penyelesaian satu grup tidak otomatis membatalkan atau menutup grup lain. |

### 8.6 Pengaturan dan meja Hari H

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menerjemahkan rencana event ke instruksi operasional yang dapat digunakan petugas. |
| Target | Admin operasional/pickup lead. |
| Konten/section | Zona/meja, kelas penanggung jawab, petugas yang ditugaskan, jam/slot bila dipakai, prosedur fallback, kontak eskalasi, pengumuman Hari H. |
| Kontrol | Tetapkan/tukar petugas sesuai hak, aktifkan akses pickup, cetak/lihat panduan, buka daftar exception. |
| Keadaan khusus | Zona belum diputuskan: status jelas, jangan mengarang lokasi pada order buyer. Koneksi bermasalah: akses ke prosedur fallback yang disetujui. |
| Pemilik konten | Admin operasional. |
| Aturan bisnis | Rancangan venue—cek QR sentral lalu ke meja kelas, atau scan per meja kelas—masih **keputusan terbuka**. Sistem harus mendukung alur yang dipilih tanpa mengubah prinsip grup mandiri. |
| Kriteria penerimaan | Petugas mengetahui kelas/grup apa yang menjadi kewenangannya dan cara eskalasi exception. |

### 8.7 Layar pickup / pencarian pesanan

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memastikan verifikasi dan serah-terima yang cepat, akurat, dan dapat ditelusuri. |
| Target | Petugas pickup/seller yang diberi akses. |
| Konten/section | Area scan/cari, status validasi, grup yang berwenang, item/kuantitas, status handover, instruksi verifikasi, tombol serahkan setelah konfirmasi fisik, serta jalur exception. |
| Kontrol | Scan QR, cari melalui fallback resmi, pilih grup berwenang, konfirmasi handover, buat insiden/eskalasi. |
| Keadaan khusus | QR tidak terbaca/tidak ditemukan, QR/order sudah tercatat, group bukan kewenangan petugas, payment belum valid, item salah/kurang/rusak, konektivitas gagal. |
| Pemilik konten | Data sistem; handover oleh petugas; koreksi oleh admin berwenang. |
| Aturan bisnis | QR membuka konteks buyer order dan grup; pemindaian saja bukan handover. Sistem mencegah penyerahan ganda pada grup sama. |
| Kriteria penerimaan | Staf dapat menyerahkan satu grup yang tepat, mencatat waktu/petugas/lokasi sesuai kebutuhan, dan tidak dapat mengulang handover tanpa otorisasi. |

### 8.8 Settlement dan payout per kelas

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Mengelola pencairan kelas secara independen setelah kewajiban kelas tersebut jelas. |
| Target | Finance/admin berwenang; seller dapat membaca miliknya sesuai keputusan. |
| Konten/section | Daftar kelas, status kelayakan, penjualan yang relevan, refund/penyesuaian yang disetujui, issue terbuka, statement, status payout, riwayat tindakan. |
| Kontrol | Tinjau, tandai layak/tahan dengan alasan, buat/unggah statement bila disetujui, tandai payout diproses/selesai sesuai kewenangan. |
| Keadaan khusus | Ada issue terbuka di kelas A: tahan/atur kelas A saja; kelas B yang bersih tetap dapat diproses. |
| Pemilik konten | Finance; admin menyumbang status resolusi operasional. |
| Aturan bisnis | Payout bukan perubahan status order buyer. Rumus, biaya, metode, jadwal, dan otoritas final payout adalah **keputusan terbuka**. |
| Kriteria penerimaan | Finance dapat membuktikan alasan payout/tahan per kelas; masalah kelas lain tidak memblokir kelas yang bersih. |

### 8.9 Konten statis, laporan, dan riwayat tindakan

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menjaga informasi publik konsisten dan keputusan kritis dapat ditelusuri. |
| Target | Admin konten/operasional/finance sesuai hak. |
| Konten/section | FAQ, ToS, informasi pickup, pengumuman, laporan operasional/settlement, dan riwayat tindakan material. |
| Kontrol | Buat/revisi/publikasi konten sesuai hak, ekspor laporan yang diizinkan, filter riwayat. |
| Keadaan khusus | Konten kebijakan lama tetap dapat ditelusuri bila diperlukan; laporan tanpa hak akses tidak dapat dibuka. |
| Pemilik konten | Admin/finance sesuai area. |
| Aturan bisnis | Riwayat minimal mencatat tindakan, pelaku, waktu, alasan, dan objek untuk perubahan payment, handover, refund, resolusi, serta payout. |
| Kriteria penerimaan | Informasi publik konsisten dengan checkout; tindakan kritis dapat ditelusuri tanpa mengekspos data berlebih. |

---

## 9. Alur buyer end-to-end

1. Buyer tiba dari beranda, artikel proyek, atau langsung ke katalog.
2. Buyer membaca cerita proyek atau detail produk, termasuk kelas pemilik, PO, dan pickup.
3. Buyer memilih produk. **Tambah ke Keranjang** mempertahankan keranjang lintas kelas; **Beli Sekarang** mengarahkan checkout untuk satu produk tersebut.
4. Buyer meninjau keranjang yang dikelompokkan per kelas dan melanjutkan checkout.
5. Buyer melihat semua grup kelas, jadwal/aturan pickup, ToS, dan total sebelum menyetujui checkout.
6. Sistem membuat satu pesanan buyer dan membentuk grup operasional per kelas.
7. Buyer membayar melalui proses yang ditetapkan. Sebelum pembayaran valid, pesanan belum menjadi referensi produksi/pickup.
8. Setelah pembayaran dikonfirmasi, buyer melihat detail pesanan dan QR pickup.
9. Pada Hari H buyer menunjukkan QR. Satu atau beberapa grup dapat diserahkan pada waktu/tempat berbeda sesuai pengaturan venue.
10. Detail pesanan buyer langsung membedakan grup yang sudah diserahkan, masih tertunda, atau sedang dalam resolusi.
11. Jika seluruh grup sudah diserahkan atau memiliki resolusi akhir yang disetujui, pesanan berstatus selesai.

### Cabang penting buyer

| Kondisi | Perilaku yang dibutuhkan |
|---|---|
| PO belum buka | Buyer dapat membaca konten; pemesanan baru belum tersedia dan jadwal ditampilkan. |
| PO sudah tutup | Tidak ada pesanan baru; konten dan pesanan lama tetap dapat diakses sesuai hak. |
| Pembayaran belum selesai | Buyer melihat instruksi/tenggat; belum menerima status layak pickup. Perlakuan setelah tenggat adalah keputusan terbuka. |
| Buyer ingin membatalkan setelah bayar | Buyer mengajukan permintaan; keputusan mengikuti kebijakan publik, bukan pembatalan otomatis. |
| Satu grup bermasalah | Grup lain boleh tetap diambil; grup terdampak mengikuti insiden/refund/resolusi. |
| Buyer diwakili orang lain | Mengikuti aturan identitas/otorisasi yang masih perlu dikonfirmasi; tindakan fallback harus tercatat. |

---

## 10. Alur seller/kelas end-to-end

1. Tim kelas memperoleh akses seller sesuai keputusan admin.
2. Kelas menyiapkan artikel proyek dan produk lengkap, termasuk cerita/deskripsi produk.
3. Kelas mengajukan konten; admin menyetujui, meminta revisi, atau menolak dengan alasan.
4. Ketika publik, artikel menarik pengunjung ke produk; produk juga tersedia melalui katalog.
5. Selama PO, seller memantau permintaan kelasnya dalam batas informasi yang diizinkan. Angka sebelum final diberi label belum final.
6. Setelah PO/payment dikunci sesuai kebijakan, kelas memakai rekap valid sebagai referensi produksi.
7. Kelas menyiapkan barang, label/prosedur handover, dan melapor segera bila ada kekurangan atau masalah kualitas.
8. Pada Hari H, petugas kelas hanya menangani grup kelasnya. Setelah barang fisik diberikan, petugas mengonfirmasi handover grup tersebut.
9. Kelas menindaklanjuti no-show atau insiden sampai periode resolusi berakhir.
10. Setelah settlement kelasnya bersih, finance dapat mencairkan payout kelas tanpa menunggu kelas lain.

### Batas seller yang wajib dijaga

- Seller tidak melihat detail order/data buyer kelas lain.
- Seller tidak dapat mengubah status pembayaran, refund, atau payout secara mandiri.
- Seller tidak dapat menyelesaikan satu buyer order secara keseluruhan jika masih ada grup kelas lain.
- Edit produk/artikel setelah pesanan ada tidak mengubah informasi historis pesanan buyer.

---

## 11. Alur admin dan finance end-to-end

### Sebelum PO

1. Admin menetapkan acara, satu jendela PO, jadwal/batas bayar, Hari H, informasi pickup, kelas peserta, dan aturan yang telah final.
2. Admin memastikan ToS, kebijakan pembatalan/refund, dan instruksi buyer tersedia serta konsisten.
3. Admin meninjau artikel dan produk, memeriksa keterhubungan proyek-produk, lalu menerbitkan yang disetujui.
4. Admin mengatur akses seller/pickup, rencana zona, kontak eskalasi, dan skenario fallback.
5. Tim menjalankan pengujian berulang dengan peran nyata.

### Saat PO

1. Admin memantau review konten, order, payment, pertanyaan buyer, dan risiko kesiapan kelas.
2. Admin menjaga semua kelas pada satu batas PO yang sama, kecuali kebijakan perubahan secara eksplisit disetujui dan dikomunikasikan.
3. Admin/finance mencatat alasan atas tindakan material terhadap payment, order, refund, atau konten.

### Setelah PO dan sebelum Hari H

1. Admin mengunci/menetapkan referensi order valid sesuai kebijakan pembayaran.
2. Seller menerima rekap untuk kelasnya; buyer paid menerima informasi/QR pickup.
3. Admin menyelesaikan atau mengeskalasi kekurangan kualitas/stok sebelum acara.
4. Admin memastikan petugas memahami cara membedakan handover grup dari penyelesaian order penuh.

### Setelah Hari H

1. Admin mengevaluasi grup belum diambil, insiden, dan koreksi handover.
2. Pesanan buyer ditutup hanya bila semua grup final.
3. Finance menilai kelayakan settlement per kelas, terpisah dari status pesanan lain.
4. Finance memproses payout kelas yang sudah bersih dan menahan/menyesuaikan hanya kelas yang masih memiliki isu.
5. Tim membuat laporan dan pelajaran untuk event berikutnya.

---

## 12. Operasional Hari H pickup

### 12.1 Checklist pembukaan

- Admin mengonfirmasi venue, zona/meja, kelas, stok fisik, petugas, perangkat, koneksi, signage, dan kontak eskalasi.
- Petugas masuk dengan peran yang benar dan menguji skenario QR valid, grup sudah diserahkan, serta lookup fallback.
- Admin mengaktifkan/menyampaikan prosedur exception dan jalur bantuan buyer.
- Setiap kelas mengetahui hanya grup kelasnya yang boleh mereka serahkan.

### 12.2 Alur standar

1. Buyer menuju kelas/stand penjual
2. Penjual mengarahkan buyer untuk membuka QR atau kode produknya
3. Buyer menunjukkan QR pesanan yang sesuai dengan kelasnya.
6. Penjual mengscan QR, memverifikasi product grup, menyerahkan barang fisik.
7. Sistem menandai grup tersebut sebagai diserahkan; grup lain tetap terbuka bila belum diambil.
8. Saat grup terakhir menjadi final, sistem menandai pesanan buyer selesai.

### 12.3 Exception Hari H

| Kejadian | Respons operasional wajib |
|---|---|
| QR tak terbaca / buyer tidak membawa QR | Gunakan lookup dan verifikasi identitas/otorisasi yang disetujui; catat penggunaan fallback. |
| Grup sudah tercatat diserahkan | Jangan menyerahkan ulang. Periksa riwayat dan eskalasi ke admin. |
| Grup bukan milik kelas/petugas | Jangan tampilkan/serahkan detail yang tidak berwenang; arahkan ke zona/petugas benar. |
| Barang kurang, salah, atau rusak | Jangan menandai handover penuh secara palsu. Buat insiden untuk penggantian, penyerahan parsial, refund, atau resolusi lain yang disetujui. |
| Kelas belum siap | Admin memberi informasi kepada buyer dan memilih tindak lanjut sesuai kebijakan; status dicatat. |
| Buyer no-show | Grup tetap belum diambil sampai aturan no-show/late pickup diterapkan. |
| Koneksi/perangkat gagal | Gunakan log fallback terkendali; rekonsiliasi semua aksi saat sistem pulih untuk mencegah penyerahan ganda. |

### 12.4 Penutupan Hari H

1. Petugas memastikan tindakan fallback dan insiden telah diserahkan kepada admin untuk rekonsiliasi.
2. Admin meninjau grup belum diambil dan handover yang perlu koreksi.
3. Buyer menerima status akhir/lanjutan sesuai keputusan terhadap setiap grup.
4. Finance belum menutup payout kelas yang masih memiliki kewajiban resolusi.

---

## 13. Status yang harus dapat dipahami pengguna

Nama status akhir dapat disesuaikan desain, tetapi maknanya tidak boleh berubah atau tumpang tindih.

| Area | Status/fase minimum | Makna untuk pengguna |
|---|---|---|
| PO | Belum buka / Buka / Tutup | Menentukan apakah pesanan baru dapat dibuat. |
| Konten | Draft / Menunggu review / Perlu revisi / Disetujui / Dipublikasikan | Mengendalikan visibilitas artikel/produk. |
| Pembayaran | Menunggu pembayaran / Menunggu konfirmasi / Dibayar / Gagal / Kedaluwarsa / Direfund sebagian/penuh | Menjelaskan kelayakan pesanan untuk tahap berikutnya. |
| Grup kelas | Menunggu pemenuhan / Siap pickup bila dipakai / Diserahkan / Bermasalah / Dalam resolusi / Final melalui resolusi | Status per kelas dalam satu order buyer. |
| Pesanan buyer | Perlu pembayaran / Aktif untuk pickup / Sebagian selesai / Dalam resolusi / Selesai | Ringkasan yang berasal dari status semua grup, bukan satu scan QR. |
| Payout kelas | Belum layak / Ditinjau / Ditahan / Diproses / Dicairkan | Settlement kelas, terpisah dari status order buyer. |

### Aturan penyelesaian

- Grup hanya dinyatakan **diserahkan** setelah barang fisik telah diberikan dan tindakan dikonfirmasi.
- Grup yang tidak diserahkan harus memiliki resolusi akhir yang disetujui sebelum dianggap final.
- Pesanan buyer **selesai** hanya jika setiap grup kelasnya final.
- Payout kelas dapat **dicairkan** bila kewajiban dan penyesuaian kelas itu sudah bersih, tanpa syarat semua kelas lain bersih.

---

## 14. Aturan bisnis lintas halaman

1. Artikel proyek dan cerita/deskripsi produk adalah bagian wajib dari pengalaman FORTEXIV.
2. Setiap produk publik harus terkait dengan satu kelas dan artikel proyek yang relevan.
3. Semua kelas peserta memakai satu jendela PO bersama.
4. Tidak ada janji pengiriman; pemenuhan standar adalah pickup pada event.
5. Buyer dapat membeli lintas kelas dalam satu pesanan; pemisahan grup hanya untuk kebutuhan operasi/fulfilment.
6. Beli Sekarang ditujukan untuk satu produk; keranjang dapat memuat banyak produk/kelas.
7. Hanya pesanan dengan pembayaran valid sesuai kebijakan yang menjadi referensi produksi dan layak mendapat QR pickup.
8. ToS dan batas penting harus dapat dibaca sebelum buyer menyelesaikan checkout.
9. Pemindaian QR tidak sama dengan penyerahan. Penyerahan dilakukan per grup kelas.
10. Sistem wajib mencegah penyerahan ulang grup yang sama tanpa otorisasi koreksi.
11. Refund/penyesuaian hanya mengenai grup/kelas terdampak dan tidak mengubah status kelas lain secara otomatis.
12. Payout adalah proses finance terpisah dari order/payment buyer dan dapat ditutup per kelas.
13. Seller memiliki akses minimum yang diperlukan untuk kelas sendiri saja.
14. Override manual untuk payment, handover, refund, resolusi, atau payout harus menyimpan alasan dan pelaku yang berwenang.
15. Copy status dan kebijakan di beranda, detail produk, checkout, order, FAQ, dan ToS harus konsisten.

---

## 15. Konten dan kepemilikan

| Aset konten | Pemilik penyusun | Penyetuju/publikasi | Lokasi tampil |
|---|---|---|---|
| Artikel proyek | Tim kelas | Admin konten | Beranda, daftar artikel, detail artikel |
| Detail/deskripsi produk | Tim kelas (Seller) | Admin sesuai proses review | Katalog, detail produk |
| Cerita/artikel produk | Admin konten | Admin konten/operasional | Detail produk, kartu produk terkait artikel |
| Jadwal PO/Hari H dan lokasi | Admin operasional | Penanggung jawab acara | Beranda, checkout, order, halaman pickup/FAQ |
| ToS dan kebijakan refund/no-show | Penanggung jawab kebijakan | Pihak berwenang yang ditunjuk | Footer, halaman ToS, checkout |
| FAQ dan pengumuman | Admin operasional/konten | Admin berwenang | Beranda, FAQ, order bila relevan |
| Status order/handover | Sistem dan petugas berwenang | Sistem/admin untuk koreksi | Pesanan buyer, pickup, admin |
| Statement/payout | Finance | Finance berwenang | Portal finance dan seller sesuai hak |

---

## 16. Pengujian dan kesiapan rilis

Pengujian berulang adalah keputusan yang sudah dikonfirmasi. Uji dengan peran nyata (buyer, seller kelas A, seller kelas B, petugas pickup, admin, finance), bukan hanya dengan satu akun admin.

| Skenario uji | Hasil yang harus terbukti |
|---|---|
| Artikel → produk → keranjang → payment → QR → pickup | Alur lengkap berjalan dan buyer memahami pickup. |
| Satu order dari beberapa kelas | Order tetap satu untuk buyer; grup terbagi per kelas. |
| Grup A diserahkan lebih dulu daripada grup B | A menjadi diserahkan, B tetap tertunda, order belum selesai. |
| Grup terakhir diserahkan | Order buyer menjadi selesai. |
| Satu grup direfund/diselesaikan resmi | Grup tersebut final; order hanya selesai bila grup lain juga final. |
| PO menutup ketika buyer sedang browse/checkout | Tidak ada order baru yang salah dibuat; pesan jelas. |
| Payment gagal, belum selesai, atau kedaluwarsa | Tidak masuk rekap produksi/pickup sebagai paid tanpa keputusan yang berlaku. |
| QR sudah digunakan pada grup A | Tidak terjadi handover ganda; grup lain tetap dapat diproses sesuai kewenangan. |
| QR tidak terbaca / koneksi putus | Fallback tercatat dan direkonsiliasi tanpa duplikasi. |
| Seller A membuka pickup order milik seller B | Seller A tidak dapat menyerahkan/melihat detail yang tidak berwenang. |
| Barang kurang/rusak | Petugas tidak menandai handover palsu; insiden dan resolusi dapat ditelusuri. |
| Kelas A bersih, kelas B punya isu | Payout A dapat dilanjutkan, B dapat ditahan/diatur sendiri. |
| Konten produk berubah setelah buyer order | Pesanan historis tidak berubah diam-diam. |
| ToS | Buyer dapat membukanya dan persetujuannya dicatat sebelum checkout selesai. |

Setiap skenario memiliki catatan: prasyarat, langkah, hasil diharapkan, hasil aktual, pemilik tindak lanjut, dan keputusan lulus/tidak lulus.

---

## 17. Keputusan terbuka — perlu dikonfirmasi sebelum build/finalisasi

Bagian ini bukan instruksi implementasi. Pemilik bisnis perlu menetapkan jawabannya agar tim dapat memfinalkan perilaku dan copy.

1. Metode/penyedia pembayaran dan bagaimana konfirmasi pembayaran terjadi.
2. Batas pembayaran dibandingkan dengan waktu PO tutup, termasuk perlakuan order yang belum bayar dan reservasi item.
3. Konfirmasi scope rilis pertama untuk checkout lintas kelas; dokumen ini memakai alur target lintas kelas yang telah diprioritaskan.
4. Bentuk QR, aturan kedaluwarsa/penggunaan, dan detail fallback verifikasi identitas.
5. Apakah wakil buyer boleh mengambil; bila boleh, bukti/otorisasi serta informasi yang perlu dicatat.
6. Desain venue: check-in QR sentral lalu meja kelas, atau pemindaian langsung di tiap meja kelas, atau model lain.
7. Kebijakan pasti pembatalan, refund, fulfilment sebagian, penggantian, no-show, dan late pickup; siapa pemberi keputusan tiap kasus.
8. Status dan waktu kapan pesanan valid untuk produksi bila payment masih dalam verifikasi.
9. Field konten yang wajib untuk artikel/produk, termasuk aturan kategori, gambar, dan informasi khusus produk.
10. Siapa yang dapat mengedit/menyetujui konten dan perubahan material setelah publikasi/ada order.
11. Rumus payout, potongan/biaya bila ada, rekening/tujuan pencairan, jadwal, dan pihak penyetuju.
12. Durasi periode komplain/resolusi setelah Hari H.
13. Kebijakan privasi, retensi, serta data buyer apa yang boleh dilihat seller/petugas.
14. Kanal notifikasi dan persetujuan komunikasi buyer.
15. Kebutuhan aksesibilitas, bahasa tambahan bila ada, dan perangkat yang dipakai petugas Hari H.

---

## 18. Checklist handoff sebelum development

- [ ] Tim menyetujui seluruh keputusan terkonfirmasi pada dokumen ini.
- [ ] Setiap keputusan terbuka memiliki pemilik dan tanggal keputusan.
- [ ] Sitemap dan daftar halaman di atas telah dipetakan ke desain/wireframe.
- [ ] Semua halaman buyer memiliki perilaku untuk loading, kosong, gagal, dan PO tertutup bila relevan.
- [ ] Copy ToS, refund, no-show, pembayaran, dan pickup telah disetujui.
- [ ] Hak akses seller, admin, finance, dan petugas pickup telah disepakati.
- [ ] Layout Hari H, zona/meja, dan fallback saat QR/koneksi gagal telah diputuskan.
- [ ] Definisi status order, grup kelas, refund/resolusi, dan payout telah disetujui lintas tim.
- [ ] Skenario multi-kelas serta handover grup independen masuk dalam test plan dan uji coba berulang.
- [ ] Finance menyetujui pemisahan payment buyer, refund, settlement, dan payout per kelas.

---

## 19. Ringkasan implementasi fungsional untuk tim

Prioritas build sebaiknya mengikuti nilai alur, bukan sekadar urutan halaman: publikasi artikel/proyek dan produk; katalog dengan filter kelas/kategori; keranjang lintas kelas dan checkout; informasi payment/order/QR; handover per grup kelas; lalu exception, settlement, dan payout per kelas. Setiap tahap harus tetap mengikuti aturan bisnis di atas dan tidak mengunci pilihan teknologi sebelum keputusan terbuka selesai.

Dokumen ini adalah acuan fungsional FORTEXIV. Jika ada perubahan keputusan bisnis, perbarui bagian keputusan, aturan, halaman terdampak, dan skenario uji secara bersamaan agar tim tidak bekerja dengan versi alur yang berbeda.
