const { test, expect } = require('@playwright/test');
const { login, openModule, expectNative } = require('../helpers/app');

// extension::candidates|admin converted to native via the meta.json native
// block: seven hiring-stage tabs (all the CandidateAdapter with per-stage
// filters, wired by the existing initAdminCandidates). The eye action opens
// the rich CandidateProfile through getTableChildComponents — the same
// mechanism the employees module uses.
test.describe('extension::candidates|admin — native SPA', () => {
  test('mounts natively with all hiring-stage tabs and candidate cards', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'extension::candidates|admin');
    await expectNative(page, [
      'Board', 'All Candidates', 'New', 'Short Listed', 'Interview', 'Offer', 'Hired', 'Rejected',
    ]);

    // The Board is the first (default) tab.
    await expect(page.locator('.ant-tabs-tab-active')).toHaveText('Board');
    await expect(page.locator('[data-board-col="New"]')).toBeVisible({ timeout: 15000 });

    // The list tabs still carry the card view with Add New.
    await page.locator('.ant-tabs-tab', { hasText: 'All Candidates' }).click();
    await page.waitForTimeout(1500);
    const active = page.locator('.ant-tabs-tabpane-active');
    await expect(active.locator('.ant-card').first()).toBeVisible({ timeout: 15000 });
    await expect(active.getByRole('button', { name: /Add New/i }).first()).toBeVisible();
  });

  test('the eye opens the rich candidate profile', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'extension::candidates|admin');
    await page.waitForTimeout(2500);
    await page.locator('.ant-tabs-tab', { hasText: 'All Candidates' }).click();
    await page.waitForTimeout(1500);

    await page.locator('.anticon-eye').first().click();
    // CandidateProfile renders with its profile card and detail tabs.
    await expect(page.getByText('Candidate Profile')).toBeVisible({ timeout: 20000 });
    await expect(page.getByText('Interviews', { exact: true })).toBeVisible();
    await expect(page.getByText('Candidate Feedback')).toBeVisible();

    // The hiring stage is an inline select — changeable without the edit dialog.
    const stageItem = page.locator('.ant-descriptions-item', { hasText: 'Hiring Stage' });
    await expect(stageItem.locator('.ant-select')).toBeVisible({ timeout: 10000 });
  });

  test('add/edit uses the guided candidate wizard', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'extension::candidates|admin');
    await page.waitForTimeout(2500);
    await page.locator('.ant-tabs-tab', { hasText: 'All Candidates' }).click();
    await page.waitForTimeout(1500);

    // Add New: wizard chrome — icon steps, heading + blurb, Next flow.
    await page.getByRole('button', { name: /Add New/i }).first().click();
    const modal = page.locator('.ant-modal:visible');
    await expect(modal.getByText('New Candidate')).toBeVisible({ timeout: 10000 });
    await expect(modal.getByText('Who is the candidate?')).toBeVisible();
    await expect(modal.getByText('Contact', { exact: true })).toBeVisible(); // step rail

    // Steps are clickable; the last step shows the create action.
    await modal.locator('.ant-steps-item', { hasText: 'Education' }).click();
    await expect(modal.getByRole('button', { name: /Create candidate/ })).toBeVisible();
    await page.keyboard.press('Escape');
    await page.waitForTimeout(800);

    // Edit: same wizard with edit title, prefilled from the record.
    await page.locator('.anticon-edit').first().click();
    const editModal = page.locator('.ant-modal:visible');
    await expect(editModal.getByText('Edit Candidate')).toBeVisible({ timeout: 15000 });
    const firstName = await editModal.locator('#first_name').inputValue();
    expect(firstName.length).toBeGreaterThan(0);
    await editModal.locator('.ant-steps-item', { hasText: 'Education' }).click();
    await expect(editModal.getByRole('button', { name: /Save changes/ })).toBeVisible();
  });

  test('Board tab shows the kanban and drag-drop moves a candidate between stages', async ({ page }) => {
    test.setTimeout(90000);
    await page.setViewportSize({ width: 1600, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'extension::candidates|admin');
    await page.waitForTimeout(2000);
    await page.locator('.ant-tabs-tab', { hasText: /^Board$/ }).click();
    await page.waitForTimeout(3000);

    // One column per stage tab.
    for (const col of ['New', 'Short Listed', 'Interview', 'Offer', 'Hired', 'Rejected']) {
      await expect(page.locator(`[data-board-col="${col}"]`)).toBeVisible();
    }

    // Columns stretch to equal, viewport-filling height so a card can be
    // dropped in the empty area below a short column's cards.
    const heights = await page.evaluate(() => Array.from(document.querySelectorAll('[data-board-col]'))
      .map((el) => el.getBoundingClientRect().height));
    expect(Math.min(...heights)).toBeGreaterThan(600);
    expect(Math.max(...heights) - Math.min(...heights)).toBeLessThan(2);

    // Column visibility checkboxes above the board (self-restoring toggle);
    // the preference is persisted in localStorage.
    const toggles = page.locator('[data-board-col-toggles] .ant-checkbox-wrapper');
    await expect(toggles).toHaveCount(6);
    await toggles.filter({ hasText: 'Rejected' }).click();
    await expect(page.locator('[data-board-col]')).toHaveCount(5);
    await expect(page.locator('[data-board-col="Rejected"]')).toHaveCount(0);
    expect(await page.evaluate(() => window.localStorage.getItem('candidateBoardHiddenCols')))
      .toBe(JSON.stringify(['Rejected']));
    await toggles.filter({ hasText: 'Rejected' }).click();
    await expect(page.locator('[data-board-col]')).toHaveCount(6);
    expect(await page.evaluate(() => window.localStorage.getItem('candidateBoardHiddenCols')))
      .toBe(JSON.stringify([]));

    // Drag the first New card to Rejected (synthesized HTML5 DnD)…
    const drag = (from, to) => page.evaluate(([f, t]) => {
      const fromCol = document.querySelector(`[data-board-col="${f}"]`);
      const toCol = document.querySelector(`[data-board-col="${t}"]`);
      const card = fromCol && fromCol.querySelector('[data-cand-id]');
      if (!card || !toCol) return null;
      const dt = new DataTransfer();
      card.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer: dt }));
      toCol.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer: dt }));
      toCol.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: dt }));
      return card.getAttribute('data-cand-id');
    }, [from, to]);

    // Rejected groups two stages (Offer Rejected, Not Qualified) — dropping
    // must assign the FIRST associated stage of the column.
    const movedId = await drag('New', 'Rejected');
    expect(movedId).toBeTruthy();
    await expect(page.getByText(/Moved to Offer Rejected/i)).toBeVisible({ timeout: 15000 });
    await page.waitForTimeout(3000);
    await expect(page.locator(`[data-board-col="Rejected"] [data-cand-id="${movedId}"]`)).toBeVisible();

    // …and back to New, leaving the data as it was.
    await page.evaluate(([id]) => {
      const card = document.querySelector(`[data-cand-id="${id}"]`);
      const toCol = document.querySelector('[data-board-col="New"]');
      const dt = new DataTransfer();
      card.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer: dt }));
      toCol.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer: dt }));
      toCol.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: dt }));
    }, [movedId]);
    await expect(page.getByText(/Moved to Applied/i)).toBeVisible({ timeout: 15000 });
    await page.waitForTimeout(2500);
    await expect(page.locator(`[data-board-col="New"] [data-cand-id="${movedId}"]`)).toBeVisible();
  });

  test('Edit works from a board-opened profile (no list tab visited)', async ({ page }) => {
    // Regression: with no list tab mounted there is no shell form container and
    // no legacy #CandidateForm div — the adapter must create a detached one
    // instead of crashing ReactDOM.render (React #200).
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    await page.setViewportSize({ width: 1600, height: 1100 });
    await login(page, 'admin');
    await openModule(page, 'extension::candidates|admin');
    await page.waitForTimeout(2500);

    await page.locator('[data-board-col] [data-cand-id]').first().click();
    const profileModal = page.locator('.ant-modal:visible').last();
    await expect(profileModal.getByText('Candidate Profile')).toBeVisible({ timeout: 15000 });

    await profileModal.getByText('Edit', { exact: true }).click();
    await expect(page.getByText('Edit Candidate')).toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Who is the candidate?')).toBeVisible();
    expect(errors).toEqual([]);
  });
});
