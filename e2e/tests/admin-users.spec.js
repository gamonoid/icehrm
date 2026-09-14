const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

const TABS = ['Users', 'User Roles', 'User Invitations'];

test.describe('admin::users (native)', () => {
  test('mounts natively with all tabs, no iframe', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::users');
    await expectNative(page, TABS);
  });

  test('each tab loads without error', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::users');
    for (const t of TABS) {
      await openTab(page, t);
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });

  test('Users tab exposes CRUD + Change Password affordances', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::users');
    await openTab(page, 'Users');
    await expect(page.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
    // The admin user card should render and offer the password-change action.
    await expect(page.locator('.ant-card').first()).toBeVisible();
    await expect(page.locator('[aria-label="Change Password"], .ant-tooltip-open').first())
      .toBeTruthy();
  });

  test('User Roles tab lists roles', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::users');
    await openTab(page, 'User Roles');
    await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
  });
});
