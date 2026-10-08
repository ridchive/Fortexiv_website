# FORTEXIV marketplace

FORTEXIV is a Laravel 13 + Blade marketplace for the student-project showcase in `FORTEXIVsI.html`.

## Requirements

- PHP 8.3 or later with `fileinfo`, `pdo_sqlite`, and `sqlite3` enabled
- Composer 2

On Windows, run `php --ini` to find the active `php.ini`. Enable the installed extensions by uncommenting these lines (and make sure `extension_dir` points to your PHP `ext` directory):

```ini
extension=fileinfo
extension=pdo_sqlite
extension=sqlite3
```

Restart terminals and web servers after changing PHP configuration.

## Local setup

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite | Out-Null }
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open <http://127.0.0.1:8000>. The seeder creates the 12 class-project showcase entries and Handmade, Food, Stationery, and Tech categories.

Register as a buyer to shop, or as a seller to manage and publish products. New products start as drafts; add stock before publishing. Admin accounts are intentionally excluded from public registration. Once migrations have run, create the first administrator using:

```powershell
php artisan marketplace:create-admin "Marketplace Admin" admin@example.com
```

The command prompts for a password of at least 12 characters.

Configure manual bank-transfer instructions in `.env` before accepting orders:

```dotenv
PAYMENT_BANK_NAME=BCA
PAYMENT_ACCOUNT_NAME=FORTEXIV
PAYMENT_ACCOUNT_NUMBER=1234567890
```

Checkout remains disabled until all three bank-transfer settings are filled in.

Buyers upload a JPG, PNG, or PDF receipt (up to 5 MB). Receipts remain on Laravel's private local disk and can only be downloaded from the authenticated admin review page. Admin verification issues a pickup ticket; rejection returns reserved stock. Each ticket can only be used once.

## Tests

```powershell
php artisan test
```
