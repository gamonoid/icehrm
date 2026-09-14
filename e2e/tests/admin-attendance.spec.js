const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

const TABS = ['Attendance', 'Attendance Status'];

test.describe('admin::attendance (native)', () => {
  test('mounts natively with both tabs, no iframe', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::attendance');
    await expectNative(page, TABS);
  });

  test('each tab loads without error', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::attendance');
    for (const t of TABS) {
      await openTab(page, t);
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });

  test('clicking an attendance card shows exactly ONE detail modal', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::attendance');
    await page.locator('.ant-card').first().click();
    await page.waitForTimeout(1500);
    await expect(page.locator('.ant-modal-content')).toHaveCount(1);
    await expect(page.getByText('Attendance Details')).toBeVisible();
  });
});
