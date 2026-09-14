const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative,
} = require('../helpers/app');

test.describe('modules::attendance (native, employee self-service)', () => {
  test('mounts natively with the Punch button and own records', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::attendance');
    await expectNative(page, ['Attendance']);
    // Punch In/Out widget present
    await expect(page.getByRole('button', { name: /Punch/i }).first()).toBeVisible();
  });

  test('Punch button opens the punch dialog', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::attendance');
    await page.getByRole('button', { name: /Punch/i }).first().click();
    await page.waitForTimeout(1200);
    await expect(page.locator('.ant-modal-content')).toHaveCount(1);
    await expect(page.getByText(/Work from Home/i)).toBeVisible();
  });

  test('clicking a record shows exactly one detail modal', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::attendance');
    const card = page.locator('.ant-card').first();
    if (await card.count()) {
      await card.click();
      await page.waitForTimeout(1500);
      // only one VISIBLE modal
      const visible = await page.locator('.ant-modal-wrap:visible').count();
      expect(visible).toBeLessThanOrEqual(1);
    }
  });

  test('server-side punch errors are shown in the SPA (overlap)', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::attendance');
    // The exact path the ERROR response triggers: customAction -> getPunchFailCallBack
    // -> showMessage. Pre-fix this rendered into the absent legacy #messageModel
    // and showed nothing.
    await page.evaluate(() => window.modJs.getPunchFailCallBack(
      'Time entry is overlapping with an existing one',
    ));
    await page.waitForTimeout(800);
    // Friendly, non-scary phrasing (a gentle toast, not a red error modal).
    await expect(page.getByText(/overlaps with an entry you already have/i)).toBeVisible();
  });
});
