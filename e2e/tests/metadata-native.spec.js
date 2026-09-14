const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// admin::metadata converted to native: six master-data lookup tabs (countries,
// provinces, currency types, nationality, ethnicity, immigration status) via a
// shared initAdminMetadata, which the rewritten legacy page also uses (the
// old ModuleBuilder wiring emitted pre-React adapters).
test.describe('admin::metadata — native SPA', () => {
  test('mounts natively with all six lookup tabs', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::metadata');
    await expectNative(page, [
      'Countries', 'Provinces', 'Currency Types', 'Nationality', 'Ethnicity', 'Immigration Status',
    ]);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('currency types tab lists currencies', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::metadata');
    await openTab(page, 'Currency Types');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    // Find USD through the list search (it sits beyond page one).
    await active.getByPlaceholder(/Search/i).fill('United States');
    await active.getByPlaceholder(/Search/i).press('Enter');
    await expect(active.getByText('United States Dollar')).toBeVisible({ timeout: 15000 });
  });
});
