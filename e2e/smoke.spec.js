const { test, expect } = require('@playwright/test');

/**
 * Minimal smoke: homepage returns HTML. Expand only for critical user journeys.
 * waitUntil: 'domcontentloaded' — homepage assets/analytics often prevent a full 'load'.
 */
test.describe('smoke', () => {
  test('homepage responds', async ({ page }) => {
    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response?.ok() || response?.status() === 304).toBeTruthy();
    await expect(page.locator('body')).toBeVisible();
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });
});
