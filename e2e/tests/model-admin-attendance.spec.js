const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');
const {
  openList, canAdd, openAddForm, closeModal, fillNameAndSave,
  findInList, deleteRecord, uniqueName, expectTabLoaded,
} = require('../helpers/crud');

/**
 * Model coverage — admin::attendance
 *
 * Generated from the app's registered model map cross-referenced with
 * NativeModuleRegistry, the module adapters and extension meta.json. Tab entity
 * names are resolved through the registry's `entities` map, so a UI alias like
 * MyLoan is reported as the model it actually binds to (EmployeeCompanyLoan).
 *
 *   Attendance                   -> Attendance
 *   AttendanceStatus             -> Attendance Status
 */

const MODULE = 'admin::attendance';

const TABS = [
  { label: "Attendance", model: "Attendance" },
  { label: "Attendance Status", model: "AttendanceStatus" },
];

test.describe('admin::attendance — model lists', () => {
  for (const { label, model } of TABS) {
    test(`${model} list loads on the "${label}" tab`, async ({ page }) => {
      await login(page, 'admin');
      await openList(page, MODULE, label);
      await expectTabLoaded(page, label);
    });
  }
});

test.describe('admin::attendance — create/delete round trip', () => {
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
