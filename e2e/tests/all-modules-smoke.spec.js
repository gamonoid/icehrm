const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/app');

// Production-readiness gate: every menu item, for every role, must open in the
// SPA without error — whether it's natively mounted or iframe-hosted. This walks
// the real menu from the bootstrap so it stays in sync as modules are added.

const ROLES = ['admin', 'manager', 'user1'];

async function getRoutes(page) {
  return page.evaluate(async () => {
    const cfg = JSON.parse(document.getElementById('app-shell-config').textContent);
    const r = await fetch(`${cfg.restApiBase}appshell/bootstrap`, {
      headers: { Authorization: `Bearer ${cfg.token}` },
      credentials: 'same-origin',
    });
    const d = await r.json();
    const routes = new Set();
    ['admin', 'employee'].forEach((vk) => (d.views[vk] || []).forEach(
      (g) => (g.items || []).forEach((i) => routes.add(`${i.g}::${i.n}`)),
    ));
    return Array.from(routes);
  });
}

// Open a route and report whether it rendered cleanly.
async function probe(page, route) {
  await page.goto(`/app/ui/#${route}`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('.ant-layout', { timeout: 30000 });
  await page.waitForTimeout(2600);

  // Hard failures the shell surfaces:
  const errCount = await page.getByText(/Could not load this (module|list)/i).count();
  if (errCount > 0) return { ok: false, why: 'shell error state' };
  if (await page.getByText(/View not available/i).count() > 0) {
    return { ok: false, why: 'native view missing' };
  }

  // Native content OR a loaded iframe must be present in the content area.
  const content = page.locator('.ant-layout-content, .ice-content, main').first();
  const hasIframe = await content.locator('iframe').count() > 0;
  const hasNative = await content.locator([
    '.ant-card', '.ant-tabs', '.ant-table', '.ant-empty', 'canvas', '.ant-descriptions',
    'form', '.ant-list', '.ant-spin', '.ant-statistic', '.ant-row', '.ant-btn',
    '.ant-avatar', '.ant-input', '.ant-select', '.ant-pagination', '.ant-typography',
    '.ant-collapse', '.ant-alert', '.ant-result', 'table',
  ].join(', ')).count() > 0;

  if (hasIframe) {
    // Same-origin: confirm the iframe document is not a PHP fatal / blank.
    const bad = await page.evaluate(() => {
      const f = document.querySelector('.ant-layout-content iframe, main iframe');
      if (!f) return 'no-iframe';
      try {
        const doc = f.contentDocument;
        if (!doc || !doc.body) return null; // still loading is acceptable
        const t = doc.body.innerText || '';
        if (/Fatal error|Parse error|Uncaught Error|Call to (a member|undefined)/i.test(t)) return 'php-fatal';
        return null;
      } catch (e) { return null; }
    });
    if (bad === 'php-fatal') return { ok: false, why: 'iframe PHP fatal' };
    return { ok: true, why: 'iframe' };
  }
  if (hasNative) return { ok: true, why: 'native' };
  return { ok: false, why: 'no content rendered' };
}

for (const role of ROLES) {
  test(`every module opens in the SPA for ${role}`, async ({ page }) => {
    test.setTimeout(600000);
    await login(page, role);
    await page.goto('/app/ui/', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#app-shell-config', { state: 'attached', timeout: 30000 });
    const routes = await getRoutes(page);
    expect(routes.length, `${role} has menu routes`).toBeGreaterThan(0);

    const failures = [];
    for (const route of routes) {
      const res = await probe(page, route);
      if (!res.ok) failures.push(`${route} (${res.why})`);
    }
    // eslint-disable-next-line no-console
    console.log(`[${role}] ${routes.length} modules checked, ${failures.length} failed`);
    expect(failures, `Modules that failed to open for ${role}:\n${failures.join('\n')}`).toEqual([]);
  });
}
