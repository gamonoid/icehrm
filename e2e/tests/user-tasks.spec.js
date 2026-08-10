const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative,
} = require('../helpers/app');

// extension::tasks|user — the employee's "My Task Lists". Mounts natively via the
// meta.json `native` block (auto-discovered). MyTaskList self-scopes to the
// employee's own + assigned lists, and each row opens the task list in the
// in-shell native editor (no iframe).
test.describe('extension::tasks|user (native)', () => {
  test('mounts natively (no iframe), single tab, no Add New', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'extension::tasks|user');
    await expectNative(page, ['My Task Lists']);
    // Self-service list — employees do not create task lists here.
    await expect(page.getByRole('button', { name: /Add New/i })).toHaveCount(0);
  });

  test('list loads without error (empty or populated)', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'extension::tasks|user');
    await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    // Either records, or the empty-state — never a load error / iframe fallback.
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
  });
});
