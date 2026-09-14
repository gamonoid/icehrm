const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// modules::dashboard — the native employee/manager personal dashboard. Keeps the
// "My To-Do List" (opened in the in-shell editor) and adds attendance stats,
// celebrations, and (for managers + admins) a direct-reports overview.
test.describe('modules::dashboard (native personal dashboard)', () => {
  test('employee: mounts natively with greeting, To-Do list and celebrations', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'user1');
    await openModule(page, 'modules::dashboard');
    await page.waitForTimeout(3000);

    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    await expect(page.getByText(/Good (morning|afternoon|evening),/)).toBeVisible();
    await expect(page.getByText('My To-Do List')).toBeVisible();
    await expect(page.getByText('Upcoming celebrations')).toBeVisible();
    // Employees do not see the manager-only Direct Reports card.
    await expect(page.getByText('Direct Reports', { exact: true })).toHaveCount(0);
  });

  test('manager: also sees Direct reports stat and the Direct Reports card', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'manager');
    await openModule(page, 'modules::dashboard');
    await page.waitForTimeout(3000);

    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    await expect(page.getByText('Direct Reports', { exact: true })).toBeVisible();
    await expect(page.getByText(/Direct reports/)).toBeVisible();
  });

  test('admin sees the same (manager) view', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'modules::dashboard');
    await page.waitForTimeout(3000);
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    await expect(page.getByText('Direct Reports', { exact: true })).toBeVisible();
  });

  test('My To-Do List shows actionable system tasks (not task lists)', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'modules::dashboard');
    await page.waitForTimeout(3000);
    const card = page.locator('.ant-card', { hasText: 'My To-Do List' });
    // The to-do widget is a timeline of action items, each rendered as a dot.
    await expect(card.locator('.ant-timeline-item').first()).toBeVisible();
    // Setup/onboarding tasks the SystemTasks service produces.
    await expect(page.getByText(/checked-in|status for the day|holidays defined/i).first()).toBeVisible();
  });
});
