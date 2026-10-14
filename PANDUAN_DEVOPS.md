# Panduan DevOps — Deploy FORTEXIV ke Server Production

Panduan ini memasang FORTEXIV di server Linux, dengan dua cara:

- **Manual**: menjalankan perintah lewat SSH.
- **Otomatis**: memakai GitHub Actions, yang menjalankan test lalu deploy setiap ada push ke `main`.

Kedua cara memakai **struktur folder dan skrip deploy yang sama**. Anda bisa mulai manual, lalu beralih ke otomatis tanpa mengubah server.

Contoh di panduan ini memakai domain `fortexiv.example.sch.id` dan user `deploy`. Sesuaikan dengan milik Anda.

---

## 1. Gambaran arsitektur

```
Internet ──HTTPS──► Nginx ──FastCGI──► PHP-FPM 8.4 (user: deploy) ──► Laravel
                                                                    ├─ SQLite  (shared/database)
                                                                    └─ storage (shared/storage)
```

| Komponen | Pilihan |
|---|---|
| OS | Ubuntu Server 24.04 LTS |
| Web server | Nginx |
| PHP | 8.4 (FPM + CLI) — **wajib ≥ 8.4.1** karena `composer.lock` |
| Database | SQLite (satu file) |
| Build aset | Node.js 22 + Vite (`public/build` tidak ada di git) |
| HTTPS | Let's Encrypt (Certbot) |
| Spesifikasi minimum | 1 vCPU, 1–2 GB RAM, 20 GB disk |

### Struktur folder di server

```
/var/www/fortexiv/
├── deploy.sh                 # skrip deploy (bagian 4)
├── current -> releases/20261014093000   # symlink ke rilis aktif; root Nginx = current/public
├── releases/
│   ├── 20261013150000/
│   └── 20261014093000/       # tiap deploy = folder baru (bisa rollback)
└── shared/                   # data yang bertahan antar rilis
    ├── .env
    ├── database/database.sqlite
    └── storage/              # log, session, cache, gambar produk, BUKTI TRANSFER
```

Setiap deploy membuat folder rilis baru, lalu memindahkan symlink `current` secara atomik. Hasilnya, downtime hampir nol dan rollback bisa dilakukan dengan memindahkan symlink kembali.

---

## 2. Persiapan server (sekali saja)

Jalankan sebagai user dengan akses `sudo`.

### 2.1 Paket sistem

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip sqlite3 nginx ufw

# PHP 8.4
sudo add-apt-repository -y ppa:ondrej/php
sudo apt install -y php8.4-fpm php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml \
  php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-opcache
php -v    # pastikan 8.4.x

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 22 (untuk build Vite)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2.2 Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2.3 User `deploy`

Aplikasi dan PHP-FPM berjalan sebagai user `deploy`. Dengan begitu file yang dibuat saat deploy dan saat aplikasi berjalan punya pemilik yang sama, sehingga tidak ada masalah izin di `storage`.

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo mkdir -p /var/www/fortexiv
sudo chown deploy:deploy /var/www/fortexiv
```

Izinkan `deploy` me-reload PHP-FPM (untuk membersihkan OPcache) tanpa password:

```bash
echo 'deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.4-fpm' | sudo tee /etc/sudoers.d/deploy
sudo chmod 440 /etc/sudoers.d/deploy
```

### 2.4 Konfigurasi PHP-FPM

Edit `/etc/php/8.4/fpm/pool.d/www.conf`:

```ini
user = deploy
group = deploy
listen = /run/php/php8.4-fpm.sock
listen.owner = www-data
listen.group = www-data
```

Naikkan batas unggah. Aplikasi menerima gambar produk dan bukti transfer sampai 5 MB, sedangkan bawaan PHP hanya 2 MB. Buat `/etc/php/8.4/fpm/conf.d/99-fortexiv.ini`:

```ini
upload_max_filesize = 8M
post_max_size = 10M
memory_limit = 256M
expose_php = Off
opcache.enable = 1
opcache.memory_consumption = 128
opcache.validate_timestamps = 0
```

> `opcache.validate_timestamps = 0` membuat PHP tidak memeriksa perubahan file, sehingga lebih cepat. Karena itu PHP-FPM **wajib di-reload setiap deploy**, dan `deploy.sh` sudah melakukannya.

```bash
sudo systemctl restart php8.4-fpm
```

### 2.5 Akses server ke repository GitHub

Repository: `github.com/ridchive/Fortexiv_website`. Jika repository **privat**, buat *deploy key* hanya-baca:

```bash
sudo -u deploy ssh-keygen -t ed25519 -N "" -f /home/deploy/.ssh/id_ed25519 -C "fortexiv-server"
sudo -u deploy cat /home/deploy/.ssh/id_ed25519.pub
sudo -u deploy ssh -o StrictHostKeyChecking=accept-new -T git@github.com   # tambahkan github.com ke known_hosts
```

Tempel public key itu di GitHub: **Settings → Deploy keys → Add deploy key**. Jangan centang *Allow write access*.

### 2.6 Folder shared, database, dan `.env`

```bash
sudo -iu deploy
cd /var/www/fortexiv
mkdir -p releases shared/database \
  shared/storage/app/public shared/storage/app/private \
  shared/storage/framework/{cache/data,sessions,views} shared/storage/logs
