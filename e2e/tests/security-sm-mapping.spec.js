const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

/**
 * Security regression — the `sm` (source-mapping) parameter.
 *
 * A list/detail request may carry `sm` = {field: [Model, lookupCol, displayCols]}
 * to resolve foreign keys to display values. Both column names arrive verbatim from
 * the client and were never sanitised, giving a plain Employee two things:
 *
 *   arbitrary column read — {"employee":["User","employee","password"]} replaced
 *     each row's employee field with that user's bcrypt hash, bypassing User's ACL;
 *   SQL injection — the lookup column is concatenated into the WHERE string, so
 *     ["User","1=1 OR (subquery)","x"] reaches SQL.
 *
 * The fix requires every requested column to be a published fieldValueFields() entry
 * of the target model (the same allowlist the pickers use). A real column that the
 * model has declared safe to expose passes; anything else — a sensitive column, or
 * an injection fragment — is skipped.
 */

async function getWithMapping(page, model, mapping) {
  return page.evaluate(async ([m, sm]) => {
    const url = `/app/service.php?a=get&t=${m}&sm=${encodeURIComponent(JSON.stringify(sm))}`;
    const r = await fetch(url, { credentials: 'same-origin' });
    let rows = [];
    try { rows = (await r.json()).object || []; } catch (e) { /* non-JSON */ }
    return { http: r.status, rows };
  }, [model, mapping]);
}

test.describe('sm mapping cannot read sensitive columns or inject SQL', () => {
  test('mapping to Users.password does not leak the hash', async ({ page }) => {
    await login(page, 'user1');
    const { rows } = await getWithMapping(page, 'EmployeeLeave', {
      employee: ['User', 'employee', 'password'],
    });
    expect(rows.length, 'the employee should still see their own leave rows').toBeGreaterThan(0);
    for (const row of rows) {
      expect(String(row.employee), 'employee field must not become a bcrypt hash')
        .not.toMatch(/^\$2[aby]\$/);
    }
  });

  test('an injection fragment in the lookup column does not reach SQL', async ({ page }) => {
    await login(page, 'user1');
    // A malformed WHERE would 500; a skipped mapping returns the list normally.
    const { http } = await getWithMapping(page, 'EmployeeLeave', {
      employee: ['User', "1=1 OR ('a'='a", 'password'],
    });
    expect(http, 'the injection payload must be rejected, not executed').toBe(200);
  });

  test('a legitimate mapping still resolves the display value', async ({ page }) => {
    await login(page, 'user1');
    const { rows } = await getWithMapping(page, 'EmployeeLeave', {
      leave_type: ['LeaveType', 'id', 'name'],
    });
    expect(rows.length).toBeGreaterThan(0);
    // leave_type should now be a name string, not the raw numeric id.
    expect(String(rows[0].leave_type), 'the leave type should resolve to its name')
      .toMatch(/[A-Za-z]/);
  });
});
