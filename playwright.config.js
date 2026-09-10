// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Lean E2E config.
 * Chromium: general smoke. iPhone WebKit: listing-image clip on Safari-like mobile.
 * Set PLAYWRIGHT_BASE_URL if the app is not on the default Laragon host.
 */
module.exports = defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'list',
  timeout: 60_000,
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://cag.local',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    navigationTimeout: 45_000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      testIgnore: /listing-images-mobile/,
    },
    {
      name: 'iphone-webkit',
      use: { ...devices['iPhone 13'] },
      testMatch: /listing-images-mobile/,
    },
  ],
});