touch shared/database/database.sqlite
chmod 700 shared/database shared/storage/app/private
```

Buat `shared/.env` (`nano shared/.env`):

```env
APP_NAME=FORTEXIV
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://fortexiv.example.sch.id

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

PAYMENT_BANK_NAME="Bank ..."
PAYMENT_ACCOUNT_NAME="..."
PAYMENT_ACCOUNT_NUMBER="..."

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/fortexiv/shared/database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.sch.id"
MAIL_FROM_NAME="${APP_NAME}"
```

```bash
chmod 600 shared/.env
```

Hal penting tentang `.env`:

- **`DB_DATABASE` wajib berupa path absolut ke folder `shared`.** Kalau kosong, database dibuat di dalam folder rilis dan **hilang di deploy berikutnya**.
- **`PAYMENT_*` wajib diisi**, karena tanpa itu pembeli tidak bisa checkout.
- **`APP_KEY` diisi setelah deploy pertama** (bagian 3). Jangan pernah mengganti `APP_KEY` setelah aplikasi dipakai, karena session dan data terenkripsi menjadi tidak terbaca.

### 2.7 Nginx

Buat `/etc/nginx/sites-available/fortexiv`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name fortexiv.example.sch.id;
    root /var/www/fortexiv/current/public;
    index index.php;
    charset utf-8;

    client_max_body_size 10m;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }

    error_page 404 /index.php;
}
```

> `$realpath_root` (bukan `$document_root`) penting karena `current` adalah symlink. Dengan begitu PHP selalu membaca rilis yang baru setelah symlink dipindah.

```bash
sudo ln -s /etc/nginx/sites-available/fortexiv /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

HTTPS dipasang **setelah** deploy pertama (bagian 3.3).

---

## 3. Deploy pertama secara manual

### 3.1 Pasang skrip deploy

Salin skrip di **bagian 4** ke `/var/www/fortexiv/deploy.sh`, lalu:

```bash
sudo -iu deploy
chmod +x /var/www/fortexiv/deploy.sh
```

### 3.2 Jalankan deploy pertama

```bash
/var/www/fortexiv/deploy.sh main
```

Lalu, **hanya sekali**, buat APP_KEY, isi data awal, dan buat akun admin:

```bash
cd /var/www/fortexiv/current
php artisan key:generate --force        # menulis APP_KEY ke shared/.env
php artisan db:seed --force             # kategori + 12 proyek kelas
php artisan marketplace:create-admin "Nama Admin" admin@example.sch.id   # password ≥ 12 karakter
php artisan optimize                    # cache ulang config dengan APP_KEY baru
sudo systemctl reload php8.4-fpm
```

> Jangan jalankan `db:seed` lagi di deploy berikutnya kecuali memang ingin menyinkronkan ulang kategori dan proyek dari seeder. Seeder memakai `updateOrCreate`, sehingga perubahan judul atau cerita proyek yang dibuat langsung di database akan tertimpa.

### 3.3 Pasang HTTPS

Pastikan DNS domain sudah mengarah ke IP server, lalu:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d fortexiv.example.sch.id --redirect -m admin@example.sch.id --agree-tos
sudo certbot renew --dry-run     # cek perpanjangan otomatis
```

