# Product Requirements Document (PRD) — Template
### Isi bagian [ISI DI SINI] dengan proyek kalian sendiri

> Sebelum mengisi: tim kalian butuh **BRD** (Business Requirements Document) dulu — dokumen yang menjelaskan masalah apa yang mau diselesaikan dan siapa saja penggunanya. Kalau belum punya, buat itu dulu sebelum lanjut ke PRD ini.

---

## 1. Vision Statement

Satu-dua kalimat: aplikasi apa yang dibangun, untuk siapa, menyelesaikan masalah apa.

**CONTOH:**
> Membangun website yang memungkinkan seller mengelola dan menampilkan produk, sementara buyer dapat melihat produk, melakukan pemesanan, pembayaran, mendapatkan digital ticket/order, dan melakukan pickup pada hari yang ditentukan.

> 📝 **ISI DI SINI:**
>
> `_________________________________________________________________`

---

## 2. Persona

**CONTOH:**

| Persona | Kebutuhan utama | Frustrasi kalau tidak terpenuhi |
|---|---|---|
| Admin | Kontrol penuh atas platform & laporan | Tidak tahu status transaksi keseluruhan |
| Seller | Cara cepat kelola produk & harga | Produk salah info sampai ke buyer |
| Buyer | Belanja mudah, kepastian pesanan | Bayar tapi tidak ada bukti jelas |

> 📝 **ISI DI SINI — persona proyek kalian:**

| Persona | Kebutuhan utama | Frustrasi kalau tidak terpenuhi |
|---|---|---|
| | | |
| | | |

---

## 3. Epic

Kelompokkan fitur besar jadi beberapa "Epic" (kumpulan user story yang berhubungan).

**CONTOH:** E1 Autentikasi, E2 Katalog Produk, E3 Manajemen Produk, E4 Keranjang, E5 Order & Pembayaran, E6 Digital Ticket, E7 Pickup, E8 Admin & Laporan.

> 📝 **ISI DI SINI — daftar Epic proyek kalian:**

| Epic | Deskripsi singkat |
|---|---|
| E1 — | |
| E2 — | |
| E3 — | |

---

## 4. Product Backlog

Cara mengisi tiap kolom:
- **User Story**: format "Sebagai [peran], saya ingin [aksi], supaya [manfaat]"
- **Prioritas**: Must / Should / Could / Won't (lihat panduan Agile Bagian 4)
- **Poin**: 1, 2, 3, 5, atau 8 (lihat panduan Agile Bagian 5)
- **Sprint**: rencana dikerjakan di sprint keberapa
- **Acceptance Criteria**: daftar kondisi yang harus benar supaya story dianggap selesai

**CONTOH satu baris terisi penuh (dari Aplikasi Contoh, Epic Order & Pembayaran):**

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-01 | Sebagai buyer, saya ingin checkout dari keranjang, supaya pesanan saya tercatat sistem. | Must | 5 | 2 | Order dibuat dengan status "menunggu pembayaran"; stok dikurangi saat order dibuat, bukan saat verifikasi, supaya dua buyer tidak sama-sama dapat stok terakhir yang sama. |

> 📝 **ISI DI SINI — backlog proyek kalian (tambah baris sebanyak yang dibutuhkan):**

| ID | User Story | Prioritas | Poin | Sprint | Acceptance Criteria |
|---|---|---|---|---|---|
| US-01 | | | | | |
| US-02 | | | | | |
| US-03 | | | | | |
| US-04 | | | | | |
| US-05 | | | | | |

---

## 5. Out of Scope (Won't Have — kali ini)

Tulis eksplisit fitur yang **sengaja tidak dikerjakan** dulu, supaya tidak diam-diam diharapkan oleh anggota tim lain atau calon pengguna.

**CONTOH:** payment gateway otomatis, notifikasi WhatsApp otomatis, scan QR otomatis, aplikasi mobile native.

> 📝 **ISI DI SINI:**
> - _______
> - _______
> - _______

## 6. Definition of Done

Sama dengan yang disepakati di `00-panduan-metode-agile-template.md` Bagian 7 — berlaku untuk **semua** ID di backlog ini, tidak ada pengecualian per sprint.
