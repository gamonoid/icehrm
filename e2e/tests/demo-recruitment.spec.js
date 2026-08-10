const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// Demo Data Manager: Job Positions and Candidates generators, shown only when
// the recruitment extensions are installed (stats.modules.jobposition /
// .candidate class_exists guards keep the page working without them).
// Jobs draw on the recruitment-setup master data; candidates apply to the
// generated demo jobs. The test generates and then deletes, leaving no data.
test.describe('extension::demo-mode|admin — recruitment demo data', () => {
  test('generates and deletes job positions and candidates', async ({ page }) => {
    test.setTimeout(120000);
    await page.setViewportSize({ width: 1500, height: 1200 });
    await login(page, 'admin');
    await openModule(page, 'extension::demo-mode|admin');
    await page.waitForTimeout(3000);

    // Both recruitment cards are shown (extensions installed in this env).
    await expect(page.getByText('Job Positions', { exact: true }).first()).toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Candidates', { exact: true }).first()).toBeVisible();

    // Generate: jobs first, then candidates applying to them.
    await page.getByRole('button', { name: /Generate Job Positions/i }).click();
    await expect(page.getByText(/Created \d+ job positions/i)).toBeVisible({ timeout: 30000 });
    await page.waitForTimeout(1500);

    await page.getByRole('button', { name: /Generate Candidates/i }).click();
    await expect(page.getByText(/Created \d+ candidates/i)).toBeVisible({ timeout: 30000 });
    await page.waitForTimeout(1500);

    // Delete: candidates (applications chain first), then job positions.
    await page.getByRole('button', { name: /Delete Candidates \(\d+\)/ }).click();
    await page.locator('.ant-modal-confirm').getByRole('button', { name: 'Delete' }).click();
    await expect(page.getByText(/Deleted \d+ records/i)).toBeVisible({ timeout: 30000 });
    await page.waitForTimeout(2000);

    await page.getByRole('button', { name: /Delete Job Positions \(\d+\)/ }).click();
    await page.locator('.ant-modal-confirm').getByRole('button', { name: 'Delete' }).click();
    await expect(page.getByText(/Deleted \d+ records/i).last()).toBeVisible({ timeout: 30000 });
    await page.waitForTimeout(1500);

    // Everything cleaned up: both delete buttons return to disabled (count 0).
    await expect(page.getByRole('button', { name: /Delete Job Positions \(0\)/ })).toBeDisabled();
    await expect(page.getByRole('button', { name: /Delete Candidates \(0\)/ })).toBeDisabled();
  });
});
