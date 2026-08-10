const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// Self-service modules for the logged-in employee (user1). The backend scopes
// records to the current user's own employee, same as legacy.
test.describe('user self-service modules (native)', () => {
  test('dependents mounts natively', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::dependents');
    await expectNative(page, ['Dependents']);
  });

  test('emergency contacts mounts natively', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::emergency_contact');
    await expectNative(page, ['Emergency Contacts']);
  });

  test('qualifications mounts natively with all tabs', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::qualifications');
    const tabs = ['Skills', 'Education', 'Certifications', 'Languages'];
    await expectNative(page, tabs);
    for (const t of tabs) {
      await openTab(page, t);
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });
});
