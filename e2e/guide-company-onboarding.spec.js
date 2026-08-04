const { test, expect } = require('@playwright/test');

/**
 * Smoke: company guide verification UI + validation labels.
 * Guest/fast-lane path — does not create a user when company fields are missing.
 */
test.describe('guide company onboarding smoke', () => {
  test.beforeEach(async ({ page, context }) => {
    await context.addCookies([
      {
        name: 'default_cookie',
        value: 'true',
        domain: 'cag.local',
        path: '/',
      },
    ]);

    const response = await page.goto('/guide/onboarding', { waitUntil: 'domcontentloaded' });
    expect(response?.ok() || response?.status() === 304).toBeTruthy();
    await expect(page.locator('#guide-onboarding-form')).toBeVisible();

    const banner = page.locator('#cookie-consent-banner');
    if (await banner.isVisible().catch(() => false)) {
      await page.locator('#cookie-accept').click({ force: true });
      await banner.evaluate((el) => { el.style.display = 'none'; }).catch(() => {});
    }
  });

  function form(page) {
    return page.locator('#guide-onboarding-form');
  }

  async function goToTypeStep(page) {
    const accountStep = form(page).locator('.wizard-step[data-step-id="account"]');
    if (await accountStep.isVisible()) {
      await accountStep.locator('input[name="firstname"]').fill('Smoke');
      await accountStep.locator('input[name="lastname"]').fill('Tester');
      await accountStep.locator('input[name="email"]').fill(`smoke.company.${Date.now()}@example.com`);
      await accountStep.locator('input[name="password"]').fill('Password123!');
      await accountStep.locator('input[name="password_confirmation"]').fill('Password123!');
      await accountStep.locator('#terms').check({ force: true });
      await accountStep.locator('#privacy').check({ force: true });
      await accountStep.locator('.wizard-next').click();
    }
    await expect(form(page).locator('.wizard-step[data-step-id="type"]')).toBeVisible();
  }

  async function selectGuideType(page, type) {
    const radio = form(page).locator(
      `.wizard-step[data-step-id="type"] input[name="guide_type"][value="${type}"]`
    );
    // Card UI overlays the radio — click the wrapping label.
    await radio.locator('xpath=ancestor::label[contains(@class,"guide-type-card")]').click();
    await expect(radio).toBeChecked();
  }

  async function goToDetailsAsCompany(page) {
    await goToTypeStep(page);
    await selectGuideType(page, 'company');
    await form(page).locator('.wizard-step[data-step-id="type"] .wizard-next').click();
    const details = form(page).locator('.wizard-step[data-step-id="details"]');
    await expect(details).toBeVisible();
    await expect(details.locator('input[name="information[company_name]"]')).toBeVisible();
  }

  test('company fields show for company and hide for private', async ({ page }) => {
    await goToTypeStep(page);
    await expect(form(page).locator('input[name="guide_type"][value="company"]')).toBeAttached();

    await selectGuideType(page, 'company');
    await form(page).locator('.wizard-step[data-step-id="type"] .wizard-next').click();

    const details = form(page).locator('.wizard-step[data-step-id="details"]');
    await expect(details).toBeVisible();
    await expect(details.locator('input[name="information[company_name]"]')).toBeVisible();
    await expect(details.locator('select[name="information[legal_form]"]')).toBeVisible();
    await expect(details.locator('input[name="information[birthday]"]')).toBeHidden();
    await expect(details.locator('input[name="information[country]"]')).toHaveAttribute('maxlength', '3');
    await expect(details.getByText(/Ländercode|ISO country code/i)).toBeVisible();

    await details.locator('.wizard-prev').click();
    await selectGuideType(page, 'private');
    await form(page).locator('.wizard-step[data-step-id="type"] .wizard-next').click();

    await expect(details.locator('input[name="information[birthday]"]')).toBeVisible();
    await expect(details.locator('input[name="information[company_name]"]')).toBeHidden();
    await expect(details.locator('select[name="information[legal_form]"]')).toBeHidden();
  });

  test('blocks advancing without company name and legal form', async ({ page }) => {
    await goToDetailsAsCompany(page);

    const details = form(page).locator('.wizard-step[data-step-id="details"]');
    await details.locator('input[name="information[address]"]').fill('Von-Gahlen-Strasse');
    await details.locator('input[name="information[address_number]"]').fill('1');
    await details.locator('input[name="information[postal]"]').fill('40213');
    await details.locator('input[name="information[city]"]').fill('Düsseldorf');
    await details.locator('input[name="information[country]"]').fill('DE');
    await details.locator('input[name="information[phone]"]').fill('0123456789');

    await details.locator('.wizard-next').click();

    await expect(details).toBeVisible();
    await expect(form(page).locator('.wizard-step[data-step-id="legal"]')).toBeHidden();

    const companyName = details.locator('input[name="information[company_name]"]');
    expect(await companyName.evaluate((el) => el.checkValidity())).toBe(false);
  });

  test('server returns friendly German labels for missing company fields', async ({ page }) => {
    const token = await form(page).locator('input[name="_token"]').inputValue();

    const response = await page.request.post('/guide/onboarding', {
      form: {
        _token: token,
        is_fast_lane: '1',
        guide_type: 'company',
        firstname: 'Smoke',
        lastname: 'Company',
        email: `smoke.server.${Date.now()}@example.com`,
        password: 'Password123!',
        password_confirmation: 'Password123!',
        terms: '1',
        privacy: '1',
        'information[address]': 'Von-Gahlen-Strasse',
        'information[address_number]': '1',
        'information[postal]': '40213',
        'information[city]': 'Düsseldorf',
        'information[country]': 'Deutschland',
        'information[phone]': '0123456789',
        lawcard: '1',
        lawcard_nature: '1',
        lawcard_truthful: '1',
      },
      maxRedirects: 0,
      failOnStatusCode: false,
    });

    expect([302, 422, 200].includes(response.status())).toBeTruthy();

    let body = '';
    if (response.status() === 302) {
      const location = response.headers()['location'];
      const follow = await page.request.get(location || '/guide/onboarding');
      body = await follow.text();
    } else {
      body = await response.text();
    }

    expect(body).toMatch(/Firmenname muss ausgefüllt werden/i);
    expect(body).toMatch(/Rechtsform muss ausgefüllt werden/i);
    expect(body).not.toMatch(/information\.company name/i);
    expect(body).not.toMatch(/information\.legal form/i);
  });
});
