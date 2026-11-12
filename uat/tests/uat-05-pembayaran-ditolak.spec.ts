import { expect, test, type Page } from '@playwright/test';
import {
  addToCart,
  cartCount,
  cartItem,
  errors,
  flash,
  groupPanel,
  pageAs,
  placeOrder,
  products,
  projects,
  receiptPng,
  row,
  stockOf,
  users,
} from '../support/helpers';

/** Beli Sekarang + unggah bukti + penolakan admin. Mencakup UAT-19, UAT-20, UAT-27, UAT-29. */
test.describe.serial('C/D. Beli Sekarang dan pembayaran ditolak', () => {
  let buyer: Page;
  let admin: Page;
  let orderCode: string;
  let initialStock: number;

  const orderStatus = (page: Page) => page.locator('.section-head .badge').first();

  test.beforeAll(async ({ browser }) => {
    buyer = await pageAs(browser, users.buyer2.email);
    admin = await pageAs(browser, users.admin.email);
  });

  test.afterAll(async () => {
    await Promise.all([buyer, admin].map((page) => page.context().close()));
  });

  test('UAT-19 Beli Sekarang hanya memproses satu produk dan keranjang tetap utuh', async () => {
    await addToCart(buyer, products.postcard.slug, 1);
    initialStock = await stockOf(buyer, products.notebook.slug);

    await buyer.getByLabel('Jumlah Beli Sekarang').fill('2');
    await buyer.getByRole('button', { name: 'Beli Sekarang' }).click();
    await expect(groupPanel(buyer, projects.a.className)).toContainText(`${products.notebook.name} × 2`);
    await expect(groupPanel(buyer, projects.b.className)).toHaveCount(0);

    orderCode = await placeOrder(buyer);
    await expect(cartCount(buyer)).toHaveText('1');
    expect(await stockOf(buyer, products.notebook.slug)).toBe(initialStock - 2);
  });

  test('UAT-20 Unggah bukti transfer: jenis file dibatasi, status berubah', async () => {
    await buyer.goto(`/orders/${orderCode}`);
    await buyer.getByLabel('Metode / bank transfer').fill('Transfer BRI');
    await buyer.getByLabel('Bukti transfer').setInputFiles({ name: 'bukti.txt', mimeType: 'text/plain', buffer: Buffer.from('bukan gambar') });
    await buyer.getByRole('button', { name: 'Kirim bukti transfer' }).click();
    await expect(errors(buyer)).toContainText('proof');
    await expect(orderStatus(buyer)).toHaveText('Menunggu pembayaran');

    await buyer.getByLabel('Metode / bank transfer').fill('Transfer BRI');
    await buyer.getByLabel('Bukti transfer').setInputFiles(receiptPng);
    await buyer.getByRole('button', { name: 'Kirim bukti transfer' }).click();
    await expect(flash(buyer)).toHaveText('Bukti transfer berhasil dikirim dan menunggu verifikasi admin.');
    await expect(orderStatus(buyer)).toHaveText('Menunggu verifikasi');
    await expect(buyer.getByRole('button', { name: 'Kirim bukti transfer' })).toHaveCount(0);
  });

  test('UAT-27 Admin menolak pembayaran dengan alasan, stok kembali', async () => {
    await admin.goto('/admin/payments');
    admin.once('dialog', (dialog) => dialog.accept());
    await row(admin, orderCode).getByPlaceholder('Reason').fill('Bukti transfer tidak terbaca');
    await row(admin, orderCode).getByRole('button', { name: 'Reject' }).click();
    await expect(flash(admin)).toHaveText('Pembayaran ditolak dan stok dikembalikan.');

    await buyer.goto(`/orders/${orderCode}`);
    await expect(orderStatus(buyer)).toHaveText('Dibatalkan');
    await expect(buyer.getByText('Catatan admin: Bukti transfer tidak terbaca')).toBeVisible();
    await expect(buyer.getByRole('link', { name: 'Lihat tiket pickup per kelas' })).toHaveCount(0);
    expect(await stockOf(buyer, products.notebook.slug)).toBe(initialStock);
  });

  test('UAT-29 Pembayaran yang ditolak tidak dapat diproses ulang', async () => {
    await admin.goto('/admin/payments');
    await expect(row(admin, orderCode)).toContainText('Rejected');
    await expect(row(admin, orderCode).getByRole('button', { name: 'Verify' })).toHaveCount(0);

    // Leave buyer2's cart empty for later specs.
    await buyer.goto('/cart');
    await cartItem(buyer, products.postcard.name).getByRole('button', { name: 'Hapus' }).click();
    await expect(cartCount(buyer)).toHaveCount(0);
  });
});
