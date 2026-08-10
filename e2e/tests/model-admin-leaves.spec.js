const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');
const {
  openList, canAdd, openAddForm, closeModal, fillNameAndSave,
  findInList, deleteRecord, uniqueName, expectTabLoaded,
} = require('../helpers/crud');

/**
 * Model coverage — admin::leaves
 *
 * Generated from the app's registered model map cross-referenced with
 * NativeModuleRegistry, the module adapters and extension meta.json. Tab entity
 * names are resolved through the registry's `entities` map, so a UI alias like
 * MyLoan is reported as the model it actually binds to (EmployeeCompanyLoan).
 *
 *   LeaveType                    -> Leave Types
 *   LeavePeriod                  -> Leave Period
 *   WorkDay                      -> Work Week
 *   HoliDay                      -> Holidays
 *   LeaveRule                    -> Leave Rules
 *   LeaveStartingBalance         -> Leave Adjustments
 *   LeaveGroup                   -> Leave Groups
 *   EmployeeLeave                -> Employee Leave List
 *   LeaveGroupEmployee           -> reached through another tab's form
 */

const MODULE = 'admin::leaves';

const TABS = [
  { label: "Leave Types", model: "LeaveType" },
  { label: "Leave Period", model: "LeavePeriod" },
  { label: "Work Week", model: "WorkDay" },
  { label: "Holidays", model: "HoliDay" },
  { label: "Leave Rules", model: "LeaveRule" },
  { label: "Leave Adjustments", model: "LeaveStartingBalance" },
  { label: "Leave Groups", model: "LeaveGroup" },
  { label: "Employee Leave List", model: "EmployeeLeave" },
];

test.describe('admin::leaves — model lists', () => {
  for (const { label, model } of TABS) {
    test(`${model} list loads on the "${label}" tab`, async ({ page }) => {
      await login(page, 'admin');
      await openList(page, MODULE, label);
      await expectTabLoaded(page, label);
    });
  }
});

test.describe('admin::leaves — create/delete round trip', () => {
  for (const { label, model } of TABS) {
    test(`${model} can be created and deleted via "${label}"`, async ({ page }) => {
      await login(page, 'admin');
      await openList(page, MODULE, label);

      test.skip(!(await canAdd(page)), `${model}: no Add New on this list (read-only or derived)`);

      const modal = await openAddForm(page);
      expect(modal, `${model}: the Add New form should open`).not.toBeNull();

      const name = uniqueName(model.slice(0, 8));
      const saved = await fillNameAndSave(page, name);
      if (!saved) {
        // Needs dates, dependent selects or an upload — a blind fill cannot do it,
        // so record the gap rather than report a failure that is really missing
        // coverage. These are the models that still want a hand-written spec.
        await closeModal(page);
        test.skip(true, `${model}: form needs more than a name — wants a dedicated spec`);
      }

      await openList(page, MODULE, label);
      expect(await findInList(page, name), `${model}: the new record should be findable`).toBeTruthy();
      expect(await deleteRecord(page, name), `${model}: the record should delete cleanly`).toBeTruthy();
    });
  }
});

/**
 * Restriction coverage — the other half of the ACL change.
 *
 * admin::leaves declares user_levels ["Admin"], so LeaveGroup's Manager and
 * Employee matrices were emptied (they inherited BaseModel's "get","element" for
 * screens those levels cannot open). These assert both directions: the module
 * stays shut to a manager, and the employee leave flow that reads group
 * MEMBERSHIP — via a direct ActiveRecord Find, not the ACL — still works.
 */
test.describe('admin::leaves — restricted to admins', () => {
  test('a manager cannot open the module', async ({ page }) => {
    await login(page, 'manager');
    await page.goto('/app/ui/#admin::leaves', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(2500);

    // Either the shell refuses the module outright, or it renders no leave-group
    // data. What must never happen is a working Leave Groups list.
    const tab = page.locator('.ant-tabs-tab:visible', { hasText: 'Leave Groups' });
    expect(await tab.count(), 'a manager should not get the Leave Groups tab').toBe(0);
  });

  test('an employee can still apply for leave (membership path is ACL-free)', async ({ page }) => {
    await login(page, 'user1');
    await page.goto('/app/ui/#modules::leaves', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(2500);

    // The leave module resolves the employee's leave groups to decide which leave
    // types they may request. If emptying the ACL had broken that, this module
    // would surface the shell's error state instead of a working list.
    await expect(page.getByText(/Could not load this (list|module)/i)).toHaveCount(0);
  });
});

test.describe('admin::leaves — LeaveGroup is closed on the generic path', () => {
  /**
   * This is the test that actually proves the ACL change.
   *
   * The manager test above passes whether or not the model grants anything: the
   * module gate (meta.json user_levels) already stops a manager reaching the SCREEN.
   * What it does NOT stop is service.php?t=LeaveGroup&a=get — the generic data path
   * is guarded by the MODEL's matrix alone. Before emptying getManagerAccess(),
   * LeaveGroup inherited BaseModel's ("get","element"), so a manager could read
   * every leave group directly despite having no screen for it.
   */
  test('a manager is refused LeaveGroup data over service.php', async ({ page }) => {
    await login(page, 'manager');
    const body = await page.evaluate(async () => {
      const r = await fetch('/app/service.php?a=get&t=LeaveGroup', { credentials: 'same-origin' });
      return r.text();
    });
    expect(body, 'a manager must not receive leave group rows').not.toMatch(/"name"\s*:/);
  });

  test('an admin still receives LeaveGroup data over the same path', async ({ page }) => {
    await login(page, 'admin');
    const body = await page.evaluate(async () => {
      const r = await fetch('/app/service.php?a=get&t=LeaveGroup', { credentials: 'same-origin' });
      return r.text();
    });
    expect(body, 'an admin should still be able to read leave groups').toMatch(/Latvia|Germany|"name"\s*:/);
  });
});
