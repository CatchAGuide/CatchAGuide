const { test, expect } = require('@playwright/test');

/**
 * Mobile WebKit smoke: listing/product photos must stay inside their media box.
 * iOS Safari often ignores aspect-ratio + height:100% and paints the file at
 * intrinsic size, so the image spills into the next card and under the tab bar.
 *
 * Playwright WebKit is not identical to iPhone Safari, but a bounding-box
 * overflow here is the same layout failure.
 */
const SLACK_PX = 2;
const ASPECT_SLACK_PX = 24;
const CARDS_TO_CHECK = 6;

test.describe('listing images stay in their media box (iPhone WebKit)', () => {
  test.setTimeout(90_000);

  test.beforeEach(async ({ page, context, baseURL }) => {
    const hostname = new URL(baseURL || 'http://cag.local').hostname;
    await context.addCookies([
      {
        name: 'default_cookie',
        value: 'true',
        domain: hostname,
        path: '/',
      },
    ]);
    page.setDefaultNavigationTimeout(60_000);
  });

  test('offers catalog cards clip photos', async ({ page }) => {
    await openListingPage(page, '/offers');
    await assertImagesStayInMedia(page, {
      media: '.offers-card__media',
      img: '.offers-card__img',
      aspectHeightOverWidth: 10 / 16,
    });
    await assertImagesDoNotLeakBelowTabBar(page, {
      media: '.offers-card__media',
      img: '.offers-card__img',
    });
  });

  test('tours catalog cards clip photos', async ({ page }) => {
    await openListingPage(page, '/guidings/alloffers');
    await assertImagesStayInMedia(page, {
      media: '.offers-card__media',
      img: '.offers-card__img',
      aspectHeightOverWidth: 10 / 16,
    });
  });

  test('vacation trip cards clip photos', async ({ page }) => {
    await openListingPage(page, '/vacations/trips');
    await page.locator('.vacation-trip-list-card__gallery').first().scrollIntoViewIfNeeded();
    await assertImagesStayInMedia(page, {
      media: '.vacation-trip-list-card__gallery',
      img: '.vacation-trip-list-card__img',
      aspectHeightOverWidth: 1,
    });
  });

  test('first offer product gallery clips photos', async ({ page }) => {
    await openListingPage(page, '/offers');
    const cta = page.locator('.offers-card__cta').first();
    await expect(cta).toBeVisible({ timeout: 20_000 });
    await Promise.all([
      page.waitForURL((url) => !url.pathname.endsWith('/offers'), { timeout: 45_000 }),
      cta.click(),
    ]);
    await dismissCookieBanner(page);

    const productGallery = await firstVisibleGallery(page);
    expect(productGallery, 'product page should expose a gallery media box').toBeTruthy();
    await assertImagesStayInMedia(page, productGallery);
  });
});

async function openListingPage(page, path) {
  const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
  expect(response?.ok() || response?.status() === 304).toBeTruthy();
  await dismissCookieBanner(page);
}

async function dismissCookieBanner(page) {
  const banner = page.locator('#cookie-consent-banner');
  if (await banner.isVisible().catch(() => false)) {
    const accept = page.locator('#cookie-accept');
    if (await accept.isVisible().catch(() => false)) {
      await accept.click({ force: true });
    }
    await banner.evaluate((el) => {
      el.style.display = 'none';
    }).catch(() => {});
  }
}

async function firstVisibleGallery(page) {
  const candidates = [
    { media: '.guidings-gallery .left-image', img: 'img', aspectHeightOverWidth: 2 / 3 },
    { media: '.camp-gallery__main', img: 'img' },
    { media: '.gc-carousel', img: '.carousel-item.active img' },
    { media: '.guiding-card__gallery', img: 'img' },
    { media: '.offers-card__media', img: '.offers-card__img', aspectHeightOverWidth: 10 / 16 },
  ];

  for (const candidate of candidates) {
    const media = page.locator(candidate.media).first();
    if (await media.isVisible().catch(() => false)) {
      return candidate;
    }
  }

  return null;
}