### 3.4 Verifikasi

```bash
curl -fsS https://fortexiv.example.sch.id/up    # harus 200
```

Buka situs, login sebagai admin, lalu coba alur singkat dengan akun uji: pembeli checkout, unggah bukti, verify, dan validasi ticket.

### 3.5 Update manual berikutnya

Setiap kali ada perubahan di `main`:

```bash
ssh deploy@fortexiv.example.sch.id
/var/www/fortexiv/deploy.sh main          # atau nama tag/commit: deploy.sh v1.2.0
```

---

## 4. Skrip deploy (`/var/www/fortexiv/deploy.sh`)

Skrip ini dipakai oleh deploy manual **dan** GitHub Actions.

```bash
#!/usr/bin/env bash
# Pemakaian: deploy.sh [branch|tag|commit]   (default: main)
set -euo pipefail

APP_DIR=/var/www/fortexiv
REPO=git@github.com:ridchive/Fortexiv_website.git   # repo publik: https://github.com/ridchive/Fortexiv_website.git
REF="${1:-main}"
KEEP_RELEASES=5
PHP=/usr/bin/php8.4

RELEASE="$APP_DIR/releases/$(date +%Y%m%d%H%M%S)"
SHARED="$APP_DIR/shared"

exec 9>"$APP_DIR/.deploy.lock"
flock -n 9 || { echo "Deploy lain sedang berjalan."; exit 1; }

echo "==> Rilis baru: $RELEASE ($REF)"
git clone --quiet "$REPO" "$RELEASE"
cd "$RELEASE"
git checkout --quiet "$REF"
git rev-parse HEAD > REVISION

echo "==> Tautkan data shared"
rm -rf storage database/database.sqlite
ln -s "$SHARED/storage" storage
ln -s "$SHARED/.env" .env

echo "==> Dependensi PHP"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

echo "==> Build aset frontend"
npm ci --no-audit --no-fund
npm run build
rm -rf node_modules

$PHP artisan storage:link

echo "==> Migrasi database"
$PHP artisan down --retry=15 || true
trap '$PHP "$RELEASE/artisan" up || true' EXIT
cp "$SHARED/database/database.sqlite" "$SHARED/database/pre-deploy.sqlite"
$PHP artisan migrate --force

echo "==> Cache config/route/view"
$PHP artisan optimize

echo "==> Aktifkan rilis"
ln -sfn "$RELEASE" "$APP_DIR/current.tmp"
mv -Tf "$APP_DIR/current.tmp" "$APP_DIR/current"
sudo /usr/bin/systemctl reload php8.4-fpm
$PHP "$APP_DIR/current/artisan" queue:restart
$PHP "$APP_DIR/current/artisan" up
trap - EXIT

echo "==> Bersihkan rilis lama (simpan $KEEP_RELEASES)"
ls -1dt "$APP_DIR"/releases/*/ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf

echo "==> Selesai: $(cat "$APP_DIR/current/REVISION")"
```

Langkah yang dikerjakan skrip:

1. Mengunci deploy agar tidak berjalan bersamaan.
2. Clone kode ke folder rilis baru, lalu menautkan `storage` dan `.env` dari `shared`.
3. Menjalankan `composer install` tanpa paket dev (Boost, PHPUnit, dan sejenisnya tidak ikut).
4. Build Vite, lalu menghapus `node_modules`.
5. Mengaktifkan maintenance mode sebentar, menyalin database ke `pre-deploy.sqlite` sebagai cadangan cepat, lalu menjalankan migrasi.
6. Menjalankan `artisan optimize`, memindahkan symlink `current`, reload PHP-FPM, dan menonaktifkan maintenance mode. Jika ada langkah yang gagal setelah maintenance aktif, `trap` tetap menjalankan `artisan up`.
7. Menyimpan 5 rilis terakhir dan menghapus sisanya.

