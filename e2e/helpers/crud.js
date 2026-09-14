const { expect } = require('@playwright/test');
const { openModule } = require('./app');

/**
 * Helpers for exercising a model's CRUD surface through the SPA card lists.
 *
 * Every model that has a list view is reached the same way — open the module,
 * open the tab — and the shell renders all of them with NativeCardList, so the
 * affordances are identical: one `.ant-card` per record, an "Add New" button, and
 * per-row edit/delete actions with an antd confirm on delete.
 *
 * These helpers deliberately assert BEHAVIOUR, not layout: that a list resolves
 * without the shell's error state, that the actions the model's ACL grants are the
 * ones actually offered, and — where a form is simple enough to fill blind — that a
 * record can be created and removed again. Anything that needs a model-specific
 * form (dates, dependent dropdowns, file uploads) is left to a hand-written spec
 * rather than guessed at here.
 */

/**
 * The ACTIVE tab pane, or the page when the module has no tabs.
 *
 * Every mounted tab keeps its markup in the DOM — inactive panes are hidden, not
 * removed — so an unscoped locator happily resolves to a control belonging to a
 * tab nobody is looking at. That is not hypothetical: `getByPlaceholder('Search…')`
 * matched a hidden pane's search box and timed out trying to type into it. Scope
 * everything here.
 */
function panel(page) {
  const active = page.locator('.ant-tabs-tabpane-active');
  return { active, root: active };
}

async function scope(page) {
  const active = page.locator('.ant-tabs-tabpane-active');
  return (await active.count()) ? active.first() : page.locator('body');
}

/** The shell's failure states. Any of these means the tab did not work. */
async function expectNoListError(page) {
  await expect(page.getByText(/Could not load this (list|module)/i)).toHaveCount(0);
  await expect(page.getByText(/Something went wrong/i)).toHaveCount(0);
}

/**
 * Open a module tab and assert it renders a working list: either records or a
 * clean empty state, never an error. Returns the number of cards shown.
 */
async function openList(page, moduleKey, tabLabel) {
  await openModule(page, moduleKey);
  if (tabLabel) {
    // Only click a tab that is actually VISIBLE. A single-tab module still renders
    // its .ant-tabs-tab node but hides the tab bar, so an unconditional click waits
    // 20s for an element that will never be clickable — that was every failure in
    // the first full run (custom_fields, fieldnames, modules, dependents, …), and
    // the shared openTab() helper has the same latent issue despite documenting
    // itself as a no-op for single-tab modules.
    const tab = page.locator('.ant-tabs-tab:visible').filter({ hasText: tabLabel }).first();
    if (await tab.count()) {
      await tab.click();
      await page.waitForTimeout(1200);
    }
  }
  await expectNoListError(page);
  const root = await scope(page);
  return root.locator('.ant-card').count();
}

/** Is the "Add New" affordance offered on the current list? */
async function canAdd(page) {
  const root = await scope(page);
  return (await root.getByRole('button', { name: /Add New/i }).count()) > 0;
}

/**
 * Open the add form and return the modal locator, or null when the list offers no
 * Add New (a read-only or derived list — the caller decides whether that is a
 * failure).
 */
async function openAddForm(page) {
  const root = await scope(page);
  const btn = root.getByRole('button', { name: /Add New/i }).first();
  if (!(await btn.count())) return null;
  await btn.click();
  const modal = page.locator('.ant-modal-content:visible').first();
  await expect(modal).toBeVisible({ timeout: 15000 });
  await page.waitForTimeout(600);
  return modal;
}

/** Close whatever modal is open, however it offers to be dismissed. */
async function closeModal(page) {
  const cancel = page.locator('.ant-modal-content:visible .ant-modal-close').first();
  if (await cancel.count()) {
    await cancel.click().catch(() => {});
  }
  await page.keyboard.press('Escape').catch(() => {});
  await page.waitForTimeout(400);
}

/**
 * Fill the first visible text input of an open form with `value`, then save.
 *
 * This is the "simple lookup" shape — the ReactIdNameAdapter models (skills,
 * certifications, countries, leave types …) whose form is a single name field.
 * Returns true when the save appears to have gone through (modal closed).
 */
async function fillNameAndSave(page, value) {
  const modal = page.locator('.ant-modal-content:visible').first();
  const input = modal.locator('input[type="text"]:visible, input:not([type]):visible').first();
  if (!(await input.count())) return false;
  await input.fill(value);

  const save = modal.getByRole('button', { name: /^(Save|Submit|Add|Update|OK)$/i }).first();
  if (!(await save.count())) return false;
  await save.click();
  await page.waitForTimeout(1500);

  // A form that rejected the input leaves the modal open with a validation error.
  return (await page.locator('.ant-modal-content:visible').count()) === 0;
}

