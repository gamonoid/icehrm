const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

/**
 * Security regression — file-download authorization (service.php?a=file).
 *
 * a=file resolves a Files.name to a signed download URL. Before the fix it performed
 * no authorization: any logged-in employee could mint a link for any file by name —
 * other employees' HR documents, and the employee-export reports whose names are
 * guessable (Report_<name>_<timestamp>). Verified live: a plain Employee resolved
 * the Active Employee Report (SSN, salary, address for every employee).
 *
 * The fix (BaseService::currentUserCanAccessFile) restricts resolution to Admin, the
 * owning employee, or a manager over the owner; owner-less files (reports live here)
 * are admin-only.
 *
 * The full owner/manager matrix is covered deterministically in
 * test/integration/security/FileAccessTest.php. This spec pins the demonstrated
 * exposure over the real HTTP path.
 */

async function resolveFile(page, name) {
  return page.evaluate(async (n) => {
    const r = await fetch('/app/service.php?a=file&name=' + encodeURIComponent(n), { credentials: 'same-origin' });
    let code = null;
    try { const j = JSON.parse(await r.text()); code = j.code || j.status; } catch (e) { /* non-JSON */ }
    return { http: r.status, code };
  }, name);
}

// An owner-less HR-export report — the worst case from the audit. Name is what an
// attacker would guess from the fixed report list + a timestamp.
const REPORT = 'Report_Active_Employee_Report-2026-07-08_12-24-11';

test.describe('a=file enforces file ownership', () => {
  test('a plain Employee cannot resolve an owner-less HR export', async ({ page }) => {
    await login(page, 'user1');
    const r = await resolveFile(page, REPORT);
    expect(r.http, 'the request should be forbidden').toBe(403);
    expect(r.code).toBe('FILE_ACCESS_DENIED');
  });

  test('a Manager cannot resolve an owner-less HR export', async ({ page }) => {
    await login(page, 'manager');
    const r = await resolveFile(page, REPORT);
    expect(r.http, 'a manager is not an admin for owner-less exports').toBe(403);
  });

  test('an Admin can still resolve it', async ({ page }) => {
    await login(page, 'admin');
    const r = await resolveFile(page, REPORT);
    // 200 SUCCESS when the file exists; if the fixture lacks it, the point is only
    // that the admin is NOT blocked by the authorization gate.
    expect(r.http).not.toBe(403);
  });
});
