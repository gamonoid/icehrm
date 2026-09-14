const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, cardCount,
} = require('../helpers/app');

test.describe('admin::fieldnames (native)', () => {
  test('mounts natively (single tab) and lists field mappings', async ({ page }) => {
    await login(page, 'admin');
    await openModule(page, 'admin::fieldnames');
    await expectNative(page, ['Field Names']);
    // Field name mappings are seeded, so there should be records.
    expect(await cardCount(page)).toBeGreaterThan(0);
  });
});
