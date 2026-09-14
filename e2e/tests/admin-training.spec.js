const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab, cardCount,
} = require('../helpers/app');

const TABS = ['Courses', 'Training Sessions', 'Employee Training Sessions'];

test.describe('admin::training (native)', () => {
  test('mounts natively with all tabs, no iframe', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::training');
    await expectNative(page, TABS);
  });

  test('each tab loads without error', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::training');
    for (const t of TABS) {
      await openTab(page, t);
      // either records or a clean empty-state, never an error
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });

  test('Courses tab exposes CRUD affordances', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::training');
    await openTab(page, 'Courses');
    // Admin can add courses
    await expect(page.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });
});
