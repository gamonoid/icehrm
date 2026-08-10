const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// extension::learn|admin (Courses / LMS) converted to native via the meta.json
// native block: Course, Lessons and Enrollments of Direct Reports card lists
// reusing the existing initAdminLearn. Seed: one published course, 4 lessons,
// one enrollment.
test.describe('extension::learn|admin — native SPA', () => {
  test('mounts natively with all three tabs and the seeded course', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::learn|admin');
    await expectNative(page, ['Course', 'Lessons', 'Enrollments of Direct Reports']);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.getByText('Add Your First Employee to IceHrm')).toBeVisible({ timeout: 15000 });
    await expect(active.locator('.ant-tag', { hasText: 'Published' })).toBeVisible();
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('lessons tab lists the seeded lessons', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::learn|admin');
    await openTab(page, 'Lessons');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
  });
});
