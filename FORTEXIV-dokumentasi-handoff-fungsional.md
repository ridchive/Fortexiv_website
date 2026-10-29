# FORTEXIV — Handoff Kebutuhan Fungsional Website & Operasional

**Versi:** 1.2  
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

### Prinsip Utama: Mobile-First

FORTEXIV dikembangkan dengan pendekatan **mobile-first**, karena perangkat mobile merupakan perangkat utama yang digunakan oleh target pengguna untuk mengakses website. Oleh karena itu, seluruh perancangan antarmuka, pengalaman pengguna (UX), navigasi, dan interaksi harus memprioritaskan penggunaan pada layar ponsel sebelum disesuaikan untuk tablet dan desktop.

Mobile bukan sekadar versi responsif dari tampilan desktop. Pengalaman utama website harus dirancang agar nyaman, intuitif, dan berfungsi dengan baik sejak awal pada perangkat mobile.

#### 1. Prioritas Pengembangan Antarmuka

- **Mobile sebagai prioritas utama:** Rancang dan evaluasi setiap halaman terlebih dahulu pada layar ponsel, kemudian sesuaikan untuk layar yang lebih besar.
- **Navigasi sederhana:** Menu, tombol, dan perpindahan halaman harus mudah dipahami serta diakses melalui layar sentuh.
- **Konten mudah dibaca:** Artikel proyek dan cerita produk harus memiliki tipografi, jarak antarparagraf, serta tata letak yang nyaman untuk dibaca pada layar kecil.
- **Interaksi yang nyaman:** Tombol, tautan, filter, formulir, dan kontrol kuantitas harus mudah digunakan tanpa memerlukan presisi sentuhan yang berlebihan.
- **Alur pembelian yang ringkas:** Buyer harus dapat menjelajahi produk, menambahkan barang ke keranjang, melakukan checkout, dan memeriksa pesanan tanpa navigasi yang membingungkan.
- **Pengambilan barang yang praktis:** Informasi pesanan dan QR pengambilan harus mudah ditemukan dan ditampilkan saat Buyer berada di lokasi acara.
- **Dashboard yang responsif:** Seller dan Admin juga harus dapat menjalankan fungsi utama melalui perangkat mobile, meskipun pengelolaan data yang kompleks dapat lebih nyaman dilakukan melalui desktop.
- **Performa yang diperhatikan:** Gambar, artikel, dan elemen antarmuka harus dioptimalkan agar halaman tetap nyaman digunakan pada koneksi internet yang terbatas.

#### 2. Penerapan pada Setiap Bagian Website

| Bagian | Persyaratan Mobile-First |
|---|---|
| Landing Page | Artikel dan proyek unggulan mudah ditemukan; CTA terlihat jelas; konten tersusun secara vertikal dan nyaman dibaca. |
| Artikel/Proyek | Judul, gambar, isi artikel, dokumentasi, dan tautan produk tersusun dengan baik pada layar kecil. |
| Katalog Produk | Kartu produk mudah dipindai; filter kelas dan kategori mudah dibuka dan digunakan; informasi harga dan stok mudah terlihat. |
| Detail Produk | Foto, deskripsi, harga, informasi kelas, dan tombol pembelian memiliki hierarki visual yang jelas. |
| Keranjang dan Checkout | Ringkasan pesanan, jumlah produk, total pembayaran, persetujuan ToS, dan tombol konfirmasi mudah digunakan tanpa harus melakukan zoom. |
| My Orders | Status pembayaran dan pengambilan mudah dibedakan; detail pesanan dan QR mudah diakses. |
| Seller Dashboard | Seller dapat melihat pesanan, memperbarui informasi produk, memeriksa stok, dan mengakses fitur pickup melalui ponsel. |
| Admin Dashboard | Admin dapat mengakses fungsi penting seperti review produk, pengelolaan artikel, pemantauan transaksi, dan pengaturan acara melalui ponsel. |
| Hari H | QR pickup, detail kelompok pesanan, daftar barang, dan konfirmasi penyerahan mudah digunakan melalui layar sentuh. |

#### 3. Perilaku Responsif

