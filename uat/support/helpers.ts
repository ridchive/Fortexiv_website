import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, type Browser, type Locator, type Page } from '@playwright/test';

export const PASSWORD = 'Password123!';

/** Accounts created by support/seed.php (all verified unless noted). */
export const users = {
  admin: { email: 'admin@e2e.test', name: 'Admin E2E' },
  buyer: { email: 'buyer@e2e.test', name: 'Budi Pembeli' },
  buyer2: { email: 'buyer2@e2e.test', name: 'Citra Pembeli' },
  toggle: { email: 'toggle@e2e.test', name: 'Dodi Nonaktif' },
  reset: { email: 'reset@e2e.test', name: 'Eka Lupa Sandi' },
  unverified: { email: 'unverified@e2e.test', name: 'Fajar Belum Verifikasi' },
  seller: { email: 'seller@e2e.test', name: 'Sari Penjual', store: 'Toko E2E A', className: 'E2E Kelas A' },
  seller2: { email: 'seller2@e2e.test', name: 'Tono Penjual', store: 'Toko E2E B', className: 'E2E Kelas B' },
} as const;

export const projects = {
  a: { className: 'E2E Kelas A', title: 'Proyek Uji Kelas A' },
  b: { className: 'E2E Kelas B', title: 'Proyek Uji Kelas B' },
} as const;

export const products = {
  tote: { name: 'E2E Tote Bag', slug: 'e2e-tote-bag', price: 25000 },
  notebook: { name: 'E2E Notebook', slug: 'e2e-notebook', price: 15000 },
  draftLamp: { name: 'E2E Draft Lamp', slug: 'e2e-draft-lamp', price: 50000 },
  emptyPoster: { name: 'E2E Empty Poster', slug: 'e2e-empty-poster', price: 10000 },
  soldOut: { name: 'E2E Habis Terjual', slug: 'e2e-habis-terjual', price: 9000 },
  postcard: { name: 'E2E Kartu Pos', slug: 'e2e-kartu-pos', price: 8000 },
  lastUnit: { name: 'E2E Stok Terakhir', slug: 'e2e-stok-terakhir', price: 30000 },
  bracelet: { name: 'E2E Gelang Anyam', slug: 'e2e-gelang-anyam', price: 12000 },
} as const;

/** A valid 1×1 PNG, so Laravel's `mimes` rule (which inspects file contents) accepts it. */
export const receiptPng = {
  name: 'bukti-transfer.png',
  mimeType: 'image/png',
  buffer: Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'base64',
  ),
};

/** Formats an integer the way App\Support\Money does: "Rp 25.000". */
export function rupiah(amount: number): string {
  return `Rp ${amount.toLocaleString('id-ID')}`;
}

export async function login(page: Page, email: string, password = PASSWORD): Promise<void> {
  await page.goto('/login');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password', { exact: true }).fill(password);
  await page.getByRole('button', { name: 'Sign in' }).click();
  await expect(accountMenu(page)).toBeVisible();
}

export function accountMenu(page: Page): Locator {
  return page.locator('summary[aria-label="Menu akun"]');
}

export async function logout(page: Page): Promise<void> {
  await accountMenu(page).click();
  await page.getByRole('button', { name: 'Keluar' }).click();
  await expect(accountMenu(page)).toHaveCount(0);
}

/** Opens a fresh browser context (own cookies/session) logged in as the given user. */
export async function pageAs(browser: Browser, email: string): Promise<Page> {
  const context = await browser.newContext();
  const page = await context.newPage();
  await login(page, email);
  return page;
}

/** Reads the stock badge on the product page ("Stok 10" or "Stok habis"). */
export async function stockOf(page: Page, slug: string): Promise<number> {
  await page.goto(`/products/${slug}`);
  const text = await page.locator('.detail-copy .badge').first().innerText();
  return /habis/i.test(text) ? 0 : Number.parseInt(text.replace(/\D+/g, ''), 10);
}

/** The session flash message rendered by the layout (`<div class="flash" role="status">`). */
export function flash(page: Page): Locator {
  return page.locator('main .flash').first();
}

/** The validation error panel rendered by the layout (`role="alert"`). */
export function errors(page: Page): Locator {
  return page.locator('main [role="alert"]').first();
}

export function row(page: Page, text: string | RegExp): Locator {
  return page.getByRole('row').filter({ hasText: text });
}

