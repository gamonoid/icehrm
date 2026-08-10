const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// admin::loans and modules::loans converted to the native SPA card list.
// Admin: Loan Types (CRUD lookup) + Employee Loans (CRUD, status tag).
// User: read-only "Loans Taken" list; the eye opens the record details.
// Seed: 2 CompanyLoans; one EmployeeCompanyLoan for user1 (Personal loan, Approved).
test.describe('loans — native SPA', () => {
  test('admin::loans mounts natively with both tabs and loan type cards', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::loans');
    await expectNative(page, ['Loan Types', 'Employee Loans']);

    // Loan Types tab: seeded lookup rows + Add New for admins.
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Personal loan').first()).toBeVisible();
    await expect(active.getByText('Educational loan').first()).toBeVisible();
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('admin::loans Employee Loans tab lists the seeded loan with status tag', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::loans');
    await openTab(page, 'Employee Loans');

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Personal loan')).toBeVisible({ timeout: 15000 });
    await expect(active.locator('.ant-tag', { hasText: 'Approved' })).toBeVisible();
    // Filters come from the adapter's getFilters().
    await expect(active.getByRole('button', { name: /Filters/i })).toBeVisible();
  });

  test('admin can open the add form for a new employee loan', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::loans');
    await openTab(page, 'Employee Loans');

    await page.locator('.ant-tabs-tabpane-active').getByRole('button', { name: /Add New/i }).click();
    await page.waitForTimeout(1000);
    const modal = page.locator('.ant-modal:visible');
    await expect(modal.getByText('Loan Amount')).toBeVisible({ timeout: 15000 });
    await expect(modal.getByText('Monthly Installment')).toBeVisible();
    await page.keyboard.press('Escape');
  });

  test('modules::loans is a read-only native list for the employee', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::loans');
    await expectNative(page, ['Loans Taken']);

    await expect(page.getByText('Personal loan')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('.ant-tag', { hasText: 'Approved' })).toBeVisible();
    // Read-only: no Add New / edit / delete for employees.
    await expect(page.getByRole('button', { name: /Add New/i })).toHaveCount(0);
  });

  test('employee can view loan details via the eye button', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::loans');
    await page.waitForTimeout(2000);

    // The record card's view (eye) action opens the read-only details form.
    await page.locator('.anticon-eye').first().click();
    const modal = page.locator('.ant-modal:visible');
    await expect(modal.getByText('Monthly Installment')).toBeVisible({ timeout: 15000 });
    // Placeholder fields render as read-only inputs; remote-source ids resolve
    // to display names (loan type, currency).
    const values = await modal.locator('input').evaluateAll((els) => els.map((e) => e.value));
    expect(values).toContain('Personal loan');
    expect(values).toContain('United States Dollar');
    expect(values).toContain('Laptop purchase loan');
  });
});
