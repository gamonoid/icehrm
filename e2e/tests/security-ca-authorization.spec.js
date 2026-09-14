const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

/**
 * Security regression — custom-action (`a=ca`) module authorization.
 *
 * The custom-action dispatcher in core/service.php resolves a module's action
 * manager from the request `mod` parameter and invokes the named method. A module's
 * meta.json `user_levels` gates the MENU, not this dispatch — so before the fix, any
 * logged-in user could invoke any module's actions directly, regardless of level:
 *
 *   POST service.php  a=ca&mod=admin=salary&sa=<any action of that module>
 *   → every employee's salary rows, returned to a plain Employee.
 *
 * deleteEmployee and saveUsage (disable every module) were reachable the same way.
 * The fix enforces the module's declared user_levels at the dispatch point
 * (ModuleAccessService::userMayAccessModuleLevels).
 *
 * These tests assert the property in BOTH directions — a lower level is denied an
 * out-of-scope module, and each level keeps its own — so a future change that
 * loosens the gate fails here, and one that over-tightens it also fails here.
 *
 * The probes use deliberately READ-ONLY or non-existent actions. A denied request
 * returns HTTP 403 with code MODULE_ACCESS_DENIED before the action runs; an allowed
 * request gets past the gate (200, whatever the action then does). We assert on the
 * gate, not on the action's own output.
 */

async function ca(page, mod, sa, extra) {
  return page.evaluate(async ([m, s, e]) => {
    const body = new URLSearchParams(Object.assign({ a: 'ca', mod: m, sa: s, req: '{}' }, e || {}));
    const r = await fetch('/app/service.php', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString(),
    });
    const text = await r.text();
    let code = null;
    try { code = JSON.parse(text).code || null; } catch (e2) { /* non-JSON body */ }
    return { http: r.status, code, bytes: text.length };
  }, [mod, sa, extra]);
}

const DENIED = (r) => r.http === 403 && r.code === 'MODULE_ACCESS_DENIED';
const ALLOWED = (r) => r.http !== 403;

test.describe('a=ca dispatcher enforces module user_levels', () => {
  test('a plain Employee is denied Admin-only modules', async ({ page }) => {
    await login(page, 'user1');

    // A representative spread of Admin-only modules (meta.json user_levels = Admin).
    // We never invoke the destructive actions with real effect — the gate rejects them
    // before they run — but naming them documents exactly what the gate is protecting.
    for (const mod of ['admin=salary', 'admin=modules', 'admin=audit', 'admin=users']) {
      expect(DENIED(await ca(page, mod, 'nonexistentProbe')),
        `Employee must be denied ${mod}`).toBeTruthy();
    }
  });

  test('a Manager is denied Admin-only modules but keeps Manager modules', async ({ page }) => {
    await login(page, 'manager');

    // Admin-only: denied.
    expect(DENIED(await ca(page, 'admin=salary', 'nonexistentProbe')),
      'Manager must not reach admin=salary (Admin-only)').toBeTruthy();
    expect(DENIED(await ca(page, 'admin=users', 'nonexistentProbe')),
      'Manager must not reach admin=users (Admin-only)').toBeTruthy();

    // Manager-allowed (user_levels include Manager): reaches the dispatch.
    for (const mod of ['admin=overtime', 'admin=travel', 'admin=employees']) {
      expect(ALLOWED(await ca(page, mod, 'nonexistentProbe')),
        `Manager should reach ${mod}`).toBeTruthy();
    }
  });

  test('an Admin reaches Admin modules', async ({ page }) => {
    await login(page, 'admin');
    for (const mod of ['admin=salary', 'admin=modules', 'admin=audit', 'admin=users']) {
      expect(ALLOWED(await ca(page, mod, 'nonexistentProbe')),
        `Admin should reach ${mod}`).toBeTruthy();
    }
  });

  test('an Employee keeps their own user-side modules', async ({ page }) => {
    await login(page, 'user1');
    // The employee legitimately drives these through a=ca (submit overtime, log
    // attendance, timesheets, travel). The gate must not block them.
    for (const mod of ['user=overtime', 'user=attendance', 'user=time_sheets', 'user=travel']) {
      expect(ALLOWED(await ca(page, mod, 'nonexistentProbe')),
        `Employee should reach their own ${mod}`).toBeTruthy();
    }
  });
});
