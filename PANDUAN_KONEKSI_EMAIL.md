# Panduan koneksi email FORTEXIV

FORTEXIV sudah memiliki alur email verifikasi akun dan reset password. Pengiriman email menggunakan konfigurasi Laravel di `config/mail.php` dan nilai dari `.env`.

## Status konfigurasi saat ini

Konfigurasi aplikasi saat panduan ini dibuat masih menggunakan `MAIL_MAILER=log`. Email tidak dikirim ke inbox, melainkan ditulis ke log aplikasi. Ganti konfigurasi mailer ke SMTP untuk mengirim email sungguhan.

## 1. Siapkan akun atau layanan SMTP

Gunakan SMTP dari penyedia email yang akan mengirim email aplikasi, misalnya Google Workspace, layanan hosting, atau penyedia email transaksional. Ambil host, port, metode enkripsi, username, dan password SMTP dari dashboard penyedia tersebut.

Jika menggunakan Gmail pribadi, aktifkan verifikasi 2 langkah lalu buat **App Password** khusus. Jangan gunakan password login Gmail biasa. Untuk domain sendiri, ikuti petunjuk penyedia untuk mengotorisasi domain pengirim (misalnya SPF dan DKIM).

## 2. Atur `.env`

Edit `.env` di server atau lingkungan lokal. Contoh berikut menggunakan SMTP Gmail dengan STARTTLS pada port 587:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat-email-pengirim@gmail.com
MAIL_PASSWORD=password-aplikasi-smtp
MAIL_FROM_ADDRESS=alamat-email-pengirim@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Gunakan nilai dari penyedia SMTP jika tidak memakai Gmail. `MAIL_FROM_ADDRESS` sebaiknya merupakan alamat yang diizinkan penyedia untuk mengirim email. Jika penyedia meminta koneksi TLS implisit pada port 465, gunakan pengaturan scheme dan port yang mereka berikan.

Jangan menaruh kredensial SMTP di kode, dokumentasi, atau Git. Jangan mengubah `.env.example` menjadi tempat menyimpan password; file itu hanya untuk contoh konfigurasi.

## 3. Muat ulang konfigurasi

Setelah menyimpan `.env`, jalankan dari root proyek:

```bash
php artisan config:clear
```

Untuk deployment yang menggunakan cache konfigurasi, bangun kembali cache setelah environment variable sudah tersedia:

```bash
php artisan config:cache
```

Pastikan `APP_URL` menunjuk ke URL publik aplikasi yang benar, terutama di staging/production. Link verifikasi akun adalah signed URL dan host aplikasi yang salah dapat membuat link tidak valid.

## 4. Uji pengiriman

1. Pastikan SMTP dapat diakses dari server aplikasi; port SMTP mungkin diblokir oleh firewall atau hosting.
2. Daftarkan akun uji dengan alamat email yang dapat diakses. Setelah pendaftaran, aplikasi mengirim link verifikasi dan menampilkan halaman verifikasi.
3. Jika akun sudah ada, gunakan tombol kirim ulang di halaman verifikasi.
4. Uji reset password melalui tautan **Forgot your password?** di halaman login.
5. Periksa inbox, folder spam, serta log Laravel bila email tidak diterima.

Alamat route terkait: `verification.send` untuk kirim ulang verifikasi dan `password.email` untuk permintaan reset. Endpoint reset dibatasi maksimal 6 permintaan per menit. Respons reset sengaja tidak mengungkap apakah sebuah alamat email terdaftar.

## 5. Periksa hasil dan kendala umum

Nilai mailer aktif dapat diperiksa tanpa menampilkan password:

```bash
php artisan tinker --execute 'dump(config("mail.default"), config("mail.mailers.smtp.host"), config("mail.mailers.smtp.port"));'
```

Jika output masih menunjukkan `log`, periksa `.env`, jalankan `php artisan config:clear`, lalu ulangi pemeriksaan. Jika menggunakan worker queue untuk pekerjaan lain, restart worker setelah perubahan konfigurasi.

Kendala umum:

- **Authentication failed**: periksa username dan password SMTP; Gmail memerlukan App Password.
- **Connection timed out/refused**: periksa host, port, enkripsi, DNS, dan aturan firewall/hosting.
- **Email terkirim tetapi masuk spam**: periksa reputasi pengirim dan autentikasi domain SPF/DKIM.
- **Link verifikasi ditolak**: pastikan `APP_URL` sesuai URL yang dipakai membuka aplikasi dan gunakan tautan yang masih berlaku.
- **Email tidak ditemukan saat masih memakai `log`**: cari isi email di log Laravel, biasanya `storage/logs/laravel.log`; mailer `log` memang tidak mengirim email ke penerima.

## Kode yang sudah menangani email

- `App\Models\User` mengimplementasikan `MustVerifyEmail`.
- `AuthController` meminta link verifikasi setelah pendaftaran, mendukung kirim ulang, dan menangani permintaan reset password.
- `App\Services\EmailService` meneruskan pengiriman notifikasi verifikasi dan reset ke fitur Laravel.
- `config/mail.php` menyediakan mailer `smtp`; `.env.example` menunjukkan nilai bawaan `log` untuk pengembangan lokal.

Tes feature yang ada menggunakan fake notification/mail sehingga memeriksa alur aplikasi, bukan koneksi ke server SMTP sungguhan:

```bash
php artisan test tests/Feature/EmailVerificationTest.php tests/Feature/PasswordResetTest.php tests/Feature/Services/EmailServiceTest.php --compact
```
