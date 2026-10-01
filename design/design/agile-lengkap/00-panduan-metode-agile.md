# Panduan Metode Agile — Aplikasi Contoh
### Versi Lengkap (contoh penerapan nyata, dipakai sebagai acuan/bantuan)

---

## 0. Cara Pakai Dokumen Ini

Dokumen ini menjelaskan **cara kerja**, bukan cuma teori. Empat dokumen lain di folder ini (PRD, ERD, Route/API Spec, Sitemap) adalah *hasil* dari cara kerja yang dijelaskan di sini — bukan dokumen terpisah yang kebetulan dikumpulkan bareng.

Kalau tim kalian nanti kehilangan arah ("kita ini lagi ngerjain apa sih sekarang, sudah sejauh mana?"), balik ke dokumen ini dulu, khususnya Bagian 9 (Rencana Sprint).

---

## 1. Kenapa Agile, Bukan Waterfall?

Cara lama ("waterfall"): kumpulkan **semua** requirement → desain **semua** → coding **semua** fitur → baru testing di akhir → baru rilis. Masalahnya, kalau di tengah jalan ternyata ada salah paham soal satu fitur, ketahuannya di **akhir** — saat sudah terlanjur banyak kode ditulis di atas asumsi yang salah.

**Agile**: pecah pekerjaan jadi potongan kecil yang bisa selesai dan **benar-benar dicoba** dalam waktu pendek (1–2 minggu, disebut **Sprint**), lalu diulang. Tiap akhir sprint ada sesuatu yang **jalan** — walau belum semua fitur ada — bukan "50% desain, 0% kode yang bisa dijalankan".

**Kenapa ini penting khusus untuk Aplikasi Contoh:** aplikasi ini rencananya benar-benar dipakai seller dan buyer sungguhan. Kita perlu tahu secepat mungkin kalau ada asumsi yang meleset (misalnya: ternyata buyer bingung dengan alur checkout) — bukan menyadarinya seminggu sebelum hari peluncuran, saat sudah terlambat memperbaiki.

---

## 2. Peran dalam Tim Kecil

| Peran | Tugas | Di tim SMA biasanya dipegang |
|---|---|---|
| **Product Owner** | Mewakili suara "pengguna" (seller & buyer), memutuskan fitur mana yang paling penting duluan, menulis/menyetujui isi backlog | 1 orang, atau bergilir per sprint |
| **Scrum Master** | Menjaga proses tetap jalan: memimpin standup, mencatat hambatan (blocker), memastikan sprint tidak molor tanpa disadari | 1 orang, bisa juga guru/pendamping di awal |
| **Dev Team** | Semua yang menulis kode: backend (route/controller/model), frontend (Blade/tampilan), database | Sisa anggota tim — di tim kecil, satu orang bisa merangkap Product Owner **dan** Dev Team |

> 💡 Di tim kecil (3–6 orang), jangan kaku memisah peran ke orang yang berbeda-beda. Yang penting **ketiga fungsi** di atas selalu ada yang mengerjakan tiap sprint, bukan siapa namanya.

---

## 3. Product Backlog & User Story

**Product Backlog** = daftar semua hal yang *mungkin* dikerjakan, diurutkan dari paling penting ke paling tidak penting. Isinya ditulis dalam format **User Story**:

> **Sebagai** [peran], **saya ingin** [melakukan sesuatu], **supaya** [mendapat manfaat].

**Contoh nyata dari Aplikasi Contoh:**

> Sebagai **buyer**, saya ingin **melihat status pesanan saya**, supaya **saya tahu kapan harus datang untuk pickup**.

> Sebagai **seller**, saya ingin **mengatur stok produk saya**, supaya **produk yang sudah habis tidak bisa dipesan buyer lain**.

Setiap user story butuh **Acceptance Criteria** — cara memastikan story itu "selesai dengan benar", ditulis sebagai daftar kondisi:

> - Saat buyer membuka halaman "Pesanan Saya", status yang muncul adalah salah satu dari: Menunggu Pembayaran, Menunggu Verifikasi, Lunas, Dibatalkan.
> - Kalau status "Lunas", tanggal & lokasi pickup ikut ditampilkan.
> - Buyer hanya bisa melihat pesanan miliknya sendiri (bukan pesanan buyer lain).

Lihat `01-product-backlog-prd.md` untuk daftar lengkap user story Aplikasi Contoh beserta acceptance criteria-nya.

---

## 4. Prioritas: MoSCoW

