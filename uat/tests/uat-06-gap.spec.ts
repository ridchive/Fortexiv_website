import { expect, test } from '@playwright/test';
import { login, row, users } from '../support/helpers';

/**
 * G. Admin (UAT-49, UAT-50) dan daftar gap requirement yang belum dibangun.
 * Test `fixme` sengaja dilewati: judulnya memuat kriteria penerimaan dari dokumen requirement
 * agar langsung bisa diimplementasikan saat fiturnya tersedia (lihat design/rencana-fitur-admin-seller-buyer.md).
 */
test.describe('G. Admin: pengelolaan', () => {
  test('UAT-50 Admin mengelola akun buyer/seller tanpa menghapus riwayat', async ({ page }) => {
    await login(page, users.admin.email);
    await page.goto('/admin/users');
    await expect(row(page, users.buyer.email)).toContainText('Buyer');
    await expect(row(page, users.seller.email)).toContainText(users.seller.store);
    await expect(row(page, users.admin.email)).toHaveCount(0);
    await expect(page.getByText('Disable accounts without deleting their purchase and pickup history.')).toBeVisible();
  });

  test('UAT-49 Laporan harian dapat difilter per tanggal', async ({ page }) => {
    await login(page, users.admin.email);
    await page.goto('/admin/reports/daily');
    await page.getByLabel('Date').fill('2020-01-01');
    await page.getByRole('button', { name: 'View' }).click();
    await expect(page.getByText('Report for 01 Jan 2020.')).toBeVisible();
    await expect(page.locator('.stat').filter({ hasText: 'Total orders' }).locator('strong')).toHaveText('0');
  });
});

test.describe('Gap requirement (belum dibangun)', () => {
  test.fixme('UAT-24 Pesanan belum dibayar menjadi "Kedaluwarsa" setelah batas bayar dan stok kembali (Handoff §6.8, §16)', async () => {});
  test.fixme('UAT-25 Buyer mengajukan masalah dari detail pesanan dan melihat status resolusi (Handoff §5.1, §6.10)', async () => {});
  test.fixme('UAT-35 Produk seller berstatus "Menunggu review"; admin menyetujui atau meminta revisi dengan alasan sebelum tayang (Handoff §8.4)', async () => {});
  test.fixme('UAT-36 Seller melihat rekap produksi per produk hanya dari pesanan dibayar (Handoff §7.4)', async () => {});
  test.fixme('UAT-37 Seller menyatakan kesiapan Hari H dan melaporkan insiden kelas (Handoff §7.5, §12.3)', async () => {});
  test.fixme('UAT-38 Payout Kelas A dapat diproses sementara Kelas B ditahan (Handoff §7.7, §8.8, §16)', async () => {});
  test.fixme('UAT-44 Petugas mencari pesanan berdasarkan nama buyer/kode pesanan sebagai fallback dan penggunaannya tercatat (Handoff §8.7, §12.3)', async () => {});
  test.fixme('UAT-46 Admin mengoreksi handover dan menetapkan resolusi/refund satu grup tanpa mengubah grup lain (Handoff §8.5, §14.10–14.11)', async () => {});
  test.fixme('UAT-51 Admin membuat, mengedit, mengaitkan, dan menerbitkan artikel proyek serta cerita produk (Handoff §8.3, §16)', async () => {});
  test.fixme('UAT-52 Admin mengatur jendela PO, batas bayar, Hari H, dan lokasi dari panel; website memakai nilai tersebut (Handoff §8.2)', async () => {});
  test.fixme('UAT-53 Admin melihat dan mengelola produk seluruh kelas (BRD §6 "Manage products")', async () => {});
  test.fixme('UAT-54 Setiap tindakan manual (verifikasi, tolak, handover, resolusi) mencatat pelaku, waktu, alasan, objek (Handoff §8.9, §14.14)', async () => {});
  test.fixme('UAT-55 Admin mengedit FAQ, ToS (berversi), dan pengumuman (Handoff §5.3, §8.9)', async () => {});
  test.fixme('UAT-45 QR pickup dapat dipindai kamera ponsel petugas (manual, Handoff §6.10, §12.2)', async () => {});
});
