const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// extension::jobsetup|admin (Recruitment Setup) converted to native via the
// meta.json native block: five id-name lookup tabs (employment types,
// experience levels, job functions, education levels, benefits), each a
// NativeCardList with inline card config. Seeded master data on all tabs.
test.describe('extension::jobsetup|admin — native SPA', () => {
  test('mounts natively with all five lookup tabs', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobsetup|admin');
    await expectNative(page, [
      'Employment Types', 'Experience Levels', 'Job Functions', 'Education Levels', 'Benefits',
    ]);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Full-time')).toBeVisible({ timeout: 15000 });
    await expect(active.getByText('Part-time')).toBeVisible();
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('Benefits tab lists seeded benefits and opens the add form', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::jobsetup|admin');
    await openTab(page, 'Benefits');

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Retirement plan')).toBeVisible({ timeout: 15000 });
    await expect(active.getByText('Health plan')).toBeVisible();

    await active.getByRole('button', { name: /Add New/i }).click();
    const modal = page.locator('.ant-modal:visible');
    await expect(modal.getByText('Name', { exact: true })).toBeVisible({ timeout: 15000 });
    await page.keyboard.press('Escape');
  });
});
