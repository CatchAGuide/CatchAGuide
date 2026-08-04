const { test, expect } = require('@playwright/test');

/**
 * Website smoke: key public surfaces load after asset/build changes.
 * waitUntil: 'domcontentloaded' — analytics/third-party assets often block full 'load'.
 */
test.describe('smoke', () => {
  test('homepage responds', async ({ page }) => {
    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response?.ok() || response?.status() === 304).toBeTruthy();
    await expect(page.locator('body')).toBeVisible();
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });

  test('compiled app assets are reachable', async ({ page, request }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const cssHref = await page.locator('link[rel="stylesheet"][href*="/css/app"]').first().getAttribute('href');
    const jsSrc = await page.locator('script[src*="/js/app"]').first().getAttribute('src');

    expect(cssHref).toBeTruthy();
    expect(jsSrc).toBeTruthy();

    const css = await request.get(cssHref);
    const js = await request.get(jsSrc);
    expect(css.ok()).toBeTruthy();
    expect(js.ok()).toBeTruthy();
  });

  const publicPages = [
    { name: 'guidings search', path: '/guidings' },
    { name: 'destinations', path: '/destination' },
    { name: 'vacations hub', path: '/vacations' },
    { name: 'trips destinations', path: '/trips-destinations' },
    { name: 'guide onboarding', path: '/guide/onboarding' },
    { name: 'login', path: '/login' },
    { name: 'password reset', path: '/password/reset' },
    { name: 'search request', path: '/searchrequest' },
    { name: 'contact', path: '/contact' },
    { name: 'about us', path: '/about-us' },
    { name: 'for agents', path: '/for-agents' },
    { name: 'fishing magazine', path: '/fishing-magazine' },
    // FAQ calls translate() per Q&A and can take ~45–60s locally.
    { name: 'faq', path: '/faq', timeout: 120_000 },
    { name: 'imprint', path: '/imprint' },
    { name: 'data protection', path: '/data-protection' },
    { name: 'terms (agb)', path: '/agb' },
    { name: 'notice and takedown', path: '/notice-and-takedown' },
  ];

  for (const pageDef of publicPages) {
    test(`${pageDef.name} page responds`, async ({ page }) => {
      const navTimeout = pageDef.timeout ?? 60_000;
      test.setTimeout(navTimeout + 15_000);

      const response = await page.goto(pageDef.path, {
        waitUntil: 'domcontentloaded',
        timeout: navTimeout,
      });
      expect(response?.ok() || response?.status() === 304).toBeTruthy();
      await expect(page.locator('body')).toBeVisible();
    });
  }
});
