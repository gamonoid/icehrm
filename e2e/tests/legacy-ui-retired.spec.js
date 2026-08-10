const { test, expect } = require('@playwright/test');
const { login, PASSWORD } = require('../helpers/app');

// The legacy UI has been retired. This is the ONLY test that should reference it:
// the per-module "legacy page still renders" specs and the old UI-switcher spec
// were deleted along with the feature.
//
// Two guarantees, and nothing else:
//   1. Logging in never lands anyone on the legacy UI.
//   2. There is no way to switch into the legacy UI.
//
// Everything under /app/ that is not /app/ui/ is legacy, so the assertion is
// simply: we always end up inside /app/ui/.

const SPA = '/app/ui/';
const ROLES = ['admin', 'manager', 'user1'];

/** Assert the browser is sitting in the SPA and not on any legacy page. */
async function expectInSpa(page, what) {
  expect(page.url(), `${what}: should be in the SPA`).toContain(SPA);
  // The SPA shell must actually be mounted — a legacy page would never have it.
  // (.ant-layout nests, so match the outermost one.)
  await expect(page.locator('.ant-layout').first(), `${what}: SPA shell`)
    .toBeVisible({ timeout: 30000 });
}

test.describe('legacy UI is retired', () => {
  test('login never lands on the legacy UI, for any role', async ({ page }) => {
    for (const role of ROLES) {
      await page.goto('/app/login.php?logout', { waitUntil: 'domcontentloaded' });
      await login(page, role);
      await page.waitForTimeout(1500);
      await expectInSpa(page, `login as ${role}`);
    }
  });

  test('a login "next" pointing at a legacy page still lands in the SPA', async ({ page }) => {
    // login.php honours ?next=<base64url> — it must not become a way back into legacy.
    const next = Buffer.from('?g=modules&n=dashboard&m=module_Personal_Information')
      .toString('base64').replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

    await page.goto('/app/login.php?logout', { waitUntil: 'domcontentloaded' });
    await page.goto('/app/login.php', { waitUntil: 'domcontentloaded' });
    await page.fill('#username', 'user1');
    await page.fill('#password', PASSWORD);
    await page.evaluate((n) => {
      const f = document.querySelector('#loginForm');
      f.querySelector('#next').value = n;
      f.submit();
    }, next);
    await page.waitForTimeout(3000);
    await expectInSpa(page, 'login with legacy next=');
  });

  test('the UI switcher is gone and cannot put you back in legacy', async ({ page }) => {
    await login(page, 'user1');
    // switch-ui.php was deleted with the feature; it must not resolve.
    const resp = await page.goto('/app/switch-ui.php?to=legacy', { waitUntil: 'domcontentloaded' });
    expect(resp.status(), 'switch-ui.php should not exist').toBe(404);

    // And the session is still on the new UI afterwards.
    await page.goto(SPA, { waitUntil: 'domcontentloaded' });
    await expectInSpa(page, 'after switch-ui attempt');
  });

  test('legacy deep links bounce to the SPA', async ({ page }) => {
    await login(page, 'admin');
    const legacyLinks = [
      '/app/?g=modules&n=dashboard&m=module_Personal_Information',
      '/app/?g=admin&n=employees',
      '/app/?g=extension&n=learn&sub=admin',
    ];
    for (const link of legacyLinks) {
      await page.goto(link, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(2000);
      await expectInSpa(page, `deep link ${link}`);
    }
  });

  test('the SPA offers no affordance to switch back to legacy', async ({ page }) => {
    await login(page, 'user1');
    await page.goto(SPA, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(1500);
    await expect(page.getByRole('button', { name: /Legacy UI/i })).toHaveCount(0);
    await expect(page.locator('.ice-new-ui-promo')).toHaveCount(0);
    await expect(page.locator('a[href*="switch-ui"]')).toHaveCount(0);
  });
});
