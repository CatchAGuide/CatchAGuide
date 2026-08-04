const { test, expect } = require('@playwright/test');

/**
 * Minimal smoke: homepage loads. Expand only for critical user journeys.
 */
test.describe('smoke', () => {
  test('homepage responds', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.ok() || response?.status() === 304).toBeTruthy();
    await expect(page.locator('body')).toBeVisible();
  });
});
