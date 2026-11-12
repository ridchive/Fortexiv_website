import { devices, expect, test, type Page } from '@playwright/test';
import { addToCart, errors, hasHorizontalOverflow, login, pageAs, placeOrder, products, stockOf, users } from '../support/helpers';

/** H. Non-fungsional: UAT-56, UAT-57, UAT-58. */
test.describe('H. Non-fungsional', () => {
  test('UAT-57 Katalog dan detail produk termuat cepat (< 2 detik di lingkungan uji)', async ({ page }) => {
    for (const path of ['/catalog', `/products/${products.tote.slug}`]) {
      await page.goto(path);
      const domContentLoaded = await page.evaluate(() => {
        const [entry] = performance.getEntriesByType('navigation') as PerformanceNavigationTiming[];
        return entry.domContentLoadedEventEnd - entry.startTime;
      });
      test.info().annotations.push({ type: 'timing', description: `${path}: ${Math.round(domContentLoaded)} ms` });
      expect(domContentLoaded, path).toBeLessThan(2000);
    }
  });

  test('UAT-58 Dua buyer berebut stok terakhir: hanya satu pesanan yang berhasil', async ({ browser }) => {
    const first = await pageAs(browser, users.buyer.email);
    const second = await pageAs(browser, users.buyer2.email);

    await addToCart(first, products.lastUnit.slug, 1);
    await addToCart(second, products.lastUnit.slug, 1);

    await first.goto('/checkout?mode=cart');
    await placeOrder(first);

    await second.goto('/checkout?mode=cart');
    await second.getByLabel('Nomor WhatsApp pengambil').fill('081200009999');
    await second.locator('#accepted_terms').check();
    await second.getByRole('button', { name: /^Buat pesanan/ }).click();
    await expect(errors(second)).toContainText('Stok atau ketersediaan produk berubah. Periksa kembali pesananmu.');

    expect(await stockOf(second, products.lastUnit.slug)).toBe(0);
    await second.goto('/cart');
    await expect(second.getByText('Stok atau ketersediaan berubah. Perbarui jumlah sebelum checkout.')).toBeVisible();

    await Promise.all([first, second].map((page) => page.context().close()));
  });
});

const { defaultBrowserType: _ignored, ...pixel } = devices['Pixel 7'];

test.describe('UAT-56 Mobile-first tanpa overflow horizontal', () => {
  test.use(pixel);

  async function expectNoOverflow(page: Page, path: string): Promise<void> {
    await page.goto(path);
    expect(await hasHorizontalOverflow(page), `${path} overflows horizontally`).toBe(false);
  }

  test('pengunjung', async ({ page }) => {
    for (const path of ['/', '/projects', '/catalog', `/products/${products.tote.slug}`, '/help', '/terms']) {
      await expectNoOverflow(page, path);
    }
  });

  test('buyer: keranjang → checkout dapat diselesaikan dengan sentuhan', async ({ page }) => {
    await login(page, users.toggle.email);
    await addToCart(page, products.postcard.slug, 1);
    expect(await hasHorizontalOverflow(page)).toBe(false);
    await page.getByRole('link', { name: 'Lanjut ke checkout' }).tap();
    await page.getByLabel('Nomor WhatsApp pengambil').fill('081277778888');
    await page.locator('#accepted_terms').tap();
    expect(await hasHorizontalOverflow(page)).toBe(false);
    await page.getByRole('button', { name: /^Buat pesanan/ }).tap();
    await expect(page).toHaveURL(/\/orders\/FX-\d{8}$/);
    expect(await hasHorizontalOverflow(page)).toBe(false);
    await expectNoOverflow(page, '/orders');
  });

  test('seller dan admin', async ({ browser }) => {
    for (const [email, paths] of [
      [users.seller.email, ['/seller', '/seller/products', '/seller/orders']],
      [users.admin.email, ['/admin', '/admin/payments', '/admin/users', '/admin/pickup', '/admin/reports/daily']],
    ] as const) {
      const context = await browser.newContext(pixel);
      const page = await context.newPage();
      await login(page, email);
      for (const path of paths) {
        await expectNoOverflow(page, path);
      }
      await context.close();
    }
  });
});
