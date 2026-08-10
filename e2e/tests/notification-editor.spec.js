const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

// A "you were assigned a task list" notification carries a legacy editor link
// (g=extension&n=editor|user&...&hash=…). In the SPA the editor is a document
// viewer, not a route, so clicking must open it in the native document modal —
// not navigate to the (broken) #extension::editor|user route.
test.describe('task notification opens the native editor', () => {
  test('clicking a task-list notification mounts the editor in a modal', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/app/ui/#modules::dashboard', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(2000);

    await page.locator('button[aria-label="Notifications"]').click();
    await page.waitForTimeout(1500);

    const notif = page.getByText(/assigned the task list/i).first();
    if (!(await notif.count())) test.skip(true, 'no task-list notification seeded');

    const hashBefore = await page.evaluate(() => window.location.hash);
    await notif.click();
    await page.waitForTimeout(7000);

    // The editor mounted in a modal — not a navigation to the broken editor route.
    await expect(page.locator('.ant-modal-content')).toBeVisible();
    const after = await page.evaluate(() => ({
      hash: window.location.hash,
      blocks: document.querySelectorAll('.ce-block, .cdx-block').length,
      err: /Could not open|View not available/i.test(document.body.innerText),
    }));
    expect(after.hash).toBe(hashBefore); // did NOT route away to editor|user
    expect(after.err).toBeFalsy();
    expect(after.blocks).toBeGreaterThan(0); // editor canvas rendered the document
  });

  // Regression: the editor's initEditorUser borrows the shared window.modJsList
  // while its modal is open. The underlying module's card list must keep rendering
  // its own rows (names, not #id) through open + close — it captures its adapter
  // instance rather than reading the live global on every render.
  test('underlying module list is unaffected by opening/closing the editor', async ({ page }) => {
    const titles = () => Array.from(
      document.querySelectorAll('.ant-tabs-tabpane-active .ant-card'),
    ).slice(0, 4).map((c) => c.innerText.split('\n')[0]);

    await login(page, 'admin');
    await page.goto('/app/ui/#admin::projects', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.ant-layout', { timeout: 30000 });
    await page.waitForTimeout(2500);

    const before = await page.evaluate(titles);
    if (!before.length) test.skip(true, 'no projects to verify against');
    // Names, not the #id fallback.
    expect(before.every((t) => !/^#\d+$/.test(t))).toBeTruthy();

    await page.locator('button[aria-label="Notifications"]').click();
    await page.waitForTimeout(1200);
    const notif = page.getByText(/assigned the task list/i).first();
    if (!(await notif.count())) test.skip(true, 'no task-list notification seeded');
    await notif.click();
    await page.waitForTimeout(6000);
    await page.locator('.ant-modal-close').click();
    await page.waitForTimeout(2500);

    const after = await page.evaluate(titles);
    expect(after).toEqual(before); // unchanged — no #id fallback, no editor data bleed
  });
});
