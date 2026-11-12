import { expect, test } from '@playwright/test';
import { flash, login, pageAs, products, projects, receiptPng, row, rupiah, unique, users } from '../support/helpers';

/** Skenario UAT-30 s.d. UAT-34 (lihat uat/skenario-uat.md, bagian E). Gap UAT-35–38 ada di uat-06-gap. */
test.describe('E. Seller / kelas', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, users.seller.email);
  });

  test('UAT-30 Seller menambah produk (draft) lalu menerbitkan', async ({ page }) => {
    const name = `Produk UAT ${unique()}`;

    await page.goto('/seller/products/create');
    await page.getByLabel('Product name').fill(name);
    await page.getByLabel('Description').fill('Produk uji penerimaan dari kelas A.');
    await page.getByLabel('Price (IDR, whole rupiah)').fill('21000');
    await page.getByLabel('Stock').fill('7');
    await page.getByLabel('Category').selectOption({ label: 'Dekorasi' });
    await page.getByLabel(/Product image/).setInputFiles(receiptPng);
    await page.getByRole('button', { name: 'Save draft' }).click();
    await expect(flash(page)).toHaveText('Produk tersimpan sebagai draft.');
    await expect(row(page, name)).toContainText('Draft');

    await page.goto(`/catalog?search=${encodeURIComponent(name)}`);
    await expect(page.getByRole('heading', { name })).toHaveCount(0);

    await page.goto('/seller/products');
    await row(page, name).getByRole('button', { name: 'Publish' }).click();
    await expect(row(page, name)).toContainText('Published');

    await page.goto(`/catalog?search=${encodeURIComponent(name)}`);
    await expect(page.locator('.pcard').filter({ hasText: name }).locator('.price')).toHaveText(rupiah(21000));
    await expect(page.locator('.pcard').filter({ hasText: name })).toContainText(projects.a.className);
  });

  test('UAT-31 Seller mengubah harga dan stok', async ({ page }) => {
    await page.goto('/seller/products');
    await row(page, products.draftLamp.name).getByRole('link', { name: 'Edit' }).click();
    await page.getByLabel('Price (IDR, whole rupiah)').fill('52000');
    await page.getByLabel('Stock').fill('9');
    await page.getByRole('button', { name: 'Save changes' }).click();

    await expect(flash(page)).toHaveText('Produk diperbarui.');
    await expect(row(page, products.draftLamp.name)).toContainText(rupiah(52000));
    await expect(row(page, products.draftLamp.name)).toContainText('9');
  });

  test('UAT-32 Produk stok 0 tidak dapat diterbitkan', async ({ page }) => {
    await page.goto('/seller/products');
    const [response] = await Promise.all([
      page.waitForResponse((r) => r.request().method() === 'POST' && r.url().includes('/publish')),
      row(page, products.emptyPoster.name).getByRole('button', { name: 'Publish' }).click(),
    ]);
    expect(response.status()).toBe(422);
    await page.goto('/seller/products');
    await expect(row(page, products.emptyPoster.name)).toContainText('Draft');
  });

  test('UAT-33 Seller hanya melihat data kelasnya', async ({ page, browser }) => {
    await page.goto('/seller/products');
    await expect(row(page, products.tote.name)).toBeVisible();
    await expect(row(page, products.postcard.name)).toHaveCount(0);

    const sellerB = await pageAs(browser, users.seller2.email);
    await sellerB.goto('/seller/products');
    const editUrl = await row(sellerB, products.postcard.name).getByRole('link', { name: 'Edit' }).getAttribute('href');
    await sellerB.context().close();

    expect((await page.goto(editUrl!))?.status()).toBe(404);
  });

  test('UAT-34 Seller tidak dapat mengelola artikel proyek atau cerita produk', async ({ page }) => {
    await page.goto(`/seller/products`);
    await row(page, products.tote.name).getByRole('link', { name: 'Edit' }).click();
    await expect(page.locator('[name="story"]')).toHaveCount(0);
    await expect(page.getByLabel(/cerita|story/i)).toHaveCount(0);

    await page.goto('/seller');
    await expect(page.locator('main').getByRole('link', { name: /artikel|article/i })).toHaveCount(0);
  });
});
