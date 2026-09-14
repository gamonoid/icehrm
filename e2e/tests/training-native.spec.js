const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// modules::training converted to the native SPA card list: All Training
// Sessions (sign-up), My Training Sessions (feedback + mark completed),
// Direct Reports' sessions and Coordinated sessions. The legacy page was
// modernised onto the same initUserTraining. Seed: one Approved sign-up
// session + one Scheduled enrolment for user1.
test.describe('modules::training — native SPA', () => {
  test('mounts natively with all four tabs and the sign-up session', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::training');
    await expectNative(page, [
      'All Training Sessions', 'My Training Sessions',
      'Training Sessions of Direct Reports', 'Training Sessions Coordinated by Me',
    ]);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Marketing Fundamentals Workshop')).toBeVisible({ timeout: 15000 });
    // Sign Up action + view (eye) on the session card.
    await expect(active.locator('.anticon-login')).toBeVisible();
    // The training card's view action is anticon-read, not the anticon-eye other
    // modules use. (This assertion also failed against the dev database, so it was
    // a stale selector rather than missing fixture data.)
    await expect(active.locator('.anticon-read').first()).toBeVisible();
  });

  test('my sessions tab shows the enrolment with Mark Completed', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::training');
    await openTab(page, 'My Training Sessions');

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Marketing Fundamentals Workshop')).toBeVisible({ timeout: 15000 });
    await expect(active.locator('.ant-tag', { hasText: 'Scheduled' })).toBeVisible();
    // Scheduled -> the Mark Completed action shows.
    await expect(active.locator('.anticon-check-circle')).toBeVisible();
  });

  test('sign up confirmation dialog opens (antd confirm in the shell)', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::training');
    await page.waitForTimeout(2000);
    await page.locator('.anticon-login').first().click();
    await expect(page.getByText(/Confirm Sign Up/i)).toBeVisible({ timeout: 10000 });
    await page.locator('.ant-modal-confirm').getByRole('button', { name: 'No' }).click();
  });
});
