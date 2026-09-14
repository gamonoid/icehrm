const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

const TABS = ['Overtime Categories', 'Overtime Requests'];

test.describe('admin::overtime (native, approve workflow)', () => {
  test('mounts natively with both tabs', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::overtime');
    await expectNative(page, TABS);
  });

  test('each tab loads without error', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::overtime');
    for (const t of TABS) {
      await openTab(page, t);
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });

  test('a Pending request exposes Change Status + View Logs and approval works', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::overtime');
    await openTab(page, 'Overtime Requests');
    const pane = page.locator('.ant-tabs-tabpane-active');
    const card = pane.locator('.ant-card').first();
    if (!(await card.count())) test.skip(true, 'no overtime requests seeded');

    // Change Status (only present for actionable statuses)
    const changeBtn = pane.locator('.anticon-monitor').first();
    await expect(changeBtn).toBeVisible();
    await changeBtn.click();
    await expect(page.getByText('Change Status')).toBeVisible();
    // pick Approved + update
    await page.locator('.ant-modal .ant-select').click();
    await page.getByText('Approved', { exact: true }).last().click().catch(() => {});
    await page.getByRole('button', { name: /Update/i }).click();
    await expect(page.getByText(/Status updated/i)).toBeVisible({ timeout: 10000 });

    // View Logs shows the transition
    await openTab(page, 'Overtime Requests');
    await pane.locator('.anticon-history').first().click();
    await expect(page.getByText('Approval Log')).toBeVisible();
    await expect(page.getByText(/Approved/).first()).toBeVisible();
  });
});
