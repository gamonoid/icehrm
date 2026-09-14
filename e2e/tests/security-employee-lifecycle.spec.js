const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

/**
 * Security regression — employee lifecycle is Admin-only.
 *
 * The a=ca module gate lets a Manager into admin=employees (they legitimately edit
 * subordinates there). But deleteEmployee and activateEmployee had NO authorization
 * check, so a Manager could delete any employee (and receive the full archived
 * record — salary, SSN, NIC — that deleteEmployee returns) or reactivate anyone.
 * Employee creation via the generic add path was likewise open to Managers.
 *
 * Fix: Employee::getManagerAccess drops "add" (delete was never granted), and the
 * lifecycle action methods enforce checkSecureAccess('delete') — only Admin holds
 * "delete" on the Employee model.
 *
 * These probes use a non-existent id, so nothing is created or deleted: a Manager is
 * refused at the authorization layer (403) before the record is even looked at,
 * while an Admin passes authorization and only then hits "not found" (not a 403).
 * The destructive end-to-end paths were verified once against seeded throwaway rows.
 */

async function ca(page, sa, extra) {
  return page.evaluate(async ([s, e]) => {
    const body = new URLSearchParams(Object.assign({ a: 'ca', mod: 'admin=employees', sa: s }, e));
    const r = await fetch('/app/service.php', { method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() });
    return { http: r.status };
  }, [sa, extra]);
}

const BOGUS = { id: '99999999' };

test.describe('employee lifecycle is Admin-only', () => {
  test('a Manager cannot delete an employee', async ({ page }) => {
    await login(page, 'manager');
    expect((await ca(page, 'deleteEmployee', BOGUS)).http, 'manager delete must be forbidden').toBe(403);
  });

  test('a Manager cannot activate an employee', async ({ page }) => {
    await login(page, 'manager');
    expect((await ca(page, 'activateEmployee', BOGUS)).http, 'manager activate must be forbidden').toBe(403);
  });

  test('a Manager cannot add an employee via the generic path', async ({ page }) => {
    await login(page, 'manager');
    const r = await page.evaluate(async () => {
      const b = new URLSearchParams({ a: 'add', t: 'Employee', first_name: 'ZZ', last_name: 'X' });
      const res = await fetch('/app/service.php', { method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: b.toString() });
      return res.status;
    });
    expect(r, 'manager add must be forbidden').toBe(403);
  });

  test('an Admin passes authorization (reaches the not-found path, not a 403)', async ({ page }) => {
    await login(page, 'admin');
    expect((await ca(page, 'deleteEmployee', BOGUS)).http, 'admin must not be blocked by the gate')
      .not.toBe(403);
  });
});
