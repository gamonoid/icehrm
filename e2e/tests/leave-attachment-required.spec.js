const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// Leave types with "Require a supporting document" = Yes must not be applied for
// without an attachment — for everyone, admins included. Two layers enforce this:
//   1. getLeaveDays returns attachment_required (Yes/No) so the SPA blocks up-front,
//      before the day-selection modal, with a clear message.
//   2. addLeave rejects the application outright — the final safety net.
// "Medical leave" (seed id 3) requires a document; "Annual leave" (id 1) does not.
// Both are available to all employees (leave_group is NULL).
//
// These assertions drive the real endpoints through the authenticated session
// (the SPA's getLeaveDaysSuccessCallBack guard reads exactly attachment_required),
// which is stable regardless of form-widget rendering. The addLeave call is
// rejected, so no leave record is created.
async function callLeaves(page, subAction, reqObj) {
  return page.evaluate(async ({ sa, req }) => {
    const url = `/app/service.php?g=modules&n=leaves&t=EmployeeLeave&a=ca`
      + `&sa=${encodeURIComponent(sa)}`
      + `&mod=${encodeURIComponent('modules=leaves')}`
      + `&req=${encodeURIComponent(JSON.stringify(req))}`;
    const r = await fetch(url, { credentials: 'same-origin' });
    return r.json();
  }, { sa: subAction, req: reqObj });
}

test.describe('modules::leaves — supporting document requirement', () => {
  // Runs for a regular employee and an admin: the rule applies to both.
  for (const username of ['user1', 'admin']) {
    test(`${username}: getLeaveDays flags a document-required leave type`, async ({ page }) => {
      await login(page, username);
      await openModule(page, 'modules::leaves');
      await page.waitForTimeout(1200);

      const medical = await callLeaves(page, 'getLeaveDays', {
        start_date: '2026-08-10', end_date: '2026-08-10', leave_type: '3',
      });
      expect(medical.status).toBe('SUCCESS');
      expect(medical.data[2].attachment_required).toBe('Yes');

      const annual = await callLeaves(page, 'getLeaveDays', {
        start_date: '2026-08-10', end_date: '2026-08-10', leave_type: '1',
      });
      expect(annual.status).toBe('SUCCESS');
      expect(annual.data[2].attachment_required).toBe('No');
    });

    test(`${username}: addLeave rejects a document-required leave with no attachment`, async ({ page }) => {
      await login(page, username);
      await openModule(page, 'modules::leaves');
      await page.waitForTimeout(1200);

      const res = await callLeaves(page, 'addLeave', {
        date_start: '2026-08-10',
        date_end: '2026-08-10',
        leave_type: '3',
        details: 'e2e attachment guard',
        attachment: '',
        days: JSON.stringify({ '2026-08-10': 'Full Day' }),
      });
      expect(res.status).toBe('ERROR');
      expect(String(res.data)).toMatch(/attachment is mandatory/i);
    });
  }
});