- Tata letak harus menyesuaikan ukuran layar tanpa menyebabkan konten terpotong atau meluber secara horizontal.
- Navigasi desktop dapat disederhanakan menjadi menu mobile selama seluruh fungsi utama tetap dapat ditemukan.
- Tabel atau data yang lebar harus disesuaikan agar tetap terbaca, misalnya dengan tampilan kartu atau pengguliran horizontal pada komponen yang memang memerlukannya.
- Tombol dan kontrol interaktif harus dapat digunakan melalui sentuhan, bukan bergantung pada efek hover.
- Formulir, dialog konfirmasi, dropdown, dan filter harus tetap nyaman digunakan pada layar kecil.
- Elemen yang melekat pada layar (*sticky elements*) tidak boleh menutupi konten atau tombol penting.
- QR pengambilan harus dapat ditampilkan dengan ukuran yang memadai untuk dipindai dari layar ponsel.

#### 4. Kriteria Penerimaan

Website dinyatakan memenuhi prinsip mobile-first apabila:

1. Seluruh halaman utama telah dirancang dan diuji pada layar ponsel sebelum penyempurnaan desktop.
2. Tidak terdapat *horizontal overflow* yang mengganggu penggunaan halaman pada ukuran layar kecil.
3. Buyer dapat menyelesaikan alur utama dari menjelajahi proyek hingga melihat detail pesanan melalui ponsel.
4. Seller dapat mengakses produk, pesanan, dan fungsi pengambilan barang melalui ponsel.
5. Admin dapat mengakses fungsi pengelolaan utama melalui ponsel.
6. Semua tombol dan kontrol utama dapat digunakan melalui layar sentuh tanpa bergantung pada hover.
7. Formulir, dialog, navigasi, dan informasi status tetap terbaca serta mudah digunakan pada layar kecil.
8. QR pengambilan dan informasi kelompok pesanan dapat ditampilkan dengan jelas pada perangkat mobile.
9. Pengujian mencakup alur Buyer, Seller, dan Admin pada perangkat mobile serta pemeriksaan tampilan pada layar desktop.

**Prinsip akhir:** Desktop tetap didukung, tetapi prioritas perancangan, pengujian pengalaman pengguna, dan penyempurnaan antarmuka FORTEXIV adalah perangkat mobile.

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
| Tim seller/kelas | Mengelola informasi dasar produk dan memenuhi pesanan kelasnya | Mengelola nama, harga, stok, foto, kategori, dan deskripsi dasar produk miliknya; melihat kebutuhan kelasnya; menyiapkan/menyerahkan grupnya | Membuat atau mengedit artikel proyek maupun artikel/cerita produk; melihat data kelas lain; mengubah transaksi paid atau payout sendiri |
| Admin konten/operasional | Menjaga narasi FORTEXIV dan acara berjalan tertib | Membuat, menulis, mengedit, mengelola, serta menerbitkan artikel proyek dan artikel/cerita produk; mengelola jendela PO, menangani exception, mengatur Hari H | Mencairkan payout tanpa kewenangan finance yang berlaku |
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
├─ Artikel/cerita produk
│  ├─ Daftar / status artikel produk
│  └─ Buat atau revisi artikel produk
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
| Pemilik konten | Admin konten/operasional. Admin menyusun dan mengelola artikel proyek serta artikel/cerita produk; kelas dapat menjadi sumber informasi proyek/produk bila diperlukan. |
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
| Pemilik konten | Admin konten/operasional menyusun, menulis, mengedit, mengelola, dan menerbitkan artikel proyek. |
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
| Pemilik konten | Admin konten/operasional membuat, menulis, mengedit, mengelola, dan menerbitkan artikel proyek; tim kelas tidak mengelola artikel ini. |
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
| Konten/section | Nama, visual, harga, pilihan/varian jika ada, ketersediaan sesuai aturan, jumlah, deskripsi dasar spesifikasi/karakteristik produk, **artikel/cerita produk** bila tersedia, kelas pemilik, tautan ke artikel proyek, informasi PO, informasi pickup Hari H, kebijakan penting, produk terkait bila ada. |
| Kontrol | Pilih varian, atur jumlah, **Tambah ke Keranjang**, **Beli Sekarang**, buka artikel proyek, kembali ke katalog. |
| Tujuan navigasi | Keranjang, checkout Beli Sekarang, artikel proyek, katalog. |
| Keadaan khusus | Varian belum dipilih: CTA menjelaskan data yang kurang. Produk tidak tersedia/PO tertutup: tindakan membeli nonaktif dan alasan terlihat. Produk dihapus/tidak publik: tidak dapat dibeli. Loading/error/not found ditangani jelas. |
| Pemilik konten | Seller/tim kelas mengelola informasi dasar produk (nama, harga, stok, foto, kategori, dan deskripsi dasar spesifikasi/karakteristik). Admin mengelola artikel/cerita produk, termasuk narasi dan penyajiannya; admin juga meninjau serta mengelola status publikasi produk sesuai kewenangan. |
| Aturan bisnis | Beli Sekarang membuat alur checkout untuk **satu produk ini**, sedangkan Tambah ke Keranjang dapat bergabung dengan produk kelas lain. Harga dan informasi dasar produk yang tampil saat checkout harus diverifikasi ulang. Artikel/cerita produk tidak dapat dikelola Seller. |
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
| Konten/section | Status produk, jadwal PO, indikator kebutuhan tindakan, rekap paid/confirmed yang diizinkan, kesiapan Hari H, insiden terbuka, status settlement/payout kelas. Artikel proyek dan artikel/cerita produk dikelola Admin dan tidak tersedia untuk dibuat atau diedit dari portal ini. |
| Kontrol | Buka konten, rekap produksi, kesiapan, pickup, insiden, settlement sesuai hak. |
| Keadaan khusus | Tidak ada kelas yang ditugaskan: jelaskan bahwa akses belum diberikan. PO tutup: fokus bergeser ke rekap produksi. |
| Pemilik konten | Sistem; data berasal dari kelas/admin/finance. |
| Aturan bisnis | Angka hanya untuk kelas sendiri; status payout tidak boleh dianggap sebagai status semua buyer order. |
| Kriteria penerimaan | Seller segera tahu tindakan berikutnya dan tidak melihat data kelas lain. |

