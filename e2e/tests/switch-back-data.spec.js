const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

// Regression: switching AWAY from a native module and back (in-app, no reload)
// must re-load its data. Reproduces the bespoke-view (NativeExtensionView) race
// where the view fetched before window.modJs was restored, showing empty state.

async function spaOpen(page, route) {
  await page.evaluate((r) => { window.location.hash = r; }, route);
  await page.waitForTimeout(3500);
}

test.describe('data reloads when switching modules', () => {
  test('directory (bespoke view) keeps data after switching away and back', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/app/ui/#extension::directory|user', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(3500);
    const content = page.locator('.ant-layout-content').first();
    const firstText = (await content.innerText()).trim();
    expect(firstText.length).toBeGreaterThan(40);

    await spaOpen(page, 'admin::salary');
    await spaOpen(page, 'extension::directory|user');

    const backText = (await content.innerText()).trim();
    // should render the same (non-trivial) content again, not an empty shell
    expect(backText.length, 'directory re-rendered with content').toBeGreaterThan(40);
  });
});
