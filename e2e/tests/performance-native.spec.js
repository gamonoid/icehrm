const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// admin::performance (leave_and_performance PRO) converted to the native SPA:
// Performance Reviews / Feedback Requests / Employee Feedback Templates (both
// admin-only via requiresLevel) / Employee Goals. Adapters + initAdminPerformance
// live in the PRO admin bundle; sub-forms (bulk add, filling forms, in-review
// goal/feedback forms) resolve their modal hosts via getDomContainer.
test.describe('admin::performance — native SPA', () => {
  test('mounts natively with all four tabs for an admin', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::performance');
    await expectNative(page, [
      'Performance Reviews', 'Feedback Requests', 'Employee Feedback Templates', 'Employee Goals',
    ]);

    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
  });

  test('templates tab lists templates; goals tab allows editing', async ({ page }) => {
    test.setTimeout(60000);
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'admin::performance');
    await openTab(page, 'Employee Feedback Templates');
    let active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });

    await openTab(page, 'Employee Goals');
    active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    // Admins (and the employee's manager) edit goals from this tab, but new
    // goals are still created from inside a performance review — no Add New.
    // The cards are edit-only: no view button either.
    await expect(active.getByRole('button', { name: /Add New/i })).toHaveCount(0);
    await expect(active.locator('.ant-card').first().locator('.anticon-eye')).toHaveCount(0);
    // Manager and employee assessments render as progress bars, like legacy.
    await expect(active.locator('.ant-card').first().locator('.ant-progress')).toHaveCount(2);
    await active.locator('.ant-card').first().locator('.anticon-edit').first().click();
    const modal = page.locator('.ant-modal:visible').last();
    await expect(modal.locator('#title')).toBeVisible({ timeout: 15000 });
    await modal.locator('#title').fill('Goal 1');
    await modal.getByRole('button', { name: /Save|OK|Submit/i }).last().click();
    await expect(page.locator('.ant-tabs-tabpane-active').getByText('Goal 1').first()).toBeVisible({ timeout: 15000 });
  });

  test('manager does not see the admin-only tabs', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'manager');
    await openModule(page, 'admin::performance');
    await page.waitForTimeout(2500);
    await expect(page.locator('.ant-layout-content iframe')).toHaveCount(0);
    await expect(page.locator('.ant-tabs-tab', { hasText: 'Performance Reviews' })).toBeVisible({ timeout: 15000 });
    await expect(page.locator('.ant-tabs-tab', { hasText: 'Employee Feedback Templates' })).toHaveCount(0);
    await expect(page.locator('.ant-tabs-tab', { hasText: 'Feedback Requests' })).toHaveCount(0);
  });

  // The review view splits its sections into tabs: Self Feedback
  // (questionnaire only), Reviewer Feedback, Peer Feedback, Goals.
  test('review view has four tabs', async ({ page }) => {
    test.setTimeout(60000);
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'admin::performance');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await active.locator('.ant-card').first().locator('.anticon-eye').first().click();
    const review = page.locator('.ant-modal:visible').last();
    for (const t of ['Self Feedback', 'Reviewer Feedback', 'Peer Feedback', 'Goals']) {
      await expect(review.getByRole('tab', { name: t })).toBeVisible({ timeout: 15000 });
    }
    // Self Feedback tab: questionnaire only.
    await expect(review.getByText('Self Feedback Questionnaire')).toBeVisible();
    // Reviewer Feedback tab.
    await review.getByRole('tab', { name: 'Reviewer Feedback' }).click();
    await expect(review.locator('.ant-tabs-tabpane-active .ant-card-head-title', { hasText: 'Reviewer Feedback' })).toBeVisible();
    // Peer Feedback tab with the request button; submitted feedback cards
    // show the rating (stars + percent).
    await review.getByRole('tab', { name: 'Peer Feedback' }).click();
    await expect(review.getByRole('button', { name: /Request Feedback/i })).toBeVisible();
    const fbCard = review.locator('.ant-tabs-tabpane-active .ant-card', { hasText: 'Feedback from' }).first();
    // .first(): a feedback card renders a rating widget per question as well as the
    // overall one, so the bare locator is ambiguous under strict mode.
    await expect(fbCard.locator('.ant-rate').first()).toBeVisible();
    await expect(fbCard.getByText(/\d+%/).first()).toBeVisible();
  });

  // The in-review goal form has no Status field — goals saved without one
  // default to Private (EmployeeGoal::executePreSaveActions).
  test('in-review goal form has no status field', async ({ page }) => {
    test.setTimeout(60000);
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'admin::performance');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await active.locator('.ant-card').first().locator('.anticon-eye').first().click();
    const review = page.locator('.ant-modal:visible').last();
    await expect(review.getByRole('tab', { name: 'Goals' })).toBeVisible({ timeout: 15000 });
    await review.getByRole('tab', { name: 'Goals' }).click();
    await review.getByRole('button', { name: /Add New Goal/i }).click();
    const goalModal = page.locator('.ant-modal:visible').last();
    await expect(goalModal.locator('#title')).toBeVisible({ timeout: 15000 });
    await expect(goalModal.locator('#title')).toBeVisible();
    await expect(goalModal.getByText('Status')).toHaveCount(0);
    await expect(goalModal.locator('.ant-select')).toHaveCount(0);
  });

  // Editing a feedback template whose questions already have stored answers
  // (CustomFieldValues type PerformanceReview / ReviewFeedback, joined via the
  // review/feedback tables' `form` column) warns before opening the editor —
  // renaming questions would orphan those answers.
  test('template edit warns when stored answers reference it', async ({ page }) => {
    test.setTimeout(60000);
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'admin::performance');
    await openTab(page, 'Employee Feedback Templates');
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });

    // The seeded self-feedback template has stored answers -> notice first.
    const selfCard = active.locator('.ant-card', { hasText: 'Employee Self Feedback' });
    await selfCard.locator('.anticon-edit').click();
    await expect(page.getByText('This template is in use')).toBeVisible({ timeout: 15000 });
    await expect(page.getByText(/self-feedback answer/)).toBeVisible();
    await page.locator('.ant-modal-confirm').getByRole('button', { name: 'Cancel' }).click();
    await expect(page.locator('.ant-modal-confirm')).toHaveCount(0);

    // Edit Anyway proceeds to the real edit form with the record loaded.
    const peerCard = active.locator('.ant-card', { hasText: 'Peer feedback' });
    await peerCard.locator('.anticon-edit').click();
    await expect(page.getByText(/peer-feedback answer/)).toBeVisible({ timeout: 15000 });
    await page.locator('.ant-modal-confirm').getByRole('button', { name: 'Edit Anyway' }).click();
    await expect(page.locator('.ant-modal:visible #name').last()).toHaveValue('Peer feedback', { timeout: 15000 });
  });
});
