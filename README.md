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

Configure at least one payment method in `.env` before accepting orders. For manual bank transfer:

```dotenv
PAYMENT_BANK_NAME=BCA
PAYMENT_ACCOUNT_NAME=FORTEXIV
PAYMENT_ACCOUNT_NUMBER=1234567890
```

To enable Midtrans Snap, add a Sandbox Server Key (or a Production Server Key when going live):

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
```

In the Midtrans dashboard, set the Payment Notification URL to `https://<your-public-domain>/api/midtrans/notifications`. Use a publicly reachable HTTPS URL. Checkout shows configured payment methods. If no payment method is configured, an order can still be saved, but buyers should wait for official payment instructions before paying.

After deploying the changes, run `php artisan migrate` to add the Midtrans payment fields.

For manual transfers, buyers upload a JPG, PNG, or PDF receipt (up to 5 MB). Receipts remain on Laravel's private local disk and can only be downloaded from the authenticated admin review page. Admin verification or a confirmed Midtrans payment issues a pickup ticket; manual rejection or a failed/expired Midtrans transaction returns reserved stock. Each ticket can only be used once.

A paid order is split into class/seller groups, and each group can be handed over independently by its seller or an admin. The order is marked picked up only after every class group has been handed over; repeat handovers are rejected.

## Tests

```powershell
php artisan test
```