### 7.2 Batas akses artikel proyek dan artikel/cerita produk

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Menetapkan batas yang jelas: Seller/tim kelas tidak mengelola artikel proyek maupun artikel/cerita produk dari portal Seller. |
| Target | Seller lead/anggota seller. |
| Konten/section | Tidak ada daftar, editor, draft, atau catatan revisi artikel di portal Seller. Artikel yang telah publik hanya dapat dibaca melalui halaman publik bila relevan. |
| Kontrol | Tidak ada kontrol membuat, mengedit, mengajukan review, atau menerbitkan artikel proyek maupun artikel/cerita produk. |
| Tujuan navigasi | Produk, rekap produksi, dan dashboard. |
| Keadaan khusus | Bila Seller memerlukan perubahan narasi artikel, kebutuhan tersebut disampaikan melalui prosedur operasional yang ditetapkan Admin; tidak ada pengeditan langsung oleh Seller. |
| Pemilik konten | Admin konten/operasional sepenuhnya bertanggung jawab atas pembuatan, penulisan, penyuntingan, pengelolaan, dan publikasi artikel proyek serta artikel/cerita produk. |
| Aturan bisnis | Admin dapat mengaitkan artikel proyek dengan kelas dan produk relevan, serta menampilkan artikel/cerita produk pada detail produk atau area relevan lain. |
| Kriteria penerimaan | Dashboard Seller tidak menyediakan akses untuk membuat atau mengedit artikel proyek maupun artikel/cerita produk; artikel publik tetap dapat mengarahkan Buyer ke produk terkait. |

