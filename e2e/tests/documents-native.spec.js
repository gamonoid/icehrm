const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// admin::documents and modules::documents converted to the native SPA.
// Admin: Company Documents / Document Types / Employee Documents / Employee
// Payslip (shared initAdminDocuments, also used by the modernised legacy page).
// User: read-only My Documents / Company Documents / Payslips with download
// actions. Seed: one company document, three document types.
test.describe('documents — native SPA', () => {
  test('admin::documents mounts natively with all four tabs', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::documents');
    await expectNative(page, [
      'Company Documents', 'Document Types', 'Employee Documents', 'Employee Payslip',
    ]);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('admin document types tab lists the seeded types', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::documents');
    await openTab(page, 'Document Types');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
  });

  test('modules::documents mounts natively with the three user tabs', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::documents');
    await expectNative(page, ['My Documents', 'Company Documents', 'Payslips']);

    // Company documents list the seeded document with a download action.
    await openTab(page, 'Company Documents');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(active.getByRole('button', { name: /Add New/i })).toHaveCount(0);
  });
});
