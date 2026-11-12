import { expect, test } from '@playwright/test';
import { login, products, projects, rupiah, users } from '../support/helpers';

/** Skenario UAT-01 s.d. UAT-08 (lihat uat/skenario-uat.md, bagian A). */
test.describe('A. Pengunjung & konten publik', () => {
  test('UAT-01 Beranda menjelaskan acara, status PO, dan dua jalur', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1, name: 'Konsumsi Bijak, Bumi Lestari.' })).toBeVisible();
    await expect(page.locator('.band .chip')).toHaveText('PO aktif');
    await expect(page.locator('.band')).toContainText(/PO \d{2}\/\d{2}\/\d{4} – \d{2}\/\d{2}\/\d{4} · Pengambilan \d{2}\/\d{2}\/\d{4}/);
    await expect(page.getByRole('region', { name: 'Cerita proyek kelas' }).getByRole('heading', { name: projects.a.title })).toHaveCount(1);

    await page.getByRole('link', { name: 'Jelajahi Proyek', exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Proyek kelas' })).toBeVisible();
    await page.goto('/');
    await page.getByRole('link', { name: 'Lihat Katalog', exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Katalog produk' })).toBeVisible();
  });

  test('UAT-02 Daftar proyek dan filter kelas', async ({ page }) => {
    await page.goto('/projects');
    await expect(page.getByRole('heading', { name: projects.a.title })).toBeVisible();

    await page.getByRole('link', { name: projects.b.className, exact: true }).click();
    await expect(page.getByRole('heading', { name: projects.b.title })).toBeVisible();
    await expect(page.getByRole('heading', { name: projects.a.title })).toHaveCount(0);

    await page.getByRole('link', { name: 'Semua kelas' }).click();
    await expect(page.getByRole('heading', { name: projects.a.title })).toBeVisible();
  });

  test('UAT-03 Detail proyek terhubung ke produk kelasnya', async ({ page }) => {
    await page.goto('/projects');
    await page.getByRole('link', { name: new RegExp(projects.a.title) }).click();

    await expect(page.getByRole('heading', { level: 1, name: projects.a.title })).toBeVisible();
    for (const heading of ['Latar belakang', 'Tujuan', 'Proses', 'Hasil dan target']) {
      await expect(page.getByRole('heading', { name: heading })).toBeVisible();
    }
    const related = page.getByRole('region', { name: 'Produk dari proyek ini' });
    await expect(related.getByRole('heading', { name: products.tote.name })).toBeVisible();
    await expect(related.getByRole('heading', { name: products.postcard.name })).toHaveCount(0);
    await expect(related.getByRole('heading', { name: products.draftLamp.name })).toHaveCount(0);

    await page.getByRole('link', { name: 'Lihat produk kelas ini' }).click();
    await expect(page.locator('#class option:checked')).toHaveText(projects.a.className);
  });

  test('UAT-04 Filter katalog kelas + kategori bersamaan, hasil kosong, dan reset', async ({ page }) => {
    await page.goto('/catalog');
    await page.locator('#class').selectOption({ label: projects.a.className });
    await page.locator('#category').selectOption({ label: 'Aksesori' });
    await page.getByRole('button', { name: 'Terapkan filter' }).click();

    await expect(page.getByRole('heading', { name: products.tote.name })).toBeVisible();
    await expect(page.getByRole('heading', { name: products.notebook.name })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: products.postcard.name })).toHaveCount(0);

    await page.locator('#search').fill('tidak-ada-produk-ini');
    await page.getByRole('button', { name: 'Terapkan filter' }).click();
    await expect(page.getByRole('heading', { name: 'Tidak ada produk yang cocok' })).toBeVisible();

    await page.getByRole('link', { name: 'Atur ulang filter' }).click();
    await expect(page).toHaveURL(/\/catalog$/);
    await expect(page.getByRole('heading', { name: products.postcard.name })).toBeVisible();
  });

  test('UAT-05 Detail produk lengkap dengan dua CTA', async ({ page }) => {
    await page.goto(`/products/${products.tote.slug}`);
    await expect(page.locator('.detail-copy .price')).toHaveText(rupiah(products.tote.price));
    await expect(page.locator('.detail-copy .badge')).toHaveText(/^Stok \d+$/);
    await expect(page.getByText('CERITA PRODUK', { exact: true })).toBeVisible();
    await expect(page.getByText('PROYEK KELAS TERKAIT')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'PO dan pengambilan' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Masuk untuk memesan' })).toBeVisible();

    await login(page, users.buyer.email);
    await page.goto(`/products/${products.tote.slug}`);
    await expect(page.getByRole('button', { name: 'Tambah ke Keranjang' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Beli Sekarang' })).toBeVisible();
    await expect(page.getByText('Checkout hanya untuk produk ini. Isi keranjang tidak ikut dibayar.')).toBeVisible();
  });

  test('UAT-06 Produk stok habis terlihat tetapi tidak bisa dibeli', async ({ page }) => {
    await login(page, users.buyer.email);
    await page.goto(`/catalog?stock=sold_out&search=${encodeURIComponent(products.soldOut.name)}`);
    const card = page.locator('.pcard').filter({ hasText: products.soldOut.name });
    await expect(card).toContainText('Stok habis');
    await expect(card.getByRole('button', { name: '+ Keranjang' })).toHaveCount(0);

    await page.goto(`/products/${products.soldOut.slug}`);
    await expect(page.getByText('Stok produk ini habis.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Tambah ke Keranjang' })).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Beli Sekarang' })).toHaveCount(0);
  });

  test('UAT-07 Produk draft tidak tampil ke publik', async ({ page }) => {
    await page.goto(`/catalog?search=${encodeURIComponent(products.draftLamp.name)}`);
    await expect(page.getByRole('heading', { name: 'Tidak ada produk yang cocok' })).toBeVisible();
    expect((await page.goto(`/products/${products.draftLamp.slug}`))?.status()).toBe(404);
  });

  test('UAT-08 Bantuan, cara pickup, dan ToS dapat diakses dari footer', async ({ page }) => {
    await page.goto('/');
    const footer = page.locator('footer');

    await footer.getByRole('link', { name: 'Cara memesan' }).click();
    await expect(page.getByRole('heading', { level: 2, name: 'Cara memesan' })).toBeVisible();

    await page.locator('footer').getByRole('link', { name: 'Pengambilan barang' }).click();
    await expect(page.getByRole('heading', { level: 2, name: 'Pengambilan barang' })).toBeVisible();
    await expect(page.getByText('Instruksi pickup E2E: tunjukkan kode pickup ke meja kelas.')).toBeVisible();

    await page.locator('footer').getByRole('link', { name: 'Terms of Service' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Terms of Service' })).toBeVisible();
    await expect(page.getByText('VERSI e2e-1.0')).toBeVisible();
  });

  test.fixme('UAT-09 PO belum buka / sudah tutup menonaktifkan transaksi (manual: ubah MARKETPLACE_PO_* lalu restart)', async () => {});
});
