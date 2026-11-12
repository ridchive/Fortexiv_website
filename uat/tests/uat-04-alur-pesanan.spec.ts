import { expect, test, type Page } from '@playwright/test';
import {
  addToCart,
  cartCount,
  cartItem,
  errors,
  flash,
  groupPanel,
  pageAs,
  pickupCodeFor,
  placeOrder,
  products,
  projects,
  row,
  rupiah,
  stockOf,
  uploadProof,
  users,
} from '../support/helpers';

/**
 * Perjalanan utama lintas peran (Handoff §16 baris pertama):
 * keranjang lintas kelas → checkout + ToS → bukti bayar → verifikasi admin → tiket → serah-terima per kelas → selesai.
 * Mencakup UAT-15–18, 21–23, 26, 28, 29, 39–43, 47–49, 59.
 */
test.describe.serial('C/D/F. Alur pesanan lintas kelas sampai Hari H', () => {
  let buyer: Page;
  let sellerA: Page;
  let sellerB: Page;
  let admin: Page;
  let orderCode: string;
  let codeA: string;
  let codeB: string;
  let proofUrl: string;
  let toteStock: number;

  const orderStatus = (page: Page) => page.locator('.section-head .badge').first();

  test.beforeAll(async ({ browser }) => {
    buyer = await pageAs(browser, users.buyer.email);
    sellerA = await pageAs(browser, users.seller.email);
    sellerB = await pageAs(browser, users.seller2.email);
    admin = await pageAs(browser, users.admin.email);
  });

  test.afterAll(async () => {
    await Promise.all([buyer, sellerA, sellerB, admin].map((page) => page.context().close()));
  });

  test('UAT-15 Keranjang lintas kelas dikelompokkan per kelas', async () => {
    toteStock = await stockOf(buyer, products.tote.slug);
    await addToCart(buyer, products.tote.slug, 1);
    await addToCart(buyer, products.bracelet.slug, 1);
    await addToCart(buyer, products.postcard.slug, 1);
    await addToCart(buyer, products.notebook.slug, 1);

    await expect(groupPanel(buyer, projects.a.className)).toContainText(products.tote.name);
    await expect(groupPanel(buyer, projects.a.className)).toContainText(products.bracelet.name);
    await expect(groupPanel(buyer, projects.b.className)).toContainText(products.postcard.name);

    await buyer.getByLabel(`Jumlah ${products.tote.name}`).fill('2');
    await cartItem(buyer, products.tote.name).getByRole('button', { name: 'Perbarui' }).click();
    await expect(cartItem(buyer, products.tote.name)).toContainText(rupiah(50000));

    await cartItem(buyer, products.notebook.name).getByRole('button', { name: 'Hapus' }).click();
    await expect(cartItem(buyer, products.notebook.name)).toHaveCount(0);
    await expect(cartCount(buyer)).toHaveText('3');
    await expect(buyer.locator('.kv').filter({ hasText: 'Total pesanan' })).toContainText(rupiah(70000));
  });

  test('UAT-16 Validasi stok di keranjang', async () => {
    await buyer.goto(`/products/${products.tote.slug}`);
    const quantity = buyer.getByLabel('Jumlah untuk keranjang');
    await quantity.evaluate((input: HTMLInputElement) => input.removeAttribute('max'));
    await quantity.fill(String(toteStock));
    await buyer.getByRole('button', { name: 'Tambah ke Keranjang' }).click();
    await expect(errors(buyer)).toContainText('Jumlah melebihi stok yang tersedia.');

    await buyer.goto('/cart');
    await buyer.getByLabel(`Jumlah ${products.tote.name}`).fill('60');
    const [response] = await Promise.all([
      buyer.waitForResponse((r) => r.request().method() === 'POST' && /\/cart\/\d+$/.test(r.url())),
      cartItem(buyer, products.tote.name).getByRole('button', { name: 'Perbarui' }).click(),
    ]);
    expect(response.status()).toBe(422);
    await buyer.goto('/cart');
    await expect(buyer.getByLabel(`Jumlah ${products.tote.name}`)).toHaveValue('2');
  });

  test('UAT-17 Checkout menampilkan ringkasan dan mewajibkan ToS', async () => {
    await buyer.goto('/cart');
    await buyer.getByRole('link', { name: 'Lanjut ke checkout' }).click();

    await expect(buyer.locator('.kv').filter({ hasText: 'Jumlah grup kelas' })).toContainText('2');
    await expect(buyer.locator('.kv').filter({ hasText: /^Total/ })).toContainText(rupiah(70000));
    await expect(buyer.getByText('Tenggat pembayaran:')).toBeVisible();
    await expect(buyer.getByText('Pengambilan:')).toBeVisible();
    await expect(buyer.getByText('Aula E2E, Jl. Pengujian No. 1, Bandung').first()).toBeVisible();
    await expect(buyer.getByText(/Terms of Service.*\(versi e2e-1\.0\)/)).toBeVisible();

    await buyer.getByLabel('Nomor WhatsApp pengambil').fill('081234567890');
    await buyer.locator('#accepted_terms').evaluate((input: HTMLInputElement) => input.removeAttribute('required'));
    await buyer.getByRole('button', { name: /^Buat pesanan/ }).click();
    await expect(errors(buyer)).toContainText(/accepted terms field is required|Setujui Terms of Service/i);
  });

  test('UAT-18 Satu checkout lintas kelas menjadi satu pesanan dengan grup per kelas', async () => {
    await buyer.goto('/checkout?mode=cart');
    orderCode = await placeOrder(buyer, '081211112222');

    await expect(buyer.getByRole('heading', { level: 1, name: orderCode })).toBeVisible();
    await expect(groupPanel(buyer, projects.a.className)).toBeVisible();
    await expect(groupPanel(buyer, projects.b.className)).toBeVisible();
    await expect(buyer.locator('.kv').filter({ hasText: 'Total pesanan' })).toContainText(rupiah(70000));
    await expect(buyer.getByText(/Terms of Service versi e2e-1\.0 disetujui pada/)).toBeVisible();
    await expect(cartCount(buyer)).toHaveCount(0);
    expect(await stockOf(buyer, products.tote.slug)).toBe(toteStock - 2);
  });

  test('UAT-21 Pesanan belum dibayar tidak memiliki kode/QR pickup', async () => {
    await buyer.goto(`/orders/${orderCode}`);
    await expect(orderStatus(buyer)).toHaveText('Menunggu pembayaran');
    await expect(buyer.getByRole('link', { name: 'Lihat tiket pickup per kelas' })).toHaveCount(0);
    expect((await buyer.goto(`/orders/${orderCode}/ticket`))?.status()).toBe(404);

    await uploadProof(buyer, orderCode, 'Transfer Mandiri');
    await expect(orderStatus(buyer)).toHaveText('Menunggu verifikasi');
    expect((await buyer.goto(`/orders/${orderCode}/ticket`))?.status()).toBe(404);

    await sellerA.goto('/seller/orders');
    await expect(row(sellerA, orderCode).first()).toContainText('Menunggu pembayaran terverifikasi');
  });

  test('UAT-22 Pesanan Saya menampilkan ringkasan pesanan milik sendiri', async () => {
    await buyer.goto('/orders');
    const orderRow = row(buyer, orderCode);
    await expect(orderRow).toContainText('Menunggu verifikasi');
    await expect(orderRow).toContainText(rupiah(70000));
    await expect(orderRow.getByRole('cell').nth(3)).toHaveText('2');
  });

  test('UAT-59 Data pesanan buyer lain tidak dapat diakses', async ({ browser }) => {
    const other = await pageAs(browser, users.buyer2.email);
    expect((await other.goto(`/orders/${orderCode}`))?.status()).toBe(404);
    expect((await other.goto(`/orders/${orderCode}/ticket`))?.status()).toBe(404);
    await other.goto('/orders');
    await expect(row(other, orderCode)).toHaveCount(0);
    await other.context().close();
  });

  test('UAT-48 Dashboard admin memantau pesanan dan pembayaran', async () => {
    await admin.goto('/admin');
    await expect(row(admin, orderCode).first()).toContainText(users.buyer.name);
    await expect(row(admin, orderCode).first()).toContainText('Menunggu verifikasi');
    await expect(row(admin, orderCode).first()).toContainText(projects.a.className);
    await expect(row(admin, orderCode).first()).toContainText(projects.b.className);
    expect(Number(await admin.locator('.stat').filter({ hasText: 'Payments to review' }).locator('strong').innerText())).toBeGreaterThanOrEqual(1);
  });

  test('UAT-28 Bukti pembayaran hanya dapat dibuka admin', async () => {
    await admin.goto('/admin/payments');
    proofUrl = (await row(admin, orderCode).getByRole('link', { name: 'Download receipt' }).getAttribute('href'))!;
    expect(proofUrl).toBeTruthy();

    expect((await sellerA.goto(proofUrl))?.status()).toBe(403);
    expect((await buyer.goto(proofUrl))?.status()).toBe(403);
  });

  test('UAT-26 Admin memverifikasi pembayaran dan kode pickup per grup terbit', async () => {
    await admin.goto('/admin/payments');
    const download = admin.waitForEvent('download');
    await row(admin, orderCode).getByRole('link', { name: 'Download receipt' }).click();
    expect((await download).suggestedFilename()).toMatch(/\.png$/);

    await row(admin, orderCode).getByRole('button', { name: 'Verify' }).click();
    await expect(flash(admin)).toHaveText('Pembayaran diverifikasi dan ticket pickup diterbitkan.');

    await buyer.goto(`/orders/${orderCode}`);
    await expect(orderStatus(buyer)).toHaveText('Siap diambil');
    await expect(buyer.getByRole('link', { name: 'Lihat tiket pickup per kelas' })).toBeVisible();
  });

  test('UAT-29 Pembayaran yang sudah diproses tidak dapat diproses ulang', async () => {
    await admin.goto('/admin/payments');
    await expect(row(admin, orderCode)).toContainText('Verified');
    await expect(row(admin, orderCode).getByRole('button', { name: 'Verify' })).toHaveCount(0);
    await expect(row(admin, orderCode).getByRole('button', { name: 'Reject' })).toHaveCount(0);
  });

  test('UAT-39 Tiket digital berisi ID pesanan, item per grup, tanggal, dan kode', async () => {
    await buyer.goto(`/orders/${orderCode}/ticket`);
    const ticket = buyer.locator('.ticket');
    await expect(ticket.getByRole('heading', { level: 1, name: orderCode })).toBeVisible();
    await expect(ticket).toContainText(`Pengambil: ${users.buyer.name}`);
    await expect(ticket).toContainText(/Tanggal: \d{2}\/\d{2}\/\d{4}/);
    await expect(groupPanel(buyer, projects.a.className)).toContainText(products.tote.name);
    await expect(groupPanel(buyer, projects.a.className)).toContainText(products.bracelet.name);
    await expect(groupPanel(buyer, projects.b.className)).toContainText(products.postcard.name);

    codeA = await pickupCodeFor(buyer, orderCode, projects.a.className);
    codeB = await pickupCodeFor(buyer, orderCode, projects.b.className);
    expect(codeA).not.toBe(codeB);
  });

  test('UAT-23 Perubahan harga produk tidak mengubah pesanan lama', async () => {
    await sellerA.goto('/seller/products');
    await row(sellerA, products.bracelet.name).getByRole('link', { name: 'Edit' }).click();
    await sellerA.getByLabel('Price (IDR, whole rupiah)').fill('20000');
    await sellerA.getByRole('button', { name: 'Save changes' }).click();
    await expect(flash(sellerA)).toHaveText('Produk diperbarui.');

    await buyer.goto(`/products/${products.bracelet.slug}`);
    await expect(buyer.locator('.detail-copy .price')).toHaveText(rupiah(20000));

    await buyer.goto(`/orders/${orderCode}`);
    await expect(groupPanel(buyer, projects.a.className).locator('.kv').filter({ hasText: products.bracelet.name })).toContainText(rupiah(12000));
    await expect(buyer.locator('.kv').filter({ hasText: 'Total pesanan' })).toContainText(rupiah(70000));
  });

  test('UAT-43 Kelas lain tidak bisa menyerahkan grup yang bukan miliknya', async () => {
    // Seller A submits the pickup form with the code of class B's group.
    await sellerA.goto('/seller/orders');
    const form = row(sellerA, orderCode).first().locator('form');
    await form.locator('input[name="pickup_code"]').evaluate((input: HTMLInputElement, code) => { input.value = code; }, codeB);
    await form.getByRole('button', { name: 'Konfirmasi serah-terima' }).click();
    await expect(errors(sellerA)).toContainText('Kode pickup tidak valid untuk kelas ini.');

    // Admin picks the wrong class for a code.
    await admin.goto('/admin/pickup');
    await admin.getByLabel('Kode pickup grup kelas').fill(codeB);
    await admin.getByLabel('Kelas yang menyerahkan').selectOption({ label: projects.a.className });
    await admin.getByRole('button', { name: 'Konfirmasi serah-terima kelas' }).click();
    await expect(errors(admin)).toContainText('Kode pickup tidak valid untuk kelas ini.');

    await buyer.goto(`/orders/${orderCode}`);
    await expect(groupPanel(buyer, projects.b.className)).toContainText('Menunggu pengambilan');
  });

  test('UAT-40 Grup A diserahkan lebih dulu; grup B tetap tertunda', async () => {
    await sellerA.goto('/seller/orders');
    await row(sellerA, orderCode).first().getByRole('button', { name: 'Konfirmasi serah-terima' }).click();
    await expect(flash(sellerA)).toHaveText(`Grup ${users.seller.store} pada pesanan ${orderCode} berhasil diserahkan.`);

    await buyer.goto(`/orders/${orderCode}`);
    await expect(orderStatus(buyer)).toHaveText('Sebagian diserahkan');
    await expect(groupPanel(buyer, projects.a.className)).toContainText('Sudah diserahkan');
    await expect(groupPanel(buyer, projects.b.className)).toContainText('Menunggu pengambilan');
  });

  test('UAT-47 Handover mencatat waktu (pelaku belum tampil di UI)', async () => {
    await buyer.goto(`/orders/${orderCode}`);
    await expect(groupPanel(buyer, projects.a.className)).toContainText(/Diserahkan \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}/);
    test.info().annotations.push({ type: 'gap', description: 'Petugas yang menyerahkan (handed_over_by) belum ditampilkan ke admin.' });
  });

  test('UAT-41 Grup terakhir diserahkan → pesanan selesai', async () => {
    await admin.goto('/admin/pickup');
    await admin.getByLabel('Kode pickup grup kelas').fill(codeB);
    await admin.getByLabel('Kelas yang menyerahkan').selectOption({ label: projects.b.className });
    await admin.getByRole('button', { name: 'Konfirmasi serah-terima kelas' }).click();
    await expect(flash(admin)).toHaveText('Grup kelas berhasil ditandai sudah diserahkan.');

    await buyer.goto(`/orders/${orderCode}`);
    await expect(orderStatus(buyer)).toHaveText('Selesai diambil');
    await sellerB.goto('/seller/orders');
    await expect(row(sellerB, orderCode)).toContainText('Selesai diambil');
    await admin.goto('/admin');
    await expect(row(admin, orderCode).first()).toContainText('Selesai');
  });

  test('UAT-42 Kode pickup yang sudah dipakai tidak bisa dipakai ulang', async () => {
    await admin.goto('/admin/pickup');
    await admin.getByLabel('Kode pickup grup kelas').fill(codeA);
    await admin.getByLabel('Kelas yang menyerahkan').selectOption({ label: projects.a.className });
    await admin.getByRole('button', { name: 'Konfirmasi serah-terima kelas' }).click();
    await expect(errors(admin)).toContainText(/Grup kelas ini sudah diserahkan|Pesanan belum dibayar atau tidak siap untuk diambil/);
  });

  test('UAT-49 Laporan harian mencatat transaksi hari ini', async () => {
    await admin.goto('/admin/reports/daily');
    const stat = (label: string) => admin.locator('.stat').filter({ hasText: label }).locator('strong');
    expect(Number(await stat('Total orders').innerText())).toBeGreaterThanOrEqual(1);
    expect(Number(await stat('Paid orders').innerText())).toBeGreaterThanOrEqual(1);
    expect(Number(await stat('Orders picked up').innerText())).toBeGreaterThanOrEqual(1);
    expect(Number(await stat('Products sold').innerText())).toBeGreaterThanOrEqual(4);
    await expect(stat('Revenue')).toContainText('Rp');
  });
});
