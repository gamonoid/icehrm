const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// Leave types with "Require a reason" = Yes must not be applied for without a
// reason (the "details" field). This is a frontend-only guard: getLeaveDays
// returns reason_required, and getLeaveDaysSuccessCallBack blocks before the
// day-selection modal. By default reason_required is 'No', so the reason stays
// optional. Test data: Casual leave (id 2) is flagged reason_required = Yes;
// Annual leave (id 1) is not.
test.describe('modules::leaves — reason requirement', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::leaves');
    await page.waitForTimeout(1500);
  });

  test('getLeaveDays flags a reason-required leave type', async ({ page }) => {
    const result = await page.evaluate(async () => {
      const call = async (leaveType) => {
        const req = JSON.stringify({ start_date: '2026-08-10', end_date: '2026-08-10', leave_type: String(leaveType) });
        const url = `/app/service.php?g=modules&n=leaves&t=EmployeeLeave&a=ca&sa=getLeaveDays`
          + `&mod=${encodeURIComponent('modules=leaves')}&req=${encodeURIComponent(req)}`;
        const r = await fetch(url, { credentials: 'same-origin' });
        const j = await r.json();
        return j.data && j.data[2] && j.data[2].reason_required;
      };
      return { casual: await call(2), annual: await call(1) };
    });
    expect(result.casual).toBe('Yes');
    expect(result.annual).toBe('No');
  });

  test('blocks applying for a reason-required leave type with no reason', async ({ page }) => {
    // Drive the real apply flow (round-trip + guard) with an empty reason.
    await page.evaluate(() => window.modJs.getLeaveDaysWithValues({
      date_start: '2026-08-10', date_end: '2026-08-10', leave_type: '2', details: '',
    }));
    await expect(page.getByText(/A reason is required for this leave type/i)).toBeVisible({ timeout: 15000 });
    // The day-selection modal must not have opened.
    await expect(page.getByText(/Select Leave Days/i)).toHaveCount(0);
  });

  test('allows applying for a reason-required leave type once a reason is given', async ({ page }) => {
    await page.evaluate(() => window.modJs.getLeaveDaysWithValues({
      date_start: '2026-08-10', date_end: '2026-08-10', leave_type: '2', details: 'Family matters',
    }));
    // No reason guard fires; the flow proceeds past it (no "Reason Required").
    await page.waitForTimeout(2000);
    await expect(page.getByText(/A reason is required for this leave type/i)).toHaveCount(0);
  });

  test('reason field is not marked required on the apply form', async ({ page }) => {
    // The Reason field is optional by default (enforcement is the reason_required
    // guard, not a form-level rule), so it must not show the mandatory asterisk.
    await page.evaluate(() => window.modJs.renderForm());
    await page.waitForTimeout(1500);
    const fields = await page.evaluate(() => Array.from(document.querySelectorAll('.ant-form-item-label label'))
      .map((lbl) => ({ label: lbl.textContent.trim(), required: lbl.classList.contains('ant-form-item-required') })));
    const reason = fields.find((f) => f.label === 'Reason');
    const leaveType = fields.find((f) => f.label === 'Leave Type');
    expect(reason).toBeTruthy();
    expect(reason.required).toBe(false);
    // Sanity: genuinely required fields still show the asterisk.
    expect(leaveType.required).toBe(true);
  });
});
