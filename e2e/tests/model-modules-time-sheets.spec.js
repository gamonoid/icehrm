const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');
const { openList } = require('../helpers/crud');

/**
 * Model coverage — modules::time_sheets
 *
 * Generated from the app's registered model map cross-referenced with
 * NativeModuleRegistry, the module adapters and extension meta.json. Tab entity
 * names are resolved through the registry's `entities` map, so a UI alias like
 * MyLoan is reported as the model it actually binds to (EmployeeCompanyLoan).
 *
 *   EmployeeTimeEntry            -> reached through another tab's form
 *   EmployeeTimeSheet            -> reached through another tab's form
 */

const MODULE = 'modules::time_sheets';

const MODELS = ["EmployeeTimeEntry", "EmployeeTimeSheet"];

test.describe('modules::time_sheets — module surface', () => {
  // This module renders its own React view rather than the registry's card
  // lists, so it declares no tabs to derive per-model tests from. Until each
  // model gets a tab-level spec, assert the module mounts and its data surface
  // resolves, so a regression here still fails loudly.
  test('mounts and renders a data surface', async ({ page }) => {
    await login(page, 'admin');
    await openList(page, MODULE, null);
    const rendered = (await page.locator('.ant-card, .ant-table, .ant-empty, .ant-tabs, .ant-list').count()) > 0;
    expect(rendered, `${MODULE}: expected a data surface`).toBeTruthy();
  });
});
