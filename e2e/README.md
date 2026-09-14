# End-to-end tests

Playwright specs that drive the real SPA in a browser: log in through the legacy
form, then exercise the app at `/app/ui/`. 80 spec files — 34 hand-written feature
specs, and 46 generated per-module suites that give every registered model class a
named test (see [MODEL_COVERAGE.md](MODEL_COVERAGE.md)).

Also here: `smoke/`, a few shell scripts that drive `service.php` directly for
approval flows that are quicker to assert over HTTP than through the UI.

## Before you run

The app must already be up at the URL in `e2e/.env`:

```
E2E_BASE_URL=http://localhost:9080/app/ui/
```

Everything else is derived from it — the Playwright `baseURL`, and the app base the
smoke scripts post to. Point it at another environment to run the same suites
there. Logins are `admin` / `manager` / `user1`, password `Admin123$`.

> **The generated `model-*.spec.js` suites write to the database they run against.**
> They create records, verify them, then delete them, and every record is named
> `<Model>-e2e-<stamp>` so anything left behind is identifiable. A run that dies
> part-way can still leave rows, and that residue causes *false failures* in the
> next run — colliding creates look like broken features. Prefer a disposable
> instance for these; the read-only specs (`all-modules-smoke`, the feature specs)
> are safe anywhere.

## Running

From `e2e/`:

```bash
# one spec file — every test in it
npx playwright test tests/all-modules-smoke.spec.js

# everything
npx playwright test

# just the generated per-model suites
npx playwright test tests/model-*.spec.js

# one test inside a file, matched on its title
npx playwright test tests/all-modules-smoke.spec.js -g "employees"
```

The config runs headless and sequential (`workers: 1`), which is the reliable
default. Override when you want speed or eyes on it:

```bash
npx playwright test tests/model-*.spec.js --workers=3   # ~10 min instead of ~30
npx playwright test tests/admin-training.spec.js --headed
```

`run-e2e.sh` wraps the common modes and passes any other argument straight through
to Playwright:

```bash
./run-e2e.sh                              # everything, visible browser
./run-e2e.sh tests/all-modules-smoke.spec.js
./run-e2e.sh all-modules-smoke            # a bare word filters by filename
./run-e2e.sh --ui                         # spec list, live preview, time travel
./run-e2e.sh --debug                      # step through with the Inspector
SLOWMO=300 ./run-e2e.sh                   # slow every action, easy to follow
```

## Reading the output

The default line reporter overwrites itself, which is compact but hides per-test
results. For anything you need to read afterwards:

```bash
npx playwright test tests/model-*.spec.js --reporter=list
```

**Watch the exit code, not just the summary.** Piping through `tee` or wrapping the
command means `$?` belongs to the last command in the pipeline, not to Playwright —
a failing run then looks like it passed. Check `PIPESTATUS` or run Playwright last.

On failure Playwright writes to `test-results/<test>/`:

- `test-failed-1.png` — a screenshot at the moment of failure
- `error-context.md` — an accessibility snapshot of the DOM, which is usually the
  fastest way to see what the page actually contained

## Timeouts

`timeout: 90000` per test, `expect: { timeout: 20000 }` per assertion. A tab that
never becomes clickable therefore costs 20s before failing — if a whole spec is
slow, that is usually the shape of it.

## Writing specs

`helpers/app.js` handles login, opening a module by route key (`admin::leaves`),
and asserting a module mounted natively. `helpers/crud.js` handles the card lists:
opening a tab's list, the Add New form, filling a name-only form, finding a record,
and deleting it with the confirm dialog.

Three things those helpers already account for, each of which produced false
failures before being handled:

- **Scope to the active tab.** Inactive tab panes stay in the DOM, so an unscoped
  locator happily resolves to a control in a tab nobody is looking at.
- **A single-tab module hides its tab bar** but still renders the tab node, so
  clicking it unconditionally waits for something that never becomes clickable.
- **Lists page server-side.** A newly created record is often not on page 1 — check
  the visible page, then fall back to the list's search box.