> **Penting:** `npm ci` membutuhkan `package-lock.json` di repository. Commit file itu sebelum deploy pertama.

---

## 5. Deploy otomatis dengan GitHub Actions

Alurnya: **push ke `main` → test di GitHub → (opsional) persetujuan → SSH ke server → `deploy.sh <commit>` → cek `/up`.**

### 5.1 Kunci SSH untuk GitHub Actions

Di komputer Anda (bukan di server), buat kunci khusus CI:

```bash
ssh-keygen -t ed25519 -N "" -f fortexiv-ci -C "github-actions"
```

- Isi `fortexiv-ci.pub` → tambahkan ke `/home/deploy/.ssh/authorized_keys` di server.
- Ambil fingerprint server: `ssh-keyscan -t ed25519 fortexiv.example.sch.id`.

### 5.2 Secrets dan environment di GitHub

Buka **Settings → Environments → New environment → `production`**:

| Jenis | Nama | Isi |
|---|---|---|
| Secret | `SSH_PRIVATE_KEY` | Isi file `fortexiv-ci` (private key) |
| Secret | `SSH_KNOWN_HOSTS` | Output `ssh-keyscan` |
| Secret | `SSH_HOST` | `fortexiv.example.sch.id` atau IP server |
| Secret | `SSH_USER` | `deploy` |
| Variable | `APP_URL` | `https://fortexiv.example.sch.id` |

Opsional: aktifkan **Required reviewers** di environment `production`, agar setiap deploy harus disetujui terlebih dahulu.

### 5.3 Workflow `.github/workflows/deploy.yml`

```yaml
name: Test & Deploy

on:
  push:
    branches: [main]
  workflow_dispatch:

concurrency:
  group: production
  cancel-in-progress: false

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo_sqlite, sqlite3, fileinfo, mbstring, zip
          coverage: none

      - uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm

      - run: composer install --prefer-dist --no-interaction --no-progress
      - run: npm ci && npm run build
      - run: cp .env.example .env && php artisan key:generate
      - run: php artisan test --compact

  deploy:
    needs: test
    runs-on: ubuntu-latest
    environment: production
    steps:
      - name: Siapkan SSH
        run: |
          install -m 700 -d ~/.ssh
          echo "${{ secrets.SSH_PRIVATE_KEY }}" > ~/.ssh/id_ed25519
          chmod 600 ~/.ssh/id_ed25519
          echo "${{ secrets.SSH_KNOWN_HOSTS }}" > ~/.ssh/known_hosts

      - name: Deploy
        run: ssh ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} "/var/www/fortexiv/deploy.sh ${{ github.sha }}"

      - name: Health check
        run: curl --fail --silent --show-error --retry 5 --retry-delay 5 "${{ vars.APP_URL }}/up"
```

Catatan workflow:

- Deploy memakai **SHA commit** yang sudah lulus test, bukan sekadar "main terbaru".
- `concurrency` mencegah dua deploy berjalan bersamaan.
- Jika test gagal, job deploy tidak dijalankan.
- Untuk deploy ulang atau versi tertentu, jalankan workflow secara manual dari tab **Actions → Run workflow**.

### 5.4 Alternatif: Laravel Cloud

