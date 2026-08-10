const fs = require('fs');
const path = require('path');
const { defineConfig } = require('@playwright/test');

// E2E suite for the IceHRM SPA migration. The app URL is defined in e2e/.env
// (E2E_BASE_URL) and shared with the shell smoke tests — do not hardcode it here.
// Tests log in via the legacy form then exercise the SPA (/app/ui/).
// Logins: admin / manager / user1, pw Admin123$.

// Minimal KEY=VALUE reader so the suite needs no dotenv dependency.
function readEnvFile(file) {
  const out = {};
  if (!fs.existsSync(file)) return out;
  for (const line of fs.readFileSync(file, 'utf8').split('\n')) {
    const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
    if (m) out[m[1]] = m[2].trim().replace(/^["']|["']$/g, '');
  }
  return out;
}

const envFile = path.join(__dirname, '.env');

// Print an actionable error and stop, rather than a stack trace nobody can act on.
function envFail(problem) {
  process.stderr.write(
    '\n' +
    '  ──────────────────────────────────────────────────────────────\n' +
    '   e2e configuration missing\n' +
    '  ──────────────────────────────────────────────────────────────\n' +
    `   ${problem}\n\n` +
    '   Every e2e test reads the app URL from e2e/.env, which is\n' +
    '   git-ignored so each machine can point at its own app.\n\n' +
    '   Create it:\n' +
    '       cp e2e/.env.example e2e/.env\n\n' +
    '   For icehrm-pro the URL is:\n' +
    '       E2E_BASE_URL=http://localhost:9380/app/ui/\n\n' +
    `   Expected file: ${envFile}\n` +
    '  ──────────────────────────────────────────────────────────────\n\n'
  );
  process.exit(1);
}

// An exported E2E_BASE_URL wins over the file, so a one-off run elsewhere is
// just: E2E_BASE_URL=https://staging.example.com/app/ui/ npx playwright test
const baseUrl = process.env.E2E_BASE_URL || readEnvFile(envFile).E2E_BASE_URL;
if (!baseUrl) {
  envFail(
    fs.existsSync(envFile)
      ? 'e2e/.env exists but does not set E2E_BASE_URL.'
      : 'No e2e/.env file found.'
  );
}
// Specs navigate with root-relative paths ('/app/login.php', '/app/ui/#…'), so
// Playwright's baseURL must be the origin of the configured URL.
const baseURL = new URL(baseUrl).origin;

module.exports = defineConfig({
  testDir: './tests',
  // Seeds the login accounts and waits for the app before any spec runs.
  globalSetup: require.resolve('./bootstrap/global-setup.js'),
  timeout: 90000,
  expect: { timeout: 20000 },
  retries: 0,
  workers: 1,
  reporter: [['list']],
  use: {
    baseURL,
    headless: true,
    viewport: { width: 1366, height: 900 },
    actionTimeout: 20000,
    ignoreHTTPSErrors: true,
    screenshot: 'only-on-failure',
    // SLOWMO=300 ./run-e2e.sh — delay every action (ms) so headed runs are followable.
    launchOptions: process.env.SLOWMO
      ? { slowMo: parseInt(process.env.SLOWMO, 10) }
      : {},
  },
});