### 7.3 Produk: daftar, buat, revisi

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Mengelola daftar produk yang melekat pada proyek kelas. |
| Target | Seller berwenang. |
| Konten/section | Daftar produk dan status; form nama, kategori, foto, harga, opsi/varian bila dipakai, stok/ketersediaan sesuai keputusan, deskripsi dasar spesifikasi/karakteristik, tautan artikel proyek bila tersedia, dan informasi wajib yang ditetapkan admin. Artikel/cerita produk tidak menjadi field yang dikelola Seller. |
| Kontrol | Simpan draft, ajukan review, revisi, nonaktifkan sesuai kewenangan, lihat catatan review. |
| Tujuan navigasi | Artikel terkait, rekap produksi, dashboard. |
| Keadaan khusus | Produk belum ada; produk perlu revisi; produk pernah dipesan; PO tertutup. Perubahan material setelah ada order tidak boleh mengubah snapshot order dan harus melalui aturan exception/review. |
| Pemilik konten | Seller/tim kelas mengelola informasi dasar produk miliknya. Admin mengelola artikel/cerita produk; Admin juga melakukan review dan mengelola publikasi produk. |
| Aturan bisnis | Setiap produk milik satu kelas dan terhubung ke artikel proyek yang relevan. Produk tidak tampil publik sebelum disetujui/dipublikasikan. |
| Kriteria penerimaan | Produk publik selalu memiliki kelas, kategori, deskripsi dasar, dan keterkaitan artikel proyek yang benar bila artikelnya tersedia. Seller tidak dapat membuat/mengedit artikel atau cerita produk, serta tidak dapat mengubah pesanan paid melalui edit produk. |

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
| Tujuan | Menjaga kelengkapan, relevansi, konsistensi, dan kualitas artikel proyek serta artikel/cerita produk sebelum dan sesudah publikasi. |
| Target | Admin konten/operasional. |
| Konten/section | Daftar artikel proyek dan artikel/cerita produk; editor; judul; gambar sampul; ringkasan; narasi; informasi kelas; dokumentasi proyek; produk terkait; status publikasi; dan pratinjau halaman. |
| Kontrol | Membuat, menulis, melihat, mengedit, menyimpan draft, menerbitkan, mengubah status publikasi, dan mengarsipkan artikel proyek maupun artikel/cerita produk. |
| Keadaan khusus | Artikel yang sudah memiliki produk atau transaksi terkait harus dapat diperbarui tanpa mengubah riwayat transaksi. Artikel yang tidak lagi relevan dapat diarsipkan sesuai kebijakan. |
| Pemilik konten | Admin bertanggung jawab penuh atas pembuatan, penulisan, penyuntingan, pengelolaan, dan publikasi artikel proyek serta artikel/cerita produk. |
| Aturan bisnis | Hanya Admin yang berwenang mengelola artikel. Admin menentukan struktur konten, judul, gambar sampul, narasi, dokumentasi, dan informasi proyek yang ditampilkan. Admin menjaga konsistensi narasi produk dengan identitas dan tujuan FORTEXIV. Artikel proyek dapat dikaitkan dengan kelas dan produk relevan; artikel/cerita produk dapat ditampilkan pada detail produk atau area relevan lain. Artikel berstatus draft tidak ditampilkan kepada publik. |
| Kriteria penerimaan | Admin dapat mengelola artikel proyek dan artikel/cerita produk secara mandiri, menghubungkan artikel proyek dengan kelas dan produk terkait, menyajikan artikel/cerita produk pada lokasi relevan, serta mengatur status publikasi tanpa akses pengeditan dari Seller. |

### 8.4 Pengelolaan dan Review Produk

