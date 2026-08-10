/**
 * Playwright globalSetup — prepare the environment before any spec runs.
 *
 * 1. Seed the accounts every spec logs in as (admin, manager, user1..user4, all
 *    Admin123$) via bootstrap/seed-users.sh. The testing database is created from
 *    docker/init.sql, which ships schema and settings but almost no people, so
 *    without this every login fails and all 84 specs fail identically.
 * 2. Wait for the app to answer, and fail with an actionable message if it does not
 *    — a suite-wide timeout cascade is much harder to read than one clear error.
 *
 * Seeding is skipped when E2E_SKIP_SEED=1 (e.g. running against an environment you
 * do not control, or one with its own fixtures).
 */

const { execFileSync } = require('child_process');
const path = require('path');

function log(msg) {
  process.stdout.write(`[e2e:setup] ${msg}\n`);
}

async function waitForApp(baseURL, timeoutMs = 60000) {
  const target = `${baseURL}/app/login.php`;
  const deadline = Date.now() + timeoutMs;
  let lastErr = 'no response';
  while (Date.now() < deadline) {
    try {
      const res = await fetch(target, { redirect: 'manual' });
      if (res.status >= 200 && res.status < 400) return true;
      lastErr = `HTTP ${res.status}`;
    } catch (e) {
      lastErr = e.message;
    }
    await new Promise((r) => setTimeout(r, 2000));
  }
  throw new Error(
    `The app at ${target} did not respond within ${timeoutMs / 1000}s (${lastErr}).\n` +
    '  Start the testing stack:  docker compose -f docker-compose-testing.yaml up -d\n' +
    '  and confirm e2e/.env points at it (E2E_BASE_URL).'
  );
}

module.exports = async (config) => {
  const baseURL = config.projects[0].use.baseURL;

  function runSeed(script, what, env) {
    log(`${what}…`);
    try {
      const out = execFileSync('bash', [path.join(__dirname, script)], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'pipe'],
        env: Object.assign({}, process.env, env || {}),
        timeout: 15 * 60 * 1000,
      });
      out.trim().split('\n').filter(Boolean).forEach((l) => log(l));
    } catch (e) {
      const detail = (e.stderr || e.stdout || e.message || '').toString().trim();
      throw new Error(
        `${what} failed.\n${detail}\n\n` +
        '  Is the testing stack up?  docker compose -f docker-compose-testing.yaml up -d\n' +
        '  To run without seeding:   E2E_SKIP_SEED=1 npx playwright test'
      );
    }
  }

  if (process.env.E2E_SKIP_SEED === '1') {
    log('E2E_SKIP_SEED=1 — not seeding.');
  } else {
    // Optional clean slate. The seeding below is only reproducible from a known
    // baseline: the demo generator appends rather than upserts, so repeated runs
    // drift and specs start depending on residue. E2E_FRESH_DB=1 wipes
    // docker/testing/db_data and lets MySQL re-run docker/init.sql.
    if (process.env.E2E_FRESH_DB === '1') {
      runSeed('reset-db.sh', 'resetting the testing database');
    }

    // Licence/marketplace SystemData first — without it every extensions-pro
    // module renders the licence block instead of its content.
    runSeed('seed-systemdata.sh', 'injecting marketplace SystemData');

    // Accounts next: the demo-data step authenticates as admin.
    runSeed('seed-users.sh', 'seeding test accounts');

    // The app must be answering before we can drive its REST API.
    log(`waiting for ${baseURL} …`);
    await waitForApp(baseURL);

    // Then the records the specs assert on ("lists the seeded X"). Generated through
    // the app's own demo-mode extension, and skipped when data is already present.
    runSeed('seed-demo-data.sh', 'seeding demo data', { E2E_APP_BASE: `${baseURL}/app` });

    // Finally the specific named records individual specs assert on
    // ("lists the seeded loan", the sign-up session, leave-type flags …).
    // After demo data, so it can attach rows to the accounts and lookups above.
    runSeed('seed-fixtures.sh', 'seeding named fixtures');
  }

  log(`waiting for ${baseURL} …`);
  await waitForApp(baseURL);
  log('environment ready — starting tests.');
};
