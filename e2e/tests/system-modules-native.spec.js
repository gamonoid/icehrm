const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// The four system admin modules converted to the native SPA:
// - admin::settings — dynamic category tabs (Company/System/…/Other) of
//   edit-only Setting cards, tabs mirroring the legacy visibility rules;
// - admin::modules — the module list with status tags;
// - admin::connection — the System Status report via NativeExtensionView.
test.describe('system admin modules — native SPA', () => {
  test('admin::settings mounts natively with the proper settings controls', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'admin::settings');
    await expectNative(page, ['Company', 'System']);

    // The bespoke SettingsPage renders per tab: header with Save Changes,
    // a settings search, and meta-driven inline controls.
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByRole('button', { name: /Save Changes/i })).toBeVisible({ timeout: 20000 });
    await expect(active.getByPlaceholder(/Search settings/i)).toBeVisible();

    await openTab(page, 'System');
    const sys = page.locator('.ant-tabs-tabpane-active');
    await expect(sys.getByRole('button', { name: /Save Changes/i })).toBeVisible({ timeout: 20000 });
    // Yes/No switches and multi-selects render for the matching settings.
    await expect(sys.locator('.ant-switch').first()).toBeVisible();
    await expect(sys.getByText('System: Allowed Currencies')).toBeVisible();
  });

  test('admin::modules mounts natively with the module list', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::modules');
    await page.waitForTimeout(2500);
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    await expect(page.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(page.locator('.ant-tag', { hasText: 'Enabled' }).first()).toBeVisible();
  });

  // admin::permissions was removed from the product: the meta.json permission blocks,
  // the Permission model and the admin/permissions module were all deleted, and
  // migration v20260804_100000_remove_add_new_permissions_setting drops the Modules
  // row and the Permissions table on existing installs. The spec that used to cover
  // it is gone with the feature — a fresh database never registers the module, so the
  // test could only ever pass on a database old enough to still carry the row.

  test('admin::connection mounts natively with the system report', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::connection');
    await page.waitForTimeout(3000);
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    // The ConnectionTab report renders (employee count / system data blocks).
    await expect(page.locator('.ant-tabs-tabpane-active .ant-card, .ant-tabs-tabpane-active .ant-descriptions, .ant-tabs-tabpane-active table').first()).toBeVisible({ timeout: 20000 });
  });
});
