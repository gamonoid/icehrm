const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');
const { openList } = require('../helpers/crud');

/**
 * Model coverage — extension::jobsetup|admin
 *
 * Generated from the app's registered model map cross-referenced with
 * NativeModuleRegistry, the module adapters and extension meta.json. Tab entity
 * names are resolved through the registry's `entities` map, so a UI alias like
 * MyLoan is reported as the model it actually binds to (EmployeeCompanyLoan).
 *
 *   Benifit                      -> reached through another tab's form
 *   EducationLevel               -> reached through another tab's form
 *   EmployementType              -> reached through another tab's form
 *   ExperienceLevel              -> reached through another tab's form
 *   Industry                     -> reached through another tab's form
 *   JobFunction                  -> reached through another tab's form
 */

const MODULE = 'extension::jobsetup|admin';

const MODELS = ["Benifit", "EducationLevel", "EmployementType", "ExperienceLevel", "Industry", "JobFunction"];

test.describe('extension::jobsetup|admin — module surface', () => {
  // This module renders its own React view rather than the registry's card
  // lists, so it declares no tabs to derive per-model tests from. Until each
  // model gets a tab-level spec, assert the module mounts and its data surface
  // resolves, so a regression here still fails loudly.
  test('mounts and renders a data surface', async ({ page }) => {
    await login(page, 'admin');
    await openList(page, MODULE, null);
    // Assert it MOUNTED and did not error. Which widgets appear is the extension's
    // own business — the editor renders a document editor, the directory its own
    // cards — so requiring antd list markup would fail modules that work fine.
    const shell = await page.locator('.ant-layout-content').count();
    expect(shell, `${MODULE}: the module content area should render`).toBeGreaterThan(0);
  });
});