| Kategori | Arti | Contoh Penerapan |
|---|---|---|
| **M**ust have | Tanpa ini, aplikasi tidak bisa dipakai sama sekali | Login, tampilkan katalog, checkout, catat status pembayaran |
| **S**hould have | Penting, tapi aplikasi masih bisa jalan (dengan proses manual) tanpa ini di awal | Filter/pencarian produk, riwayat pesanan lengkap |
| **C**ould have | Bagus kalau ada, tidak mendesak | Wishlist, rating produk, notifikasi email |
| **W**on't have (kali ini) | Sengaja tidak dikerjakan di iterasi ini, dicatat supaya tidak dilupakan begitu saja | Payment gateway otomatis, scan QR ticket otomatis |

Prioritas menentukan urutan pengerjaan di backlog — **Must have** dikerjakan duluan, bukan berdasarkan mana yang "kelihatan seru".

---

## 5. Estimasi: Story Points Sederhana

Dipakai skala **1, 2, 3, 5, 8** (deret Fibonacci disederhanakan) untuk mengukur *seberapa besar/rumit* sebuah story — bukan berapa jam, karena kecepatan orang berbeda-beda.

| Poin | Kira-kira seberapa besar | Contoh |
|---|---|---|
| 1 | Sangat kecil, hitungan jam | Ubah teks tombol, tambah validasi sederhana |
| 2 | Kecil | Halaman statis (About, FAQ) |
| 3 | Sedang | Form tambah produk dengan validasi & upload gambar |
| 5 | Cukup besar, ada beberapa bagian bergerak | Alur checkout dari keranjang sampai order tercatat |
| 8 | Besar, sebaiknya dipecah lagi kalau bisa | Alur verifikasi pembayaran + penerbitan digital ticket |

Total poin yang berhasil diselesaikan tiap sprint disebut **velocity** — dipakai untuk memperkirakan berapa banyak pekerjaan yang realistis diambil di sprint berikutnya (jangan mengambil lebih banyak poin dari velocity sprint sebelumnya).

---

## 6. Siklus Satu Sprint

| Tahap | Kapan | Isinya |
|---|---|---|
| **Sprint Planning** | Awal sprint (di kelas/sesi tim) | Pilih story dari backlog (mulai dari prioritas tertinggi) sampai totalnya sesuai kapasitas tim; pastikan semua paham acceptance criteria-nya |
| **Daily Standup** | Tiap hari kerja, 5–10 menit | Tiap orang jawab 3 hal: (1) kemarin ngerjain apa, (2) hari ini rencana apa, (3) ada hambatan apa |
| **Sprint Review / Demo** | Akhir sprint | Tunjukkan fitur yang **benar-benar jalan** ke Product Owner (dan idealnya ke calon pengguna) — bukan slide, tapi demo langsung di aplikasi |
| **Sprint Retrospective** | Setelah review, sebelum sprint berikutnya | Diskusi jujur: apa yang jalan baik, apa yang menghambat, apa yang mau dicoba beda di sprint berikutnya |

> ⚠️ Story yang belum selesai di akhir sprint **tidak dianggap "70% jalan"** — statusnya tetap "belum Done", masuk kembali ke backlog untuk diprioritaskan ulang di sprint berikutnya.

---

## 7. Definition of Done (DoD)

Sebuah user story baru boleh dicentang "Done" kalau **semua** ini terpenuhi:

- [ ] Kode sudah di-push ke branch masing-masing dan sudah lewat Pull Request + review dari anggota lain
- [ ] Fitur sudah dicoba langsung di browser (bukan cuma "kelihatannya benar" di kode)
- [ ] Acceptance criteria di backlog terpenuhi semua
- [ ] Tidak ada data sensitif (password, bukti transfer) yang bocor ke tempat yang tidak seharusnya
- [ ] Halaman yang butuh login/peran tertentu benar-benar tertutup dari akses tanpa izin

Definition of Done ini **sama untuk semua sprint** — jangan diturunkan standarnya walau sprint terasa mepet waktu.

---

## 8. Papan Kanban

Gunakan papan dengan 4 kolom (fisik di kelas atau digital — Trello/GitHub Projects):

```
 To Do        In Progress      Review          Done
┌─────────┐  ┌─────────┐     ┌─────────┐     ┌─────────┐
│ Story A │  │ Story C │     │ Story E │     │ Story G │
│ Story B │  │ Story D │     │         │     │ Story H │
└─────────┘  └─────────┘     └─────────┘     └─────────┘
```

**Aturan WIP (Work In Progress):** batasi jumlah kartu di kolom "In Progress" (misalnya maksimal 2 per orang). Kalau semua orang mengerjakan banyak hal sekaligus, tidak ada yang benar-benar selesai — lebih baik fokus menyelesaikan sedikit story sampai tuntas daripada memulai banyak story setengah-setengah.

