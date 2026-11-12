# UAT FORTEXIV

Folder ini berisi **User Acceptance Test**: skenario penerimaan yang diturunkan dari dokumen requirement
(BRD, PRD, dan dokumen handoff fungsional). Isinya terpisah dari `e2e/`. Suite e2e menguji apakah fitur bekerja secara teknis, sedangkan UAT menguji apakah requirement bisnis terpenuhi.

| Isi | Fungsi |
|---|---|
| `skenario-uat.md` | Katalog 59 skenario (UAT-01 s.d. UAT-59): sumber requirement, prasyarat, langkah, hasil diharapkan, status fitur, kolom hasil aktual & keputusan lulus |
| `tests/` | Skenario yang diotomasi dengan Playwright; judul setiap test diawali ID UAT |
| `support/` | Server uji terisolasi (port 8002, database & storage di `uat/.data`), data uji, dan helper |

## Menjalankan

```bash
cd uat
npm install                 # sekali saja
npm run install:browsers    # sekali saja (Chromium)

npm run test:watch          # browser terlihat + gerakan diperlambat, cocok untuk ditonton
npm test                    # tanpa tampilan (lebih cepat)
npm run report              # buka laporan HTML hasil terakhir
```

- Jalankan satu file: `npm run test:watch -- tests/uat-04-alur-pesanan.spec.ts`
- Atur kecepatan: setel `E2E_SLOWMO` (milidetik per aksi, default 400) di shell sebelum menjalankan.
- PHP 8.4 dicari otomatis di `C:/xampp/php84/php.exe`. Kalau lokasinya berbeda, setel `E2E_PHP`.
- Setiap kali dijalankan, database uji direset dan diisi ulang. Database aplikasi utama tidak tersentuh.

## Membaca hasil

- **Passed**: skenario lulus.
- **Failed**: requirement tidak terpenuhi atau ada regresi. Catat di kolom *Hasil aktual* pada `skenario-uat.md`.
- **Skipped (fixme)**: gap yang sudah diketahui. Fitur belum dibangun (❌) atau perlu dieksekusi manual (🖐).

Skenario manual (UAT-09 jendela PO, UAT-45 QR kamera) dijalankan dengan mengikuti langkah di `skenario-uat.md`.
