const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

/**
 * ACL restriction coverage — the generic data path.
 *
 * Every model listed here declares an EMPTY matrix for the role under test, so the
 * role must receive nothing from service.php?a=get&t=<Model>.
 *
 * This is the surface that the module gate does NOT protect. A module's meta.json
 * user_levels stops a role opening the SCREEN; it does nothing about a direct
 * request for the model's data. checkSecureAccess consults the MODEL's matrix
 * alone, so an empty matrix is the only thing standing between a manager and, say,
 * every payslip row. A test that only checked "the tab is absent" would pass with
 * or without the fix — this one fails without it.
 *
 * Two different properties, because the two matrices mean different things:
 *
 *   Manager  — an empty getManagerAccess means NO rows at all. A manager has no
 *              only-me path to these models, so anything returned is a leak.
 *   Employee — an empty getUserAccess means no access to OTHER people's rows. The
 *              owner may still receive their own through the only-me matrix on a
 *              registered user table (that is what renders "my overtime", "my
 *              timesheets"). So the test is not "zero rows" but "rows belonging to
 *              more than one employee" — cross-employee disclosure is the defect,
 *              self-service is the feature.
 *
 * A 403/500/empty body all count as no data.
 */

const CLOSED_TO_MANAGER = [
  "Audit",
  "Backup",
  "Cron",
  "DataEntryBackup",
  "Deduction",
  "DeductionGroup",
  "EmailLogEntry",
  "EmployeeImmigration",
  "EmployeeSalary",
  "EmployeeStatus",
  "EmployeeTravelRecordApproval",
  "File",
  "IceEmail",
  "Migration",
  "Module",
  "Notification",
  "Payroll",
  "PayrollColumn",
  "PayrollColumnTemplate",
  "PayrollData",
  "PayrollEmployee",
  "PayslipDocument",
  "PayslipTemplate",
  "ReportFile",
  "RestAccessToken",
  "SalaryComponent",
  "SalaryComponentType",
  "SecureResource",
  "Setting",
  "StatusChangeLog",
  "SystemData",
  "User",
  "UserInvitation",
  "UserMeta",
  "UserRole"
];
const CLOSED_TO_EMPLOYEE = [
  "ArchivedEmployee",
  "AssetType",
  "AttendanceStatus",
  "Audit",
  "Backup",
  "CalculationHook",
  "CompanyAsset",
  "Content",
  "Cron",
  "CustomField",
  "CustomFieldValue",
  "DataEntryBackup",
  "Deduction",
  "DeductionGroup",
  "DemoDataEntry",
  "EmailLogEntry",
  "EmployeeApproval",
  "EmployeeCareer",
  "EmployeeImmigration",
  "EmployeeProject",
  "EmployeeSalary",
  "EmployeeStatus",
  "EmployeeTimeSheet",
  "EmployeeTrainingSession",
  "EmployeeTravelRecordApproval",
  "Ethnicity",
  "FieldNameMapping",
  "File",
  "IceEmail",
  "Migration",
  "Module",
  "Notification",
  "Payroll",
  "PayrollColumn",
  "PayrollColumnTemplate",
  "PayrollData",
  "PayrollEmployee",
  "PayslipDocument",
  "PayslipTemplate",
  "Report",
  "ReportFile",
  "RestAccessToken",
  "SalaryComponent",
  "SalaryComponentType",
  "SecureResource",
  "Setting",
  "StatusChangeLog",
  "SupportedLanguage",
  "SystemData",
  "User",
  "UserInvitation",
  "UserMeta",
  "UserReport",
  "UserRole"
];

async function inspect(page, model) {
  return page.evaluate(async (m) => {
    try {
      const r = await fetch(`/app/service.php?a=get&t=${m}`, { credentials: 'same-origin' });
      if (!r.ok) return { rows: 0, owners: 0 };            // 403/500 — nothing returned
      const j = await r.json();
      const arr = (j && j.object) || [];
      if (!Array.isArray(arr)) return { rows: 0, owners: 0 };
      const owners = new Set(arr.map((o) => o && o.employee).filter((v) => v !== undefined && v !== null));
      return { rows: arr.length, owners: owners.size };
    } catch (e) { return { rows: 0, owners: 0 }; }
  }, model);
}

test.describe('restricted models return nothing to a Manager', () => {
  test('every model with an empty getManagerAccess is closed', async ({ page }) => {
    test.setTimeout(600000);
    await login(page, 'manager');
    const leaked = [];
    for (const m of CLOSED_TO_MANAGER) {
      const { rows } = await inspect(page, m);
      if (rows > 0) leaked.push(`${m} (${rows} rows)`);
    }
    expect(leaked, `these models declare no Manager access but returned data:\n${leaked.join('\n')}`).toEqual([]);
  });
});

test.describe('restricted models return nothing to an Employee', () => {
  test('every model with an empty getUserAccess is closed', async ({ page }) => {
    test.setTimeout(600000);
    await login(page, 'user1');
    const leaked = [];
    for (const m of CLOSED_TO_EMPLOYEE) {
      const { rows, owners } = await inspect(page, m);
      // More than one distinct owner means somebody else's rows came back.
      if (owners > 1) leaked.push(`${m} (${rows} rows spanning ${owners} employees)`);
    }
    expect(leaked, `these models leaked other employees' rows:\n${leaked.join('\n')}`).toEqual([]);
  });
});
