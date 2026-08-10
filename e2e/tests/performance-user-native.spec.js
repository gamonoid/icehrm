const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative,
} = require('../helpers/app');

// modules::performance (PRO) converted to native: Self Assessments (view-only
// review cards titled by period) + Feedback Requests (Give Feedback / Submit
// actions). The existing initPerformaceModule carries over; the registry sends
// the same flat permissions the legacy page hardcodes, trimmed where the legacy
// UI exposed no matching action. Seed: user1 (emp 147) has a Pending review.
test.describe('modules::performance — native SPA', () => {
  test('mounts natively with both tabs and the pending self assessment', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::performance');
    await expectNative(page, ['Self Assessments', 'Feedback Requests']);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(active.locator('.ant-tag', { hasText: 'Pending' })).toBeVisible();
    // View-only: the eye shows, edit/delete don't.
    await expect(active.locator('.anticon-eye').first()).toBeVisible();
    await expect(active.locator('.anticon-delete')).toHaveCount(0);
  });
});