/**
 * Does a record containing `value` exist in this list?
 *
 * Searches rather than scanning the visible page. The card list pages server-side,
 * so a freshly created record is very often NOT on page 1 — Nationality has 195
 * rows, and the new one lands on the last page. Asserting against the visible page
 * reports a working create as a failure, which is exactly the false alarm this
 * avoids. The search box drives the same server-side query the list uses.
 */
async function findInList(page, value) {
  const root = await scope(page);
  // Cheapest first: the record may simply be on the page already.
  if ((await root.locator('.ant-card', { hasText: value }).count()) > 0) {
    return true;
  }

  // Otherwise search. The list pages server-side, so a new record is often NOT on
  // page 1 (Nationality has ~195 rows and the new one lands last) — scanning only
  // the visible page reports a working create as a failure. Not every list's
  // search covers the field we filled, hence the visible check first and the
  // restore afterwards.
  const box = root.locator('input[placeholder="Search…"]:visible').first();
  if (!(await box.count())) {
    return false;
  }
  await box.fill(value);
  await box.press('Enter');
  await page.waitForTimeout(2000);
  const found = (await root.locator('.ant-card', { hasText: value }).count()) > 0;
  if (!found) {
    // Leave the list unfiltered for whatever the caller does next.
    await box.fill('');
    await box.press('Enter');
    await page.waitForTimeout(1200);
  }
  return found;
}

/** Back-compat alias: the visible-page check, for callers that want just that. */
async function listContains(page, value) {
  return findInList(page, value);
}

/**
 * Delete the record whose card contains `value`, confirming the antd dialog.
 * Returns true when the card is gone afterwards.
 */
async function deleteRecord(page, value) {
  const root = await scope(page);
  const card = root.locator('.ant-card', { hasText: value }).first();
  if (!(await card.count())) return false;

  // Row actions are icon buttons; the delete one carries a Delete tooltip/aria.
  const del = card.locator('button:has(.anticon-delete), [aria-label*="delete" i]').first();
  if (!(await del.count())) return false;
  await del.click();

  // The confirm is an antd Modal.confirm with okType 'danger' and an okText that
  // follows the entity config ("Delete" by default, but a model may relabel it), so
  // match the danger button rather than the wording.
  const dialog = page.locator('.ant-modal-confirm:visible').first();
  await dialog.waitFor({ state: 'visible', timeout: 10000 }).catch(() => {});
  const ok = dialog.locator('button.ant-btn-dangerous, .ant-modal-confirm-btns button').last();
  if (await ok.count()) {
    await ok.click();
    // Wait for the dialog to go away — the row is only really gone once the
    // confirm has resolved and the list has refetched.
    await dialog.waitFor({ state: 'hidden', timeout: 15000 }).catch(() => {});
  }
  await page.waitForTimeout(1500);
  return (await root.locator('.ant-card', { hasText: value }).count()) === 0;
}

/** A value unique to this run, so a leftover row can never make a test pass. */
function uniqueName(prefix) {
  return `${prefix}-e2e-${Date.now().toString().slice(-6)}`;
}


/**
 * Assert a module tab finished loading.
 *
 * Most tabs are card lists, so "a card or an empty state" is the right signal. Some
 * are NOT: the generated model specs also cover bespoke views (Leave Entitlement is
 * the clearest — it renders entitlement panels with antd typography and buttons, and
 * no .ant-card anywhere). Requiring a card there fails a tab that loaded perfectly.
 *
 * So: accept cards, an empty state, or any bespoke pane that rendered real content —
 * and in every case require that no error state is present. That keeps the intent
 * ("the tab loads") without asserting a DOM shape the tab never had.
 */
async function expectTabLoaded(page, label) {
  const active = page.locator('.ant-tabs-tabpane-active');
  const pane = (await active.count()) ? active.first() : page.locator('.ant-layout-content').first();

  await expect(
    page.getByText(/Could not load this (list|module)/i),
    `"${label || 'tab'}" showed a load error`
  ).toHaveCount(0);

  // Poll rather than snapshot: a tab that is still fetching renders an empty pane for
  // a moment, and a single immediate count made this assertion flaky (it failed once
  // in ~10 runs on a tab whose data was definitely present).
  await expect
    .poll(
      async () => {
        const cards = await page.locator('.ant-card').count();
        const empty = await page.locator('.ant-empty').count();
        const text = ((await pane.innerText().catch(() => '')) || '').trim();
        return cards > 0 || empty > 0 || text.length > 20;
      },
      {
        timeout: 20000,
        message: `"${label || 'tab'}" should render a list, an empty state, or a bespoke view`,
      }
    )
    .toBe(true);
}

module.exports = {
  expectTabLoaded,
  findInList,
  expectNoListError,
  openList,
  canAdd,
  openAddForm,
  closeModal,
  fillNameAndSave,
  listContains,
  deleteRecord,
  uniqueName,
};
