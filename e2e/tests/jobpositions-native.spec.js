const { test, expect } = require('@playwright/test');
const { login, openModule, expectNative } = require('../helpers/app');

// extension::jobpositions|admin converted to native via the meta.json native
// block: a single Job Positions tab (NativeCardList titled by job title), with
// the existing initAdminJob (steps modal + custom fields) carried over.
// Seed: one Job (JO123 / Software Eng).
test.describe('extension::jobpositions|admin — native SPA', () => {
  test('mounts natively and lists the seeded job position', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobpositions|admin');
    await expectNative(page, ['Job Positions']);

    await expect(page.getByText('Software Eng')).toBeVisible({ timeout: 15000 });
    await expect(page.getByText('JO123')).toBeVisible();
    await expect(page.getByRole('button', { name: /Add New/i })).toBeVisible();
  });

  test('opens the steps add form for a new job position', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobpositions|admin');
    await page.waitForTimeout(1500);

    await page.getByRole('button', { name: /Add New/i }).click();
    const modal = page.locator('.ant-modal:visible');
    await expect(modal.getByText('Job Title', { exact: true })).toBeVisible({ timeout: 15000 });
    await page.keyboard.press('Escape');
  });

  test('Copy Job Link puts the public apply URL on the clipboard', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobpositions|admin');
    await page.waitForTimeout(1500);

    // Mirrors the legacy "Job Link" action: apply/?ref=<job code>.
    await page.locator('.anticon-link').first().click();
    await expect(page.getByText(/Job link copied/i)).toBeVisible({ timeout: 5000 });
    const clip = await page.evaluate(() => navigator.clipboard.readText());
    expect(clip).toMatch(/\/app\/apply\/\?ref=JO123$/);
  });

  test('Open Job Page opens the public apply page in a new tab', async ({ page, context }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobpositions|admin');
    await page.waitForTimeout(1500);

    // Mirrors the legacy "View" action.
    const [popup] = await Promise.all([
      context.waitForEvent('page'),
      page.locator('.anticon-monitor').first().click(),
    ]);
    await popup.waitForLoadState('domcontentloaded');
    expect(popup.url()).toMatch(/\/app\/apply\/\?ref=JO123$/);
    await expect(popup).toHaveTitle(/Software Eng/, { timeout: 15000 });
  });
});
