# Panduan Metode Agile — Proyek Kalian
### Template & Tutorial — isi bagian [ISI DI SINI] dengan proyek kalian sendiri

> Cara pakai dokumen ini: baca penjelasan tiap bagian, lihat **CONTOH** (memakai studi kasus "Aplikasi Contoh" — web pre-order produk sekolah), lalu isi bagian **ISI DI SINI** dengan proyek kalian sendiri. Kerjakan bersama satu tim, jangan sendiri-sendiri.

---

## 1. Kenapa Agile, Bukan Waterfall?

Cara lama ("waterfall"): kumpulkan semua requirement dulu → desain semua → coding semua fitur → baru testing di akhir. Masalahnya: kalau ada salah paham soal satu fitur, baru ketahuan di akhir — saat sudah terlanjur banyak kode ditulis.

**Agile**: pecah pekerjaan jadi potongan kecil (**Sprint**, biasanya 1–2 minggu), selesaikan dan **coba langsung**, lalu ulangi. Tiap akhir sprint ada sesuatu yang benar-benar **jalan**, bukan cuma "50% jadi".

> 📝 **ISI DI SINI:** Tulis 2–3 kalimat kenapa proyek kalian cocok dikerjakan dengan cara bertahap (misalnya: karena akan dipakai orang sungguhan, karena requirement-nya masih bisa berubah, dll).
>
> `_________________________________________________________________`

---

## 2. Peran dalam Tim Kecil

| Peran | Tugas | Siapa di tim kalian? |
|---|---|---|
| **Product Owner** | Mewakili suara pengguna, memutuskan fitur mana yang paling penting duluan | *(CONTOH: di Aplikasi Contoh, Product Owner memutuskan alur checkout lebih prioritas daripada fitur wishlist)* → **ISI DI SINI:** _______ |
| **Scrum Master** | Menjaga proses tetap jalan: memimpin standup, mencatat hambatan | **ISI DI SINI:** _______ |
| **Dev Team** | Menulis kode: backend, frontend, database | **ISI DI SINI:** _______ |

> 💡 Di tim kecil (3–6 orang), satu orang boleh merangkap lebih dari satu peran. Yang penting semua fungsi tetap berjalan tiap sprint.

---

## 3. Product Backlog & User Story

Format wajib:

> **Sebagai** [peran], **saya ingin** [melakukan sesuatu], **supaya** [mendapat manfaat].

**CONTOH (dari Aplikasi Contoh):**
> Sebagai **buyer**, saya ingin **melihat status pesanan saya**, supaya **saya tahu kapan harus datang untuk pickup**.

Setiap story butuh **Acceptance Criteria** (cara memastikan story itu benar-benar selesai):

**CONTOH:**
> - Saat buyer membuka halaman "Pesanan Saya", status yang muncul jelas (misal: Menunggu Pembayaran / Lunas / Dibatalkan).
> - Buyer hanya bisa melihat pesanan miliknya sendiri.

> 📝 **ISI DI SINI — tulis 1 user story punya kalian sendiri:**
>
> Sebagai _______, saya ingin _______, supaya _______.
>
> Acceptance criteria:
> - _______
> - _______

Daftar backlog lengkap proyek kalian ditulis di `01-product-backlog-prd-template.md`.

---

## 4. Prioritas: MoSCoW

| Kategori | Arti | CONTOH | ISI DI SINI (proyek kalian) |
|---|---|---|---|
| **M**ust have | Tanpa ini aplikasi tidak bisa dipakai sama sekali | Login, katalog, checkout | _______ |
| **S**hould have | Penting, tapi masih bisa jalan tanpa ini di awal | Filter/pencarian produk | _______ |
| **C**ould have | Bagus kalau ada, tidak mendesak | Wishlist, rating | _______ |
| **W**on't have (kali ini) | Sengaja tidak dikerjakan dulu, dicatat supaya tidak lupa | Payment gateway otomatis | _______ |

---

## 5. Estimasi: Story Points Sederhana

Skala **1, 2, 3, 5, 8** — mengukur *seberapa besar/rumit*, bukan berapa jam.

| Poin | Seberapa besar | CONTOH |
|---|---|---|
| 1 | Sangat kecil | Ubah teks tombol |
| 2 | Kecil | Halaman statis |
| 3 | Sedang | Form dengan validasi |
| 5 | Cukup besar | Alur checkout lengkap |
| 8 | Besar (sebaiknya dipecah lagi) | Alur verifikasi pembayaran + ticket |

> 📝 **ISI DI SINI:** setelah menulis semua user story di backlog, beri poin masing-masing. Total poin yang selesai tiap sprint = **velocity** tim kalian, dipakai untuk merencanakan sprint berikutnya.

---

## 6. Siklus Satu Sprint

| Tahap | Kapan | Isinya |
|---|---|---|
| **Sprint Planning** | Awal sprint | Pilih story dari backlog sesuai kapasitas tim (lihat velocity) |
| **Daily Standup** | Tiap hari kerja, 5–10 menit | 3 pertanyaan: kemarin ngapain, hari ini rencana apa, ada hambatan apa |
| **Sprint Review / Demo** | Akhir sprint | Tunjukkan fitur yang **jalan**, bukan slide |
| **Sprint Retrospective** | Setelah review | Apa yang jalan baik, apa yang menghambat, apa yang dicoba beda |

