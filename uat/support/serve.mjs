// Prepares an isolated database + storage folder, seeds e2e fixtures, then serves the app.
// Usage: node support/serve.mjs <port>
import { execFileSync, spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const port = process.argv[2] ?? '8001';
const e2eDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const appDir = path.resolve(e2eDir, '..');
const dataDir = path.join(e2eDir, '.data');
const dbPath = path.join(dataDir, 'uat.sqlite');
const storageDir = path.join(dataDir, 'storage');

// The app needs PHP >= 8.4.1. Set E2E_PHP to the binary when `php` on PATH is older.
const php = process.env.E2E_PHP
  ?? ['C:/xampp/php84/php.exe'].find((candidate) => fs.existsSync(candidate))
  ?? 'php';

fs.rmSync(dataDir, { recursive: true, force: true });
for (const dir of ['app/public', 'app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs']) {
  fs.mkdirSync(path.join(storageDir, dir), { recursive: true });
}
fs.writeFileSync(dbPath, '');

/** Formats a date shifted by `days` from now as "YYYY-MM-DD HH:mm:ss" (UTC, the app timezone). */
const daysFromNow = (days) => new Date(Date.now() + days * 86_400_000).toISOString().slice(0, 19).replace('T', ' ');

const env = {
  ...process.env,
  APP_ENV: 'uat',
  APP_DEBUG: 'true',
  APP_URL: `http://127.0.0.1:${port}`,
  APP_LOCALE: 'en',
  DB_CONNECTION: 'sqlite',
  DB_DATABASE: dbPath,
  LARAVEL_STORAGE_PATH: storageDir,
  SESSION_DRIVER: 'database',
  CACHE_STORE: 'database',
  QUEUE_CONNECTION: 'sync',
  // Emails are written to storage/logs/laravel.log so specs can follow verification/reset links.
  MAIL_MAILER: 'log',
  MAIL_LOG_CHANNEL: 'single',
  MAIL_FROM_ADDRESS: 'noreply@e2e.test',
  MAIL_FROM_NAME: 'FORTEXIV E2E',
  LOG_CHANNEL: 'single',
  PAYMENT_BANK_NAME: 'Bank E2E',
  PAYMENT_ACCOUNT_NAME: 'Koperasi FORTEXIV',
  PAYMENT_ACCOUNT_NUMBER: '1234567890',
  // APP_ENV=e2e gets no local defaults from PurchaseWindow, so the PO window must be explicit (open now).
  MARKETPLACE_PO_START: daysFromNow(-7),
  MARKETPLACE_PO_END: daysFromNow(14),
  MARKETPLACE_PAYMENT_DEADLINE: daysFromNow(16),
  MARKETPLACE_EVENT_AT: daysFromNow(25),
  MARKETPLACE_EVENT_LOCATION: 'Aula E2E, Jl. Pengujian No. 1, Bandung',
  MARKETPLACE_PICKUP_INSTRUCTIONS: 'Instruksi pickup E2E: tunjukkan kode pickup ke meja kelas.',
  MARKETPLACE_BANNER: 'Banner E2E: satu periode PO untuk semua kelas.',
  MARKETPLACE_TERMS_VERSION: 'e2e-1.0',
};
// variables_order=EGPCS exposes the env above through $_ENV, which Laravel reads before .env
const phpArgs = ['-d', 'variables_order=EGPCS'];

execFileSync(php, [...phpArgs, path.join(e2eDir, 'support', 'seed.php')], { cwd: appDir, env, stdio: 'inherit' });

const server = spawn(
  php,
  // Laravel's router script treats the working directory as the public root.
  [...phpArgs, '-q', '-S', `127.0.0.1:${port}`, path.join(appDir, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')],
  { cwd: path.join(appDir, 'public'), env, stdio: 'inherit' },
);
const stop = () => server.kill();
process.on('SIGINT', stop);
process.on('SIGTERM', stop);
process.on('exit', stop);
server.on('exit', (code) => process.exit(code ?? 0));
