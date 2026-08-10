const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// extension::expense-insights|admin — a native analytics dashboard (modeled on
// Workforce Insights) that visualises employee expense data: headline stat
// cards plus category / status / payment-method / monthly-trend / top-spender
// charts. Reads straight from the Expenses extension tables.
test.describe('extension::expense-insights|admin', () => {
  test('mounts natively with stat cards and charts', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1200 });
    await login(page, 'admin');
    await openModule(page, 'extension::expense-insights|admin');
    await page.waitForTimeout(4000);

    // Native mount — no legacy iframe.
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);

    // Headline stat cards.
    await expect(page.getByText('Total Requests', { exact: true })).toBeVisible();
    await expect(page.getByText('Total Amount', { exact: true })).toBeVisible();
    await expect(page.getByText('Approved Amount', { exact: true })).toBeVisible();
    await expect(page.getByText('Pending Requests', { exact: true })).toBeVisible();

    // Chart section titles.
    await expect(page.getByText('Spend by Category')).toBeVisible();
    await expect(page.getByText('Requests by Status')).toBeVisible();
    await expect(page.getByText('Monthly Spend Trend')).toBeVisible();
    await expect(page.getByText('Spend by Payment Method')).toBeVisible();
    await expect(page.getByText('Top Spenders')).toBeVisible();

    // g2plot renders into <canvas> elements — at least one chart drew.
    await expect(page.locator('canvas').first()).toBeVisible();
  });

  test('period selector reloads data', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1200 });
    await login(page, 'admin');
    await openModule(page, 'extension::expense-insights|admin');
    await page.waitForTimeout(4000);

    // Switch period to "Last 3 Months" and confirm the dashboard stays mounted.
    // Target the period select by its current displayed value.
    await page.locator('.ant-select-selection-item', { hasText: 'Last 1 Year' }).click();
    await page.locator('.ant-select-item-option', { hasText: 'Last 3 Months' }).click();
    await page.waitForTimeout(2000);
    await expect(page.getByText('Total Requests', { exact: true })).toBeVisible();
    await expect(page.locator('canvas').first()).toBeVisible();
  });
});