async function assertImagesStayInMedia(page, { media, img, aspectHeightOverWidth }) {
  const medias = page.locator(media);
  await expect(medias.first(), `listing media (${media}) on ${page.url()}`).toBeVisible({
    timeout: 20_000,
  });

  const count = Math.min(await medias.count(), CARDS_TO_CHECK);
  expect(count).toBeGreaterThan(0);

  for (let i = 0; i < count; i++) {
    const mediaEl = medias.nth(i);
    await mediaEl.scrollIntoViewIfNeeded();
    const imgEl = mediaEl.locator(img).first();
    await expect(imgEl).toBeVisible({ timeout: 15_000 });
    await waitForImageDecode(imgEl);

    const mediaBox = await mediaEl.boundingBox();
    const imgBox = await imgEl.boundingBox();
    expect(mediaBox, `card ${i} media box`).toBeTruthy();
    expect(imgBox, `card ${i} image box`).toBeTruthy();

    expect(imgBox.width, `card ${i} image wider than media`).toBeLessThanOrEqual(mediaBox.width + SLACK_PX);
    expect(imgBox.height, `card ${i} image taller than media`).toBeLessThanOrEqual(mediaBox.height + SLACK_PX);
    expect(imgBox.x, `card ${i} image left of media`).toBeGreaterThanOrEqual(mediaBox.x - SLACK_PX);
    expect(imgBox.y, `card ${i} image above media`).toBeGreaterThanOrEqual(mediaBox.y - SLACK_PX);
    expect(imgBox.x + imgBox.width, `card ${i} image right of media`).toBeLessThanOrEqual(
      mediaBox.x + mediaBox.width + SLACK_PX
    );
    expect(imgBox.y + imgBox.height, `card ${i} image below media`).toBeLessThanOrEqual(
      mediaBox.y + mediaBox.height + SLACK_PX
    );

    if (aspectHeightOverWidth) {
      const expectedHeight = mediaBox.width * aspectHeightOverWidth;
      expect(
        mediaBox.height,
        `card ${i} media box taller than aspect-ratio (${mediaBox.height} vs ${expectedHeight})`
      ).toBeLessThanOrEqual(expectedHeight + ASPECT_SLACK_PX);
      expect(
        imgBox.height,
        `card ${i} image taller than aspect-ratio (${imgBox.height} vs ${expectedHeight})`
      ).toBeLessThanOrEqual(expectedHeight + ASPECT_SLACK_PX);
    }
  }
}

async function assertImagesDoNotLeakBelowTabBar(page, { media, img }) {
  const nav = page.locator('.cag-home-bottom-nav');
  if (!(await nav.isVisible().catch(() => false))) {
    return;
  }

  await page.evaluate(() => window.scrollBy(0, 800));
  await page.waitForTimeout(300);

  const navBox = await nav.boundingBox();
  expect(navBox).toBeTruthy();

  const medias = page.locator(media);
  const count = Math.min(await medias.count(), CARDS_TO_CHECK);

  for (let i = 0; i < count; i++) {
    const mediaEl = medias.nth(i);
    const imgEl = mediaEl.locator(img).first();
    if (!(await imgEl.isVisible().catch(() => false))) {
      continue;
    }

    const mediaBox = await mediaEl.boundingBox();
    const imgBox = await imgEl.boundingBox();
    if (!mediaBox || !imgBox) {
      continue;
    }

    const mediaBottom = mediaBox.y + mediaBox.height;
    const imageBottom = imgBox.y + imgBox.height;
    const navBottom = navBox.y + navBox.height;

    if (mediaBottom <= navBox.y + SLACK_PX) {
      expect(
        imageBottom,
        `card ${i} photo paints below the tab bar (image bottom ${imageBottom}, nav bottom ${navBottom})`
      ).toBeLessThanOrEqual(navBottom + SLACK_PX);
    }
  }
}

async function waitForImageDecode(imgEl) {
  await imgEl.evaluate((el) => {
    if (!(el instanceof HTMLImageElement)) {
      return Promise.resolve();
    }
    if (el.complete && el.naturalWidth > 0) {
      return Promise.resolve();
    }
    return el.decode().catch(() => {});
  });
}
