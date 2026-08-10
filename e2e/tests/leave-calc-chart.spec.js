const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// The Leave Entitlement "How is this calculated?" dialog now renders a parsed,
// graphical view (LeaveCalcParser + LeaveCalcChart) instead of the raw log.
// Rooty carries forward across many past periods (propagation chart + table);
// Annual leave does not (current-period derivation chart).
test.describe('modules::leaves — Leave Entitlement calculation chart', () => {
  async function openCalc(page, leaveTypeName) {
    await login(page, 'admin');
    await openModule(page, 'modules::leaves');
    await page.waitForTimeout(1500);
    await page.locator('.ant-tabs-tab', { hasText: 'Leave Entitlement' }).click();
    await page.waitForTimeout(2000);
    // The entitlement panels are plain divs — no .ant-card and no h5 (verified by
    // dumping the pane's DOM). Scope by the panel that contains BOTH the leave type
    // name and its own "How is this calculated?" button, taking the innermost match.
    const calcBtn = page.getByRole('button', { name: /How is this calculated/i });
    const panel = page.locator('.ant-tabs-tabpane-active div')
      .filter({ hasText: leaveTypeName })
      .filter({ has: calcBtn })
      .last();
    await panel.getByRole('button', { name: /How is this calculated/i }).first().click();
    await expect(page.locator('.ant-modal')).toBeVisible({ timeout: 10000 });
  }

  test('carry-forward leave type shows the propagation chart + breakdown table', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1100 });
    await openCalc(page, 'Rooty');
    const modal = page.locator('.ant-modal', { hasText: 'how this is calculated' });

    await expect(modal.getByText('Carry-forward propagation')).toBeVisible();
    // g2plot renders a canvas; the per-period table has the "Carried to next" column.
    await expect(modal.locator('canvas').first()).toBeVisible();
    await expect(modal.getByText('Carried to next').first()).toBeVisible();
    // Summary reflects the parsed model (leave type + current period).
    await expect(modal.getByText('Rooty').first()).toBeVisible();
    // Raw log is preserved but collapsed.
    await expect(modal.getByText('Show calculation log')).toBeVisible();
  });

  test('simple leave type shows the current-period derivation chart', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1100 });
    await openCalc(page, 'Annual leave');
    const modal = page.locator('.ant-modal', { hasText: 'how this is calculated' });

    await expect(modal.getByText('How this period was calculated')).toBeVisible();
    await expect(modal.locator('canvas').first()).toBeVisible();
    // No carry-forward table for a non-carry-forward type.
    await expect(modal.getByText('Carry-forward propagation')).toHaveCount(0);
  });
});