> ⚠️ Story yang belum selesai di akhir sprint **tidak dianggap "70% jalan"** — kembali ke backlog, diprioritaskan ulang.

---

## 7. Definition of Done (DoD)

Contoh checklist Aplikasi Contoh — **sesuaikan/ISI DI SINI** dengan standar tim kalian sendiri:

- [ ] Kode sudah lewat Pull Request + review anggota lain
- [ ] Fitur sudah dicoba langsung di browser
- [ ] Acceptance criteria di backlog terpenuhi
- [ ] Tidak ada data sensitif bocor
- [ ] _______ (tambahkan standar kalian sendiri)
- [ ] _______

---

## 8. Papan Kanban

```
 To Do        In Progress      Review          Done
┌─────────┐  ┌─────────┐     ┌─────────┐     ┌─────────┐
│         │  │         │     │         │     │         │
└─────────┘  └─────────┘     └─────────┘     └─────────┘
```

**Aturan WIP:** batasi jumlah kartu di "In Progress" per orang (misalnya maksimal 2). Fokus menyelesaikan sedikit story sampai tuntas, jangan memulai banyak sekaligus.

---

## 9. Rencana Sprint Proyek Kalian

**CONTOH:**

| Sprint | Fokus | Output |
|---|---|---|
| Sprint 0 | Persiapan (setup, kunci ERD & sitemap, sepakati alur Git di `../../panduan-kolaborasi-git.md`) | Project jalan, tim paham desain, semua tahu cara bikin branch & PR |
| Sprint 1 | Akun & Katalog | Login jalan, produk bisa ditambah & dilihat |
| Sprint 2 | Keranjang & Order | Checkout jalan |
| Sprint 3 | Pembayaran & Ticket | Verifikasi & ticket digital jalan |
| Sprint 4 | Admin, Pickup & Laporan | Semua fitur lengkap |

> 📝 **ISI DI SINI — rencana sprint proyek kalian sendiri:**

| Sprint | Fokus | Output |
|---|---|---|
| Sprint 0 | _______ | _______ |
| Sprint 1 | _______ | _______ |
| Sprint 2 | _______ | _______ |
| Sprint 3 | _______ | _______ |

> 📌 Kalau ternyata velocity tim lebih kecil dari rencana, **jangan menambah jam kerja** — pindahkan story prioritas rendah ke sprint berikutnya. Rencana menyesuaikan kenyataan, bukan sebaliknya.

---

## 10. Hubungan Antar Dokumen

```
00-panduan-metode-agile-template.md        ← kamu di sini
        │
        ▼
01-product-backlog-prd-template.md         ← isi SEMUA user story proyek kalian
        │
        ▼
02-erd-database-schema-template.md         ← kunci struktur tabel sebelum mulai coding
        │
        ▼
03-api-technical-spec-template.md          ← peta route/controller, siapa kerjakan apa
        │
        ▼
04-sitemap-wireframe-template.md           ← acuan halaman sebelum menulis tampilan

../../panduan-kolaborasi-git.md                ← di root repo, dua level di atas folder ini (dipakai bersama versi lengkap) —
                                                sepakati bareng: branching, commit, PR, cara mengelola merge conflict
../../panduan-ai-mengisi-template.md           ← di root repo — baca SEBELUM memakai AI untuk mengisi dokumen 01–04 di atas
../../panduan-ai-proyek-coding.md              ← di root repo — baca begitu mulai coding: cara kerja Copy-Paste vs Agent/CLI
```

Urutan mengunci: **Backlog & prioritas → ERD → Sitemap kasar → Route/API Spec → kesepakatan alur Git** — baru mulai coding per sprint. Kesepakatan alur Git juga perlu dikunci sebelum Sprint 1 dimulai, sebelum semua orang mulai commit bersamaan.

> 📌 Panduan Git dan panduan AI **tidak punya versi template terpisah** — isinya generik untuk proyek apa pun. Semuanya ada di **root repo** (bukan di dalam `design/`), supaya langsung terlihat begitu repo dibuka: isi bagian **🔧 Kesepakatan Tim** di `panduan-kolaborasi-git.md` dengan keputusan tim kalian, dan baca kedua panduan AI **sebelum** memakai AI apa pun untuk dokumen ini maupun kode nanti — di situ dijelaskan data apa yang tidak boleh pernah ditempel ke chat/agent AI.

---

## Lampiran: Kalau Kalian Kehilangan Arah

Ada versi **lengkap** dari dokumen-dokumen ini (folder `agile-lengkap/`), berisi contoh penerapan penuh untuk proyek Aplikasi Contoh. Boleh dibuka sebagai bantuan kalau tim benar-benar buntu — tapi coba isi template ini dengan ide kalian sendiri dulu sebelum mengintip contohnya.
