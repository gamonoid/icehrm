const { expect } = require('@playwright/test');

const PASSWORD = 'Admin123$';

/** Log in via the login form; the session cookie then carries into the SPA. */
async function login(page, username) {
  await page.goto('/app/login.php', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="username"], #user', username).catch(async () => {
    await page.fill('#username', username);
  });
  await page.fill('input[name="password"], #password, #pass', PASSWORD);
  await Promise.all([
    page.waitForLoadState('networkidle').catch(() => {}),
    page.evaluate(() => {
      const u = document.querySelector('input[name="username"], #username, #user');
      const f = u && u.closest('form');
      if (f) f.submit();
    }),
  ]);
  // Land somewhere inside the app (not back on the login page).
  await expect(page).not.toHaveURL(/login\.php/, { timeout: 20000 });
}

/** Open a module in the SPA by its route key, e.g. 'admin::training'. */
async function openModule(page, route) {
  await page.goto(`/app/ui/#${route}`, { waitUntil: 'domcontentloaded' });
  // Shell chrome present.
  await page.waitForSelector('.ant-layout', { timeout: 30000 });
  // Give the native host / iframe a moment to resolve.
  await page.waitForTimeout(2500);
}

/** Assert a module is mounted natively (no iframe) and shows the expected tabs. */
async function expectNative(page, tabs = []) {
  await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
  if (tabs.length > 1) {
    for (const t of tabs) {
      await expect(page.locator('.ant-tabs-tab', { hasText: t }).first()).toBeVisible();
    }
  }
  // No "could not load" error states.
  await expect(page.getByText(/Could not load this (list|module)/i)).toHaveCount(0);
}

/** Switch to a tab by visible label (no-op for single-tab modules). */
async function openTab(page, label) {
  const tab = page.locator('.ant-tabs-tab', { hasText: label }).first();
  if (await tab.count()) {
    await tab.click();
    await page.waitForTimeout(1200);
  }
}

/** Count the record cards currently shown in the active area/tab. */
async function cardCount(page) {
  // NativeCardList renders one .ant-card per record (plus none for empty state).
  return page.locator('.ant-card').count();
}

module.exports = {
  PASSWORD, login, openModule, expectNative, openTab, cardCount,
};