export function cartItem(page: Page, productName: string): Locator {
  return page.locator('article.cart-i').filter({ has: page.getByRole('heading', { name: productName }) });
}

/** Badge with the number of cart items in the header; absent when the cart is empty. */
export function cartCount(page: Page): Locator {
  return page.locator('a[aria-label="Keranjang"] .n');
}

/** A class-group panel (cart, checkout, order detail, ticket) identified by its class heading. */
export function groupPanel(page: Page, className: string): Locator {
  return page.locator('.panel').filter({ has: page.getByRole('heading', { name: className, exact: true }) });
}

/** Adds a product to the cart from its detail page. */
export async function addToCart(page: Page, slug: string, quantity = 1): Promise<void> {
  await page.goto(`/products/${slug}`);
  await page.getByLabel('Jumlah untuk keranjang').fill(String(quantity));
  await page.getByRole('button', { name: 'Tambah ke Keranjang' }).click();
  await expect(page).toHaveURL(/\/cart$/);
}

/** Fills the pickup contact + ToS on the checkout page, places the order and returns its code. */
export async function placeOrder(page: Page, phone = '081234567890'): Promise<string> {
  await expect(page.getByRole('heading', { level: 1, name: 'Konfirmasi pesanan' })).toBeVisible();
  await page.getByLabel('Nomor WhatsApp pengambil').fill(phone);
  await page.locator('#accepted_terms').check();
  await page.getByRole('button', { name: /^Buat pesanan/ }).click();
  await expect(page).toHaveURL(/\/orders\/FX-\d{8}$/);
  return page.url().split('/').pop()!;
}

export async function uploadProof(page: Page, orderCode: string, method = 'Transfer BCA'): Promise<void> {
  await page.goto(`/orders/${orderCode}`);
  await page.getByLabel('Metode / bank transfer').fill(method);
  await page.getByLabel('Bukti transfer').setInputFiles(receiptPng);
  await page.getByRole('button', { name: 'Kirim bukti transfer' }).click();
  await expect(flash(page)).toHaveText('Bukti transfer berhasil dikirim dan menunggu verifikasi admin.');
}

/** Reads the pickup code shown for one class group on the buyer's ticket page. */
export async function pickupCodeFor(page: Page, orderCode: string, className: string): Promise<string> {
  await page.goto(`/orders/${orderCode}/ticket`);
  const code = (await groupPanel(page, className).locator('.ticket-code').innerText()).trim();
  expect(code).toMatch(/^FTXG-[A-Z0-9]{12}$/);
  return code;
}

/** Unique suffix so specs can create records without colliding across runs. */
export function unique(): string {
  return `${Date.now()}`.slice(-7);
}

const mailLog = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '.data', 'storage', 'logs', 'laravel.log');

function decodeQuotedPrintable(text: string): string {
  return text
    .replace(/=\r?\n/g, '')
    .replace(/=3D/gi, '=');
}

/**
 * Finds the newest link containing `pathFragment` in an email sent to `email`.
 * The e2e server uses MAIL_MAILER=log, so every email is written to the e2e laravel.log.
 */
export async function emailLink(email: string, pathFragment: string): Promise<string> {
  let link: string | undefined;

  await expect
    .poll(() => {
      if (!fs.existsSync(mailLog)) {
        return undefined;
      }
      const entries = fs.readFileSync(mailLog, 'utf8').split(/\r?\n(?=\[\d{4}-\d{2}-\d{2})/);
      const entry = entries.reverse().find((candidate) => candidate.includes(email) && candidate.includes(pathFragment));
      if (!entry) {
        return undefined;
      }
      const escaped = pathFragment.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const body = /Content-Transfer-Encoding:\s*quoted-printable/i.test(entry) ? decodeQuotedPrintable(entry) : entry;
      const matches = body.match(new RegExp(`https?://[^\\s"'<>()\\]]*${escaped}[^\\s"'<>()\\]]*`, 'g'));
      link = matches?.at(-1)?.replace(/&amp;/g, '&');
      return link;
    }, { message: `email with "${pathFragment}" for ${email}`, timeout: 10_000 })
    .toBeTruthy();

  return link!;
}

/** True when the page content is wider than the viewport (horizontal overflow). */
export async function hasHorizontalOverflow(page: Page): Promise<boolean> {
  return page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
}
