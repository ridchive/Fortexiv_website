# Sitemap & Wireframe — Template
### Isi bagian [ISI DI SINI] dengan proyek kalian sendiri

> Kunci sitemap kasar di Sprint 0, bersamaan dengan ERD — supaya saat Sprint 1 dimulai, tim sudah tahu halaman apa saja yang akan dibuat dan siapa mengerjakan yang mana.

---

## Cara Mengisi

1. Kelompokkan halaman berdasarkan **siapa yang bisa mengaksesnya** (publik, buyer, seller, admin, dst. — sesuaikan dengan peran di proyek kalian).
2. Untuk tiap halaman penting (yang ada interaksi, bukan cuma teks statis), gambar wireframe kasar — kotak-kotak sederhana, bukan desain final. Tujuannya menyepakati **isi & struktur**, bukan warna/estetika.
3. Tandai tombol/aksi utama di tiap halaman — itu yang menentukan route apa yang dibutuhkan di dokumen Route/API Spec.

---

## CONTOH

### Sitemap

```
PUBLIK
├── /products                    Katalog produk
├── /products/{slug}             Detail produk
└── /login, /register

BUYER
├── /cart                        Keranjang
├── /orders                      Riwayat pesanan
└── /orders/{id}/ticket          Digital ticket
```

### Wireframe — Detail Produk

```
┌──────────────────────────────────────────────────┐
│ [ ← Kembali ]                                     │
├───────────────┬────────────────────────────────────┤
│   [gambar]    │ Nama Produk                        │
│               │ Rp 15.000 — Stok tersisa: 12        │
│               │ Deskripsi produk...                 │
│               │ Jumlah: [ - ] 1 [ + ]                │
│               │ [ Tambah ke Keranjang ]              │
└───────────────┴────────────────────────────────────┘
```

---

## 📝 ISI DI SINI — Sitemap Proyek Kalian

```
PUBLIK
├── _______
└── _______

_______ (nama peran, misal: BUYER)
├── _______
└── _______

_______ (nama peran, misal: SELLER/ADMIN)
├── _______
└── _______
```

## 📝 ISI DI SINI — Wireframe Halaman Kunci

Ulangi kotak ini untuk tiap halaman penting (minimal untuk halaman yang ada di jalur "Must have" backlog kalian):

**Halaman: _______**

```
┌──────────────────────────────────────────────────┐
│                                                    │
│                                                    │
│                                                    │
└──────────────────────────────────────────────────┘
```

Tombol/aksi utama di halaman ini: _______

**Halaman: _______**

```
┌──────────────────────────────────────────────────┐
│                                                    │
│                                                    │
│                                                    │
└──────────────────────────────────────────────────┘
```

Tombol/aksi utama di halaman ini: _______

*(tambahkan sebanyak halaman yang dibutuhkan)*

---

## Catatan Desain Tampilan

- Pikirkan juga tampilan versi **HP**, bukan cuma desktop — biasanya kolom yang berdampingan di desktop ditumpuk vertikal di HP.
- Wireframe tahap ini **tidak perlu warna atau font final** — itu urusan belakangan setelah struktur disepakati.

---

## Lampiran

Wireframe lengkap 9 halaman kunci Aplikasi Contoh ada di `agile-lengkap/04-sitemap-wireframe.md` — buka kalau butuh contoh lebih banyak.