Jika tidak ingin mengelola server sendiri, aplikasi bisa di-deploy ke [Laravel Cloud](https://cloud.laravel.com/), yang menangani server, HTTPS, dan deploy dari GitHub secara otomatis. Perhatikan: di Cloud, file lokal tidak permanen. Database perlu dipindah ke MySQL/Postgres, dan file unggahan ke object storage (disk S3-compatible). Proyek ini sudah punya skill Boost `deploying-to-cloud` untuk membantu proses tersebut.

---

## 6. Operasional

### 6.1 Scheduler dan queue worker

Saat ini aplikasi **belum memakai** job queue maupun jadwal. Pasang keduanya sekarang agar fitur berikutnya, seperti notifikasi atau pembatalan otomatis pesanan kedaluwarsa (lihat `DOKUMENTASI_PENGEMBANG.md`), langsung berjalan.

**Scheduler** (`sudo -u deploy crontab -e`):

```cron
* * * * * cd /var/www/fortexiv/current && php artisan schedule:run >> /dev/null 2>&1
```

**Queue worker** (`/etc/systemd/system/fortexiv-queue.service`):

```ini
[Unit]
Description=FORTEXIV queue worker
After=network.target

[Service]
User=deploy
Group=deploy
WorkingDirectory=/var/www/fortexiv/current
ExecStart=/usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now fortexiv-queue
```

`deploy.sh` menjalankan `queue:restart` di setiap deploy. Worker lalu berhenti dengan rapi dan dijalankan ulang oleh systemd dengan kode rilis baru.

### 6.2 Backup

Data penting yang harus di-backup:

- **`shared/database/database.sqlite`**: semua data.
- **`shared/storage/app/private`**: bukti transfer.
- **`shared/storage/app/public`**: gambar produk.
- **`shared/.env`**: berisi `APP_KEY`.

Buat `/var/www/fortexiv/backup.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail
APP_DIR=/var/www/fortexiv
DEST=/home/deploy/backups
STAMP=$(date +%Y%m%d-%H%M)
mkdir -p "$DEST"

# Backup SQLite yang aman walau aplikasi sedang berjalan
sqlite3 "$APP_DIR/shared/database/database.sqlite" ".backup '$DEST/db-$STAMP.sqlite'"
gzip "$DEST/db-$STAMP.sqlite"

tar -czf "$DEST/files-$STAMP.tar.gz" -C "$APP_DIR/shared" storage/app .env

# Simpan 14 hari terakhir
find "$DEST" -type f -mtime +14 -delete
```

```bash
chmod +x /var/www/fortexiv/backup.sh
# crontab user deploy — setiap hari jam 02:00
0 2 * * * /var/www/fortexiv/backup.sh >> /home/deploy/backups/backup.log 2>&1
```

Salin backup ke **lokasi lain di luar server**, misalnya `rclone` ke Google Drive/S3 atau `rsync` ke server lain. Backup yang hanya ada di server yang sama akan ikut hilang jika server rusak. Lakukan juga **uji restore** secara berkala.

**Restore:**

```bash
cd /var/www/fortexiv/current && php artisan down
gunzip -c /home/deploy/backups/db-YYYYMMDD-HHMM.sqlite.gz > /var/www/fortexiv/shared/database/database.sqlite
php artisan up
```

### 6.3 Rollback

```bash
cd /var/www/fortexiv
ls -1t releases/                                   # pilih rilis sebelumnya
ln -sfn releases/<RILIS_SEBELUMNYA> current.tmp && mv -Tf current.tmp current
sudo systemctl reload php8.4-fpm
php current/artisan queue:restart
```

Jika rilis yang dibatalkan **mengubah struktur database**, kembalikan juga database dari `shared/database/pre-deploy.sqlite`, dengan risiko kehilangan transaksi setelah deploy. Cara lain, jalankan `php artisan migrate:rollback` dari folder rilis yang baru **sebelum** memindahkan symlink.

### 6.4 Log dan pemantauan

- **Log aplikasi:** `shared/storage/logs/laravel-YYYY-MM-DD.log` (`LOG_CHANNEL=daily`).
- **Log Nginx:** `/var/log/nginx/error.log`.
- **Log PHP-FPM:** `/var/log/php8.4-fpm.log`.
- **Health check:** `GET /up`. Daftarkan ke layanan uptime monitor (UptimeRobot, Better Stack, dan sejenisnya).
- **Ruang disk:** pantau dengan `df -h`, terutama karena bukti transfer dan gambar terus bertambah.

### 6.5 Maintenance mode

```bash
php /var/www/fortexiv/current/artisan down --refresh=15   # pengunjung melihat halaman 503
php /var/www/fortexiv/current/artisan up
```

---

## 7. Catatan khusus SQLite: transaksi bersamaan

SQLite cukup untuk skala sekolah, **asal konfigurasi transaksinya diubah**. Dengan konfigurasi saat ini, dua checkout yang terjadi hampir bersamaan bisa membuat salah satunya gagal dengan error `database is locked` (halaman 500).

### 7.1 Mengapa terjadi

1. **SQLite hanya mengizinkan satu penulis untuk seluruh file database.** Tidak ada kunci per baris.
2. **`lockForUpdate()` tidak berpengaruh di SQLite.** `SQLiteGrammar::compileLock()` mengembalikan string kosong, sehingga `SELECT ... FOR UPDATE` di kode checkout menjadi `SELECT` biasa.
3. **Laravel memulai transaksi dengan `BEGIN DEFERRED`** (`transaction_mode` bawaan). Transaksi belum mengunci apa pun sampai ada perintah pertama.
4. Transaksi checkout berpola **baca lalu tulis**: `SELECT cart_items` → `SELECT products` → `UPDATE products` (decrement stok) → `INSERT orders`. Transaksi mendapat kunci baca (SHARED) saat `SELECT`, lalu harus **naik** ke kunci tulis saat `UPDATE`.
5. Jika transaksi lain sudah menulis di antara kedua langkah itu, kenaikan kunci tersebut tidak bisa dilakukan:
   - **Mode journal bawaan (rollback):** kedua transaksi saling menunggu (*deadlock*). SQLite langsung mengembalikan `SQLITE_BUSY` ke salah satunya **tanpa menunggu `busy_timeout`**.
   - **Mode WAL:** data yang dibaca transaksi sudah usang (`SQLITE_BUSY_SNAPSHOT`), sehingga menunggu juga tidak ada gunanya, dan error langsung dikembalikan.
6. **`DB::transaction()` hanya mencoba 1 kali**, sehingga error langsung sampai ke pengguna.

Karena itu, **menaikkan `busy_timeout` atau menyalakan WAL tidak menyelesaikan masalah**. `busy_timeout` hanya menolong penulisan tunggal (autocommit), misalnya penulisan session. PDO SQLite sudah memakai timeout bawaan 60 detik, sehingga penulisan session tidak pernah gagal di pengujian.

**Data tetap aman.** Transaksi yang gagal di-rollback penuh. Di semua pengujian, jumlah stok yang berkurang selalu sama dengan jumlah barang yang dipesan, tanpa stok minus atau penjualan melebihi stok. Isi keranjang pembeli juga tetap ada, sehingga pembeli bisa mencoba checkout lagi.

### 7.2 Hasil pengukuran

Pengukuran dilakukan dengan menjalankan banyak proses PHP 8.4 secara bersamaan. Setiap proses menjalankan isi transaksi `MarketplaceController::checkout` yang sama persis (melalui Eloquent) terhadap salinan skema aplikasi. Lingkungan uji: laptop Windows 11 dengan SSD.

Ada tiga konfigurasi yang dibandingkan:

| Nama | `transaction_mode` | `journal_mode` | `busy_timeout` |
|---|---|---|---|
| **current** (saat ini) | DEFERRED | rollback (bawaan) | bawaan PDO (60 dtk) |
| **wal** | DEFERRED | WAL | 5000 ms |
| **immediate** (rekomendasi) | **IMMEDIATE** | WAL | 5000 ms |

**A. Kontensi penuh:** setiap proses langsung checkout lagi tanpa jeda (skenario terburuk).

| Checkout bersamaan | current | wal | immediate |
|---|---|---|---|
| 2 | 10–15% gagal | 20% gagal | **0%** |
| 5 | 16% gagal | 13% gagal | **0%** |
| 10 | 15% gagal | 20,5% gagal | **0%** |
| 20 | 12,5% gagal (p95 1,6 dtk, maks 6,7 dtk) | 12% gagal | **0%** (p95 76 ms) |
| 10, dengan `DB::transaction(..., 3)` | 12,5% gagal | 10,5% gagal | — |

Mencoba ulang 3 kali (retry) hampir tidak membantu, karena transaksi yang diulang langsung bertabrakan lagi.

**B. Trafik bercampur:** checkout diselingi penulisan session dari request lain, dengan jeda acak antar aksi.

| Beban | current | wal | immediate |
|---|---|---|---|
| ~2,5 checkout/dtk | 0% (0/125) | — | 0% (0/125) |
| ~10 checkout/dtk + ~50 request/dtk | **4,0%** gagal | 7,7% gagal | **0%** (p95 25 ms) |
| ~40 checkout/dtk + ~200 request/dtk | **3,2%** gagal, p95 2,0 dtk, maks 12,5 dtk | — | **0%** (p95 173 ms) |

Cara membaca angka di atas:

- **Yang menentukan adalah tumpang-tindih, bukan jumlah akun.** Transaksi checkout berlangsung sekitar 3–12 ms. Error hanya muncul jika dua transaksi tulis bertumpuk di jendela waktu itu.
- **Trafik sekolah normal** (misalnya ratusan siswa, kurang dari 1 checkout per detik): error **jarang, tetapi bisa terjadi**.
- **Saat ramai** (pembukaan penjualan, jam istirahat, pengumuman di grup kelas, sekitar 10 checkout per detik): **sekitar 1 dari 25 checkout gagal**, ditambah waktu tunggu sampai beberapa detik.
- Pola yang sama juga berlaku untuk transaksi lain yang membaca lalu menulis: unggah bukti transfer, verify, reject, dan validasi pickup. Hanya saja, transaksi admin jarang terjadi bersamaan.

> Angka ini indikatif. Server Linux dengan SSD biasanya sama atau lebih cepat. Pada request HTTP sungguhan, waktu transaksinya sama karena middleware dan render view berjalan di luar transaksi.

### 7.3 Solusi: `transaction_mode = IMMEDIATE`

Dengan `BEGIN IMMEDIATE`, transaksi **langsung mengambil kunci tulis di awal**. Transaksi lain mengantre lewat `busy_timeout`, dan tidak pernah terjadi kenaikan kunci di tengah transaksi, sehingga deadlock tidak mungkin terjadi. Hasil pengukuran: **0 kegagalan di semua skenario**, dan waktu respons justru lebih cepat karena tidak ada transaksi yang terbuang.

Perubahan yang diusulkan untuk `config/database.php` (`connections.sqlite`). Ini perubahan kode, jadi perlu persetujuan tim pengembang:

```php
'busy_timeout' => env('DB_BUSY_TIMEOUT', 5000),
'journal_mode' => env('DB_JOURNAL_MODE', 'wal'),
'synchronous' => env('DB_SYNCHRONOUS', 'normal'),
'transaction_mode' => env('DB_TRANSACTION_MODE', 'IMMEDIATE'),
```

Konsekuensi perubahan ini:

- Semua `DB::transaction()` menjadi penulis tunggal yang mengantre. Ini tidak jadi masalah, karena semua transaksi di aplikasi memang menulis. Kapasitas tulis terukur sekitar 250 checkout per detik secara berurutan, jauh di atas kebutuhan sekolah.
- `busy_timeout` 5000 ms membatasi antrean. Jika lebih dari 5 detik, request gagal dengan cepat, bukan menggantung sampai 60 detik.
- **WAL membuat dua file tambahan**, `database.sqlite-wal` dan `database.sqlite-shm`, yang harus berada di folder yang sama dan bisa ditulis. Saat backup, **selalu pakai `sqlite3 ... ".backup ..."`** (bagian 6.2), jangan menyalin file `.sqlite` saja. Perintah `cp` untuk `pre-deploy.sqlite` di `deploy.sh` juga sebaiknya diganti dengan `sqlite3 "$SHARED/database/database.sqlite" ".backup '$SHARED/database/pre-deploy.sqlite'"`.
- Tidak cocok untuk penyimpanan jaringan seperti NFS. Simpan file database di disk lokal server.

### 7.4 Kapan pindah ke MySQL/MariaDB/PostgreSQL

- Aplikasi dijalankan di **lebih dari satu server**, karena SQLite hanya bisa diakses dari satu mesin.
- Beban tulis terus-menerus mendekati ratusan transaksi per detik, atau ada proses tulis panjang (misalnya laporan/ekspor besar di dalam transaksi) yang membuat antrean lama.
- Butuh kunci per baris yang sesungguhnya. `lockForUpdate()` di kode sudah siap dan akan langsung berfungsi di MySQL/PostgreSQL.

Untuk pindah, ubah `DB_CONNECTION` dan kredensialnya di `.env`, jalankan `php artisan migrate --force` di database baru, lalu pindahkan datanya.

---

## 8. Checklist keamanan production

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` sudah dibuat dan di-backup, `.env` ber-permission `600`.
- [ ] HTTPS aktif, HTTP diarahkan ke HTTPS, dan `SESSION_SECURE_COOKIE=true`.
- [ ] Root Nginx adalah `current/public`, bukan root project. File seperti `index.html` atau `login.html` di root repo tidak boleh terjangkau.
- [ ] `storage/app/private` (bukti transfer) tidak bisa diakses langsung lewat URL.
- [ ] Firewall hanya membuka SSH, 80, dan 443.
- [ ] Login SSH memakai kunci. Matikan `PasswordAuthentication` dan login root di `/etc/ssh/sshd_config`.
- [ ] `sudo apt install unattended-upgrades` untuk pembaruan keamanan otomatis.
- [ ] Akun admin memakai password kuat (≥ 12 karakter), dan hanya dibuat lewat `marketplace:create-admin`.
- [ ] Backup harian berjalan, tersalin ke luar server, dan sudah pernah diuji restore.

---

## 9. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `composer install` gagal: *requires php >=8.4.1* | CLI memakai PHP lama. | `php -v`. Gunakan `/usr/bin/php8.4` atau `sudo update-alternatives --set php /usr/bin/php8.4`. |
| Halaman 500 setelah deploy | Config cache lama, atau `.env` salah. | Lihat log di `shared/storage/logs`. Jalankan `php artisan optimize:clear && php artisan optimize`, lalu reload FPM. |
| *Unable to locate file in Vite manifest* | Build aset gagal atau terlewat. | Cek output `npm run build` di log deploy. |
| Perubahan kode tidak terlihat | OPcache belum di-reload. | `sudo systemctl reload php8.4-fpm`. |
| Unggah gambar/bukti gagal (413 atau error validasi) | Batas unggah Nginx atau PHP. | Cek `client_max_body_size` serta `upload_max_filesize`/`post_max_size` (bagian 2.4 dan 2.7). |
| Gambar produk tidak tampil | Symlink `public/storage` belum dibuat. | `php artisan storage:link` di folder `current`. |
| Checkout error 503 | `PAYMENT_*` kosong. | Isi di `shared/.env`, lalu jalankan `php artisan optimize` dan reload FPM. |
| *database is locked* | Transaksi DEFERRED bertabrakan di SQLite. | Set `transaction_mode` IMMEDIATE (bagian 7.3). |
| Data hilang setelah deploy | `DB_DATABASE` tidak menunjuk ke `shared`. | Perbaiki `.env`, lalu restore dari backup atau `pre-deploy.sqlite`. |
| GitHub Actions: *Host key verification failed* | `SSH_KNOWN_HOSTS` salah. | Ulangi `ssh-keyscan` dan perbarui secret. |
| GitHub Actions: *Permission denied (publickey)* | Public key CI belum ada di `authorized_keys`. | Tambahkan ke `/home/deploy/.ssh/authorized_keys` (permission `600`). |
| Deploy: *Deploy lain sedang berjalan* | Ada deploy lain yang masih aktif, atau deploy sebelumnya berhenti mendadak. | Tunggu deploy lain selesai. Kunci `flock` otomatis lepas saat prosesnya berhenti. |
