import { defineConfig, devices } from '@playwright/test';

const port = Number(process.env.UAT_PORT ?? 8002);

/**
 * Watch mode (`npm run test:watch`) sets E2E_HEADED=1 and E2E_SLOWMO so the browser is visible
 * and every action is slowed down enough to follow along.
 */
const headed = process.env.E2E_HEADED === '1';
const slowMo = Number(process.env.E2E_SLOWMO ?? 0);

export default defineConfig({
  testDir: './tests',
  // The app runs on PHP's single-threaded built-in server and the specs share one database,
  // so files run one after another in name order.
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  // Slow motion multiplies the time each test needs.
  timeout: slowMo > 0 ? 180_000 : 45_000,
  expect: { timeout: slowMo > 0 ? 15_000 : 5_000 },
  use: {
    baseURL: `http://127.0.0.1:${port}`,
    headless: !headed,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: headed ? 'off' : 'retain-on-failure',
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
    launchOptions: {
      slowMo,
      // The layout loads Google Fonts; refuse those hosts so tests never wait on the internet.
      args: ['--host-resolver-rules=MAP fonts.googleapis.com 127.0.0.1:9, MAP fonts.gstatic.com 127.0.0.1:9'],
    },
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  webServer: {
    // Resets the e2e database + storage, seeds fixtures, then starts the app.
    command: `node support/serve.mjs ${port}`,
    url: `http://127.0.0.1:${port}/up`,
    reuseExistingServer: false,
    timeout: 120_000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