---

## 9. Rencana Sprint Aplikasi Contoh (Contoh Penerapan)

Referensi ke nomor FR ada di `01-product-backlog-prd.md` dan BRD.

| Sprint | Fokus | Story Utama (FR terkait) | Output di akhir sprint |
|---|---|---|---|
| **Sprint 0** — Persiapan (∼1 minggu) | Setup, bukan fitur | Install Laravel, buat repo GitHub, kunci ERD (`02-erd-database-schema.md`), kunci sitemap (`04-sitemap-wireframe.md`), sepakati alur branching & PR (`../../panduan-kolaborasi-git.md`) | Project Laravel kosong tapi jalan, migration awal sudah bisa dijalankan, tim paham desain database & halaman, semua orang tahu cara bikin branch dan membuka PR |
| **Sprint 1** — Akun & Katalog | FR-01 (Auth), FR-02 (Catalogue), FR-03 (Product Management dasar) | Seller bisa login & tambah produk; buyer/publik bisa lihat katalog & detail produk |
| **Sprint 2** — Keranjang & Order | FR-04 (Cart), FR-05 (Order — bagian pembuatan order) | Buyer bisa menambah ke keranjang, checkout, order tersimpan berstatus "menunggu pembayaran" |
| **Sprint 3** — Pembayaran & Digital Ticket | FR-05 (verifikasi), FR-06 (Digital Ticket) | Buyer upload bukti transfer; admin verifikasi; ticket digital terbit otomatis setelah lunas |
| **Sprint 4** — Admin, Pickup & Laporan | FR-07 (Pickup), kebutuhan Admin, laporan harian | Admin bisa tandai ticket "sudah diambil"; dashboard laporan (total order, revenue, pickup) |
| **Sprint 5** — Pemantapan (buffer) | Non-Functional Requirements | Perbaikan bug dari testing, tampilan responsif HP, uji beban ringan, siap dipakai sungguhan |

> 📌 Kalau kapasitas tim ternyata lebih kecil dari rencana (velocity rendah di Sprint 1), **jangan menambah jam kerja** — pindahkan story yang prioritasnya lebih rendah ke sprint berikutnya. Itu justru salah satu manfaat utama Agile: rencana menyesuaikan kenyataan, bukan sebaliknya.

---

## 10. Hubungan Antar Dokumen

```
00-panduan-metode-agile.md      ← kamu di sini: cara kerja & jadwal sprint
        │
        ▼
01-product-backlog-prd.md       ← daftar SEMUA user story + prioritas (sumber tiap Sprint Planning)
        │
        ▼
02-erd-database-schema.md       ← dikunci di Sprint 0, jadi acuan tabel untuk migration Laravel
        │
        ▼
03-api-technical-spec.md        ← peta route & controller, acuan siapa mengerjakan endpoint apa
        │
        ▼
04-sitemap-wireframe.md         ← acuan halaman/tampilan sebelum menulis Blade view

../../panduan-kolaborasi-git.md         ← di root repo, dua level di atas folder ini (dipakai bersama versi template) —
                                         disepakati bareng di Sprint 0: branching, commit, PR, cara mengelola merge conflict
../../panduan-ai-mengisi-template.md    ← di root repo — dipakai SELAMA mengisi dokumen 01–04: etika & keamanan data saat minta bantuan AI
../../panduan-ai-proyek-coding.md       ← di root repo — dipakai begitu mulai coding: cara kerja Copy-Paste vs Agent/CLI, etika & keamanan data kode
```

Urutan mengunci dokumen: **PRD prioritas → ERD → Sitemap kasar → Route/API Spec → kesepakatan alur Git** — baru mulai coding per sprint. Mengubah ERD di tengah sprint itu mahal (banyak kode bergantung padanya), jadi pastikan Sprint 0 benar-benar menuntaskan Bagian ini sebelum lanjut. Alur Git (`../../panduan-kolaborasi-git.md`) juga perlu disepakati sebelum Sprint 1 dimulai — begitu banyak orang mulai commit bersamaan, sulit mengubah kesepakatan di tengah jalan.

> 📌 Panduan Git dan panduan AI **tidak dipisah** jadi versi lengkap/template seperti dokumen lain di sini — isinya generik dan sama untuk proyek apa pun, jadi diletakkan di **root repo** (bukan di dalam `design/`) supaya langsung terlihat begitu repo dibuka, dipakai bersama oleh folder `agile-lengkap/` dan `agile-template/`. Pakai `panduan-ai-mengisi-template.md` sejak mulai mengisi dokumen 01–04, dan `panduan-ai-proyek-coding.md` begitu mulai menulis kode di Sprint 1.
