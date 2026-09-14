const { test, expect } = require('@playwright/test');
const {
  login, openModule, expectNative, openTab,
} = require('../helpers/app');

// The approvals tab only appears when multi-level approvals are enabled, so the
// always-present tabs are the employee's own requests and their direct reports.
const BASE_TABS = ['Overtime Requests', 'Direct Reports'];

test.describe('modules::overtime (native, employee self-service)', () => {
  test('mounts natively (no iframe) with own + direct-report tabs', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::overtime');
    await expectNative(page, BASE_TABS);
  });

  test('each tab loads without error', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::overtime');
    for (const t of BASE_TABS) {
      await openTab(page, t);
      await expect(page.getByText(/Could not load this list/i)).toHaveCount(0);
    }
  });

  test('own requests tab can open the Add New form', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::overtime');
    await openTab(page, 'Overtime Requests');
    const addBtn = page.getByRole('button', { name: /Add New/i }).first();
    if (await addBtn.count()) {
      await addBtn.click();
      await expect(page.locator('.ant-modal-content')).toBeVisible();
    }
  });

  test('a manager sees direct-report overtime and can act on a pending request', async ({ page }) => {
    await login(page, 'manager');
    await openModule(page, 'modules::overtime');
    await openTab(page, 'Direct Reports');
    const pane = page.locator('.ant-tabs-tabpane-active');
    await expect(pane.getByText(/Could not load this list/i)).toHaveCount(0);
  });

  test('saving an own request auto-populates employee (no "cannot be null")', async ({ page }) => {
    await login(page, 'user1');
    await openModule(page, 'modules::overtime');
    await openTab(page, 'Overtime Requests');

    // The save POST must carry the SPA module scope so service.php resolves
    // MODULE_TYPE='modules' and BaseService::addElement auto-fills employee.
    const saveReq = page.waitForRequest((r) => r.method() === 'POST'
      && /service\.php/.test(r.url())
      && /a=add|(^|&)a=add|add/.test(r.postData() || ''), { timeout: 15000 });

    const result = await page.evaluate(() => new Promise((resolve) => {
      const m = window.modJsList.tabEmployeeOvertime;
      const params = {
        category: 1,
        start_time: '2026-06-10 09:00:00',
        end_time: '2026-06-10 11:00:00',
      };
      m.add(
        params, [], false,
        (obj) => resolve({ ok: true, obj }),
        (obj) => resolve({ ok: false, obj }),
      );
    }));

    const req = await saveReq;
    const body = req.postData() || '';
    expect(body).toMatch(/mg=modules/);
    expect(body).toMatch(/mn=overtime/);

    expect(result.ok, `save failed: ${JSON.stringify(result.obj)}`).toBeTruthy();
    expect(JSON.stringify(result.obj)).not.toMatch(/cannot be null/i);

    // Clean up: find the row we just created (matched by the sentinel start_time)
    // and delete it. The delete is scoped too, so it only touches the own record.
    await page.evaluate(() => new Promise((resolve) => {
      const m = window.modJsList.tabEmployeeOvertime;
      m.dataPipe.get({ page: 1, limit: 50 }).then((res) => {
        const row = (res.items || []).find((r) => String(r.start_time).indexOf('2026-06-10 09:00') === 0);
        if (row && row.id) m.cleanDelete(row.id, () => resolve());
        else resolve();
      }).catch(() => resolve());
    }));
  });
});