| Aspek | Spesifikasi |
|---|---|
| Tujuan | Memastikan informasi produk lengkap, relevan, dan sesuai ketentuan sebelum ditampilkan kepada Buyer. |
| Target | Seller dan Admin operasional. |
| Konten/section | Daftar produk; nama produk; foto; deskripsi dasar spesifikasi/karakteristik; harga; stok; kategori; kelas pemilik; status produk; antrean review; dan catatan revisi. Artikel/cerita produk dikelola pada area artikel oleh Admin. |
| Kontrol Seller | Menambahkan produk, mengedit informasi dasar produk miliknya, mengatur harga dan stok, mengajukan produk untuk review, serta melihat status dan catatan review. |
| Kontrol Admin | Memeriksa produk, menyetujui, meminta revisi, menolak, serta mengatur publikasi atau penarikan produk sesuai kewenangan. |
| Keadaan khusus | Perubahan material pada produk yang sudah memiliki pesanan harus ditangani dengan hati-hati dan tidak boleh diam-diam mengubah transaksi historis. |
| Pemilik konten | Seller bertanggung jawab atas informasi dasar produk miliknya: nama, harga, stok, foto, kategori, dan deskripsi dasar spesifikasi/karakteristik. Admin bertanggung jawab atas artikel/cerita produk, proses review, dan keputusan publikasi produk. |
| Aturan bisnis | Seller hanya dapat mengelola produknya sendiri dan tidak dapat mengelola artikel/cerita produk. Produk yang belum disetujui tidak ditampilkan sebagai produk publik yang dapat dibeli. Alasan revisi atau penolakan wajib disampaikan kepada Seller agar dapat ditindaklanjuti. |
| Kriteria penerimaan | Seller dapat mengelola informasi dasar produk miliknya dan melihat status review beserta alasannya. Admin dapat memeriksa produk, menentukan keputusan review, mengelola artikel/cerita produk, dan memastikan produk yang dipublikasikan memiliki informasi dasar lengkap serta terhubung dengan kelas yang benar. |

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
2. Kelas mengelola informasi dasar produk yang dijualnya: nama, harga, stok, foto, kategori, serta deskripsi dasar spesifikasi/karakteristik.
3. Kelas mengajukan produk untuk review; admin menyetujui, meminta revisi, atau menolak informasi produk dengan alasan.
4. Admin membuat dan mengelola artikel proyek serta artikel/cerita produk. Ketika publik, artikel menarik pengunjung ke produk; produk juga tersedia melalui katalog.
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
- Seller tidak memiliki akses untuk membuat atau mengedit artikel proyek maupun artikel/cerita produk.
- Edit informasi dasar produk setelah pesanan ada tidak mengubah informasi historis pesanan buyer.

---

## 11. Alur admin dan finance end-to-end

### Sebelum PO

1. Admin menetapkan acara, satu jendela PO, jadwal/batas bayar, Hari H, informasi pickup, kelas peserta, dan aturan yang telah final.
2. Admin memastikan ToS, kebijakan pembatalan/refund, dan instruksi buyer tersedia serta konsisten.
3. Admin membuat, menulis, mengedit, dan menerbitkan artikel proyek serta artikel/cerita produk; Admin meninjau informasi dasar produk, memeriksa keterhubungan proyek-produk, lalu mengelola publikasi produk yang disetujui.
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

1. Artikel proyek dan artikel/cerita produk adalah bagian wajib dari pengalaman FORTEXIV dan dikelola Admin. Informasi dasar produk dikelola Seller/tim kelas.
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
13. Seller memiliki akses minimum yang diperlukan untuk kelas sendiri saja, termasuk pengelolaan informasi dasar produk miliknya; Seller tidak memiliki akses membuat atau mengedit artikel proyek maupun artikel/cerita produk.
14. Override manual untuk payment, handover, refund, resolusi, atau payout harus menyimpan alasan dan pelaku yang berwenang.
15. Copy status dan kebijakan di beranda, detail produk, checkout, order, FAQ, dan ToS harus konsisten.

---

## 15. Konten dan kepemilikan

| Aset konten | Pemilik penyusun | Penyetuju/publikasi | Lokasi tampil |
|---|---|---|---|
| Artikel proyek | Admin konten/operasional | Admin konten/operasional | Beranda, daftar artikel, detail artikel |
| Informasi dasar produk (nama, harga, stok, foto, kategori, deskripsi spesifikasi/karakteristik) | Tim kelas (Seller) | Admin sesuai proses review dan publikasi | Katalog, detail produk |
| Cerita/artikel produk | Admin konten/operasional | Admin konten/operasional | Detail produk atau bagian relevan lain |
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
| Informasi dasar produk berubah setelah buyer order | Pesanan historis tidak berubah diam-diam. |
| Admin mengelola artikel proyek/artikel produk | Admin dapat membuat, mengedit, mengaitkan, dan menerbitkan artikel; Seller tidak memiliki akses edit artikel melalui dashboard Seller. |
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
10. Proses perubahan material setelah publikasi/ada order, dengan kepemilikan yang sudah ditetapkan: Admin untuk artikel proyek dan artikel/cerita produk; Seller untuk informasi dasar produknya; Admin berwenang atas review/publikasi produk.
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
