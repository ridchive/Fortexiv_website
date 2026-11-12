import { expect, test } from '@playwright/test';
import { PASSWORD, accountMenu, emailLink, errors, flash, login, logout, pageAs, row, unique, users } from '../support/helpers';

/** Skenario UAT-10 s.d. UAT-14 (lihat uat/skenario-uat.md, bagian B). */
test.describe('B. Akun & peran', () => {
  test('UAT-10 Daftar akun buyer dan verifikasi email', async ({ page }) => {
    const email = `uat-${unique()}@e2e.test`;

    await page.goto('/register');
    await page.getByLabel('Full name').fill('Pembeli UAT');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password (at least 8 characters)').fill(PASSWORD);
    await page.getByLabel('Confirm password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Create account' }).click();
    await expect(page).toHaveURL(/\/email\/verify$/);

    await page.goto('/cart');
    await expect(page).toHaveURL(/\/email\/verify$/);

    await page.goto(await emailLink(email, '/email/verify/'));
    await expect(flash(page)).toHaveText('Your email address has been verified.');
    await page.goto('/account');
    await expect(page.getByText(`Email: ${email}`)).toBeVisible();
    await expect(page.getByText('Account type: Buyer')).toBeVisible();

    await logout(page);
    await page.goto('/register');
    await page.getByLabel('Full name').fill('Duplikat');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password (at least 8 characters)').fill(PASSWORD);
    await page.getByLabel('Confirm password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Create account' }).click();
    await expect(errors(page)).toContainText('email has already been taken');
  });

  test('UAT-11 Login, logout, dan arah halaman sesuai peran', async ({ page }) => {
    for (const [email, url] of [
      [users.admin.email, /\/admin$/],
      [users.seller.email, /\/seller$/],
      [users.buyer.email, /\/$/],
    ] as const) {
      await login(page, email);
      await expect(page).toHaveURL(url);
      await logout(page);
    }

    await page.goto('/orders');
    await expect(page).toHaveURL(/\/login$/);

    await page.getByLabel('Email').fill(users.buyer.email);
    await page.getByLabel('Password', { exact: true }).fill('salah-password');
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(errors(page)).toContainText('Email atau password tidak cocok.');
    await expect(accountMenu(page)).toHaveCount(0);
  });

  test('UAT-12 Akses halaman dibatasi sesuai peran', async ({ browser }) => {
    const cases = [
      [users.buyer.email, ['/admin', '/admin/payments', '/seller', '/seller/products']],
      [users.seller.email, ['/cart', '/orders', '/admin']],
      [users.admin.email, ['/seller', '/cart']],
    ] as const;

    for (const [email, paths] of cases) {
      const page = await pageAs(browser, email);
      for (const path of paths) {
        expect((await page.goto(path))?.status(), `${email} → ${path}`).toBe(403);
      }
      await page.context().close();
    }
  });

  test('UAT-13 Lupa password', async ({ page }) => {
    const newPassword = 'PasswordUat789!';

    await page.goto('/forgot-password');
    await page.getByLabel('Email').fill(users.reset.email);
    await page.getByRole('button', { name: 'Send reset link' }).click();

    await page.goto(await emailLink(users.reset.email, '/reset-password/'));
    await page.getByLabel('New password (at least 8 characters)').fill(newPassword);
    await page.getByLabel('Confirm new password').fill(newPassword);
    await page.getByRole('button', { name: 'Reset password' }).click();
    await expect(flash(page)).toHaveText('Your password has been reset. You can now sign in.');

    await page.getByLabel('Email').fill(users.reset.email);
    await page.getByLabel('Password', { exact: true }).fill(PASSWORD);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(errors(page)).toContainText('Email atau password tidak cocok.');

    await login(page, users.reset.email, newPassword);
  });

  test('UAT-14 Akun nonaktif tidak bisa masuk', async ({ page, browser }) => {
    await login(page, users.admin.email);
    await page.goto('/admin/users');
    await row(page, users.toggle.email).getByRole('button', { name: 'Disable' }).click();
    await expect(row(page, users.toggle.email)).toContainText('Disabled');

    const other = await (await browser.newContext()).newPage();
    await other.goto('/login');
    await other.getByLabel('Email').fill(users.toggle.email);
    await other.getByLabel('Password', { exact: true }).fill(PASSWORD);
    await other.getByRole('button', { name: 'Sign in' }).click();
    await expect(errors(other)).toContainText('Akun ini telah dinonaktifkan.');

    await row(page, users.toggle.email).getByRole('button', { name: 'Enable' }).click();
    await login(other, users.toggle.email);
    await other.context().close();
  });
});
