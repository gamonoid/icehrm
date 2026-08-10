const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

// Data-scope: scope (admin all-rows vs user own-rows) must be decided per-request
// from the module being viewed, NOT from the shared session modulePath.
// See docs/DATA_SCOPE_ISSUE.md.

async function spaOpen(page, route) {
  await page.evaluate((r) => { window.location.hash = r; }, route);
  await page.waitForTimeout(3500);
}

function distinctEmployees(page) {
  // Attendance cards render the employee name as the card title (first line),
  // not an "Employee: X" meta line.
  return page.evaluate(() => [...new Set(
    Array.from(document.querySelectorAll('.ant-tabs-tabpane-active .ant-card'))
      .map((c) => {
        const title = c.querySelector('.ant-card-meta-title, .ant-card-head-title');
        const text = (title ? title.innerText : c.innerText) || '';
        return text.split('\n')[0].trim();
      })
      .filter(Boolean),
  )]);
}

test.describe('per-request data scope', () => {
  test('admin attendance keeps ALL-user scope after visiting My Attendance', async ({ page }) => {
    await login(page, 'admin');

    await page.goto('/app/ui/#admin::attendance', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(3500);
    const before = await distinctEmployees(page);
    expect(before.length, 'admin attendance shows multiple employees').toBeGreaterThan(1);

    // visiting the user module poisoned the session modulePath pre-fix
    await spaOpen(page, 'modules::attendance');
    await spaOpen(page, 'admin::attendance');

    const after = await distinctEmployees(page);
    expect(after.length, 'still admin scope (multiple employees) after detour').toBeGreaterThan(1);
  });

  test('non-admin cannot claim admin scope via mg/mn (no escalation)', async ({ page }) => {
    await login(page, 'user1');
    const res = await page.evaluate(async () => {
      const u = '/app/data.php?t=Attendance&sm=%7B%7D&cl=%5B%22id%22%5D&ft=%22%22'
        + '&iDisplayStart=0&iDisplayLength=8&ob=&mg=admin&mn=attendance&version=v2';
      const r = await fetch(u, { method: 'POST', credentials: 'same-origin' });
      let body = null;
      try { body = await r.json(); } catch (e) { body = null; }
      return { status: r.status, code: body && body.code };
    });
    // The server must reject the forged admin-module claim.
    expect(res.status === 403 || res.code === 'MODULE_ACCESS_DENIED').toBeTruthy();
  });
});
