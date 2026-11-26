# IceHRM Home (entry point) + Kudos — Build Plan

Status: **draft for review** · Branch of origin: `update/permission-revamp` · Date: 2026-09-19

## 1. Goal

Make a single **Home** module the front door of the application for every user
level, and add a **Kudos** recognition feature that surfaces on it.

Home does three jobs (a dashboard only does the first):

- **Glance** — what is happening and what needs me (action items from every
  module, availability today, birthdays/anniversaries, the team feed).
- **Act** — the top intents one click away (request time off, clock in, give
  kudos) and each action item resolving in place or deep-linking to its screen.
- **Go** — a role-gated launchpad into the modules the user can open, recents, and
  search-forward.

## 2. Decisions locked

- Kudos is a **standalone pro extension** (`extensions-pro/kudos/user`), not a post
  type inside `discussions`. It reuses `discussions`' React components as a starting
  point, not its data model.
- Reactions are a **generic core table** (`target_type` + `target_id`) so Kudos uses
  it first and `discussions` can adopt it later.
- Kudos has a **single recipient** (a plain column, no join table).
- Kudos is **pro** (license-gated like the other `extensions-pro/*|user` modules).
- The existing dashboards are **kept, not retired** — they become apps reachable from
  Home's launchpad. Home only takes over the *landing* role.

## 3. Break-risk review (verified against the code)

Each finding below was checked in the source, not assumed. Findings marked
**(plan change)** altered the plan in section 4.

### 3.1 The landing is already native — and there are already two doors **(plan change)**

- `web/shell/src/AppShell.jsx:446-453` special-cases two *shell components* as the
  landing: `admin::dashboard` → `Dashboard.jsx` and `modules::dashboard` →
  `EmployeeDashboard.jsx`. They are not modules mounted through `NativeModuleHost`.
- `AppShellRestEndPoint::getHomeLink()` (`:877-895`): landing = the user's
  `default_module` if set, else `admin/dashboard` for Admin, else
  `modules/dashboard` for everyone else. The shell's `defaultKey()`
  (`AppShell.jsx:285-291`) mirrors this with fallbacks `admin::dashboard`,
  `modules::dashboard`.
- Modules rows: `6 dashboard/admin` ("Organization Overview", levels
  `["Admin","Other"]`) and `28 dashboard/user` ("Dashboard", under *About You*).
- Both shell dashboards fetch their own endpoints (`appshell/dashboard`,
  `appshell/employee-dashboard`) and contain **no async/await** (the shell bundle
  has no regenerator runtime — one `async` blanks the whole SPA).

Consequence: Home must be built as a **native module** (own bundle, async allowed,
extensions can contribute widgets), and the shell's landing resolution must be
pointed at it. The two existing dashboards stay as-is and become launchpad apps.
`EmployeeDashboard` (row 28) becomes functionally redundant once Home ships; keep it
initially, revisit after.

### 3.2 Restricted users must always have a `default_module` **(plan change)**

- `core/src/Users/Common/Model/User.php:87-100`: saving a Restricted-level user
  without `default_module` fails ("Restricted users must always have a default
  module"), and the chosen module must pass `isModuleEnabledForUser`.
- `ModuleAccessService::userMayAccessModuleLevels()`: a Restricted user may only
  access a module if (a) they hold at least one role, (b) the module lists their
  *base* level, and (c) one of their roles is on the module's allowed roles.

Consequence: Home cannot be assumed reachable by restricted users. **Keep
`default_module` semantics and validation exactly as they are.** Home becomes the
landing only for users with **no** `default_module`; a set `default_module` still
wins. Restricted users therefore keep landing where they do today. Admins can
optionally add a role to the Home module (via the User Roles dialog) to let a
restricted role use Home. The earlier idea of turning `default_module` into a mere
"escape hatch" is dropped.

### 3.3 The orphaned `kudos` module row reconciles cleanly, but `install()` will not run

- Modules row `68 kudos/user` exists with `update_path: extension>kudos|user` and no
  code. `ExtensionManager.php:208-236`: when the directory appears and a DB row
  already exists, the row is **reused** — `status`, `user_levels`, `user_roles`,
  `user_roles_blacklist` are read from the DB row (label/icon/menu updates are
  commented out). `needToInstall` is set only in the new-row branch (`:236`), so
  `$manager->install()` (`:88`, `:118`) is **skipped** for this extension.
- Migrations are **not** gated by `install()`: `MigrationRunner::runExtensionMigrations()`
  (`:92-99`) runs every registered extension migration via
  `getExtensionMigrations()` / `ensureExtensionMigrations()`, and `runAll()` is
  invoked on every login (`core/login.php:213`) and from `SettingsInitialize.php:36`.

Consequence: build `extensions-pro/kudos/user` and the tables are created on the next
login — no manual DB work, no duplicate row. Two rules: **put any first-time seeding
in the migration, not in `install()`** (it will never run for row 68); and after
building, verify `SHOW TABLES` — DbDelta marks a failed `CREATE` as `Up` silently.

### 3.4 Deep-link surface `openIntent` must preserve (additive only)

- Hash keys are `group::name` (`KEY_SEP` at `AppShell.jsx:104`) resolved through
  `itemMap`; extensions use `extension::<name>|<part>` (`LicenseBlocked.jsx:17`);
  `key === 'home'` sets hash `''` (`AppShell.jsx:478`, `:602`).
- The start-tab hint `window.__iceShellStartTab` is set by `Dashboard.jsx:462,552`
  and `Notifications.jsx:95`, and consumed once by `NativeModuleHost.jsx:189-193`.
- `Notifications.jsx:97` parses the current hash.

Consequence: `openIntent` is a wrapper over `openModule(key)` + `__iceShellStartTab`,
adding only new optional `id` / `action` parameters. Every existing link form keeps
working unchanged.

### 3.5 Things that are safe as-is

- **Reactions**: no `Reaction` class or `%eaction%` table exists — no collision.
- **Notifications**: `NotificationManager::addNotification()` /
  `addNotificationToAll()` exist for Kudos to use.
- **Tests**: nothing under `test/` references the dashboard landing.
- **Permissions**: the module-role grant model (`BaseModel::getRoleBasedAccess`,
  `isGrantedByAdminModuleRole`) is what gates every new widget, action item and
  endpoint; all new models declare `getModuleAccess()` (the 103/103 audit script
  re-verifies this).

## 4. Architecture

### 4.1 Home is a native module the shell routes to

- New core module `home` in the **user** group with `user_levels`
  `["Admin","Manager","Employee"]` (same placement family as row 28), registered in
  `NativeModuleRegistry` and rendered as a `NativeExtensionView` custom React view —
  the pattern proven by the User Roles rebuild. Own bundle, async allowed.
- Shell changes (both additive):
  - `getHomeLink()`: `default_module` if set (unchanged); otherwise `modules/home`
    for **every** level (admins included).
  - `defaultKey()` candidates become `[homeLink, 'modules::home', 'admin::dashboard',
    'modules::dashboard']` — old fallbacks retained.
  - `Dashboard.jsx` / `EmployeeDashboard.jsx` special-casing left untouched.

### 4.2 Widget registry

`registerWidget({ id, requires: { level | permission | module | license | setting },
dataKey, component, size })`. A responsive grid mounts only widgets whose `requires`
pass. Modules/extensions contribute widgets; Home hard-codes nothing about them.

### 4.3 One-call aggregation

`home/init` returns every lightweight widget payload in one request (pattern:
`dashboard`'s `getInitData` and the `appshell/*` endpoints). Heavy widgets (feed,
insights) lazy-load after first paint. Cross-module reads are guarded with
`class_exists(...)` and run through `BaseService::getDB()->Execute()`.

### 4.4 `openIntent` deep-link contract (shell, promise chains only)

`window.iceShell.openIntent({ module, tab, id, action, params })` → navigates
(`openModule`), sets `__iceShellStartTab`, and passes `id`/`action` to the mounted
module (e.g. open a record, open the leave-request modal, open the Kudos composer
prefilled). Backward compatible with every existing hash form (3.4).

### 4.5 Cross-module action-item registry (server)

A module/extension implements `actionItemsFor($user)` →
`[{ key, label, count, due, intent }]` (pending approvals, timesheet due, onboarding
tasks, expenses to review). One endpoint aggregates them; each item is
permission-gated by the module-role model so admins get company-wide counts and
employees only their own.

## 5. Workstreams

Sizes are relative (S/M/L).

### A — Shell infrastructure (P0)

- **A1** `openIntent` contract (4.4). Shell bundle: no async/await. — M
- **A2** Action-item registry + aggregation endpoint (4.5). — M
- **A3** Landing: `getHomeLink()` / `defaultKey()` per 4.1. `default_module`
  semantics and validation **unchanged** (3.2). — S

### B — Home module (P1)

- **B1** Scaffold `home` (4.1) + Modules row (all non-restricted levels). — S
- **B2** Widget registry (4.2). — M
- **B3** `home/init` aggregation (4.3). — M
- **B4** Core widgets: greeting + quick actions (Request time off · Clock in · Give
  Kudos); *Needs you* (A2); availability today (Leave + Attendance); birthdays,
  anniversaries, new hires with **Send wishes** → Kudos compose; company events /
  holidays by site; launchpad (role-gated modules incl. *Organization Overview* and
  *Dashboard* as apps, plus recents); admin-only headcount / pending-approval
  summary. — L
- **B5** Reposition (not retire) the dashboards: register rows 6 and 28 as launchpad
  apps; optional compact "Dashboard summary" widget deep-linking into them. — S
- **B6** Docs: new *Home* page in `icehrm-docs` (1440×900 screenshots). — S

### D — Generic Reactions (P2, core; before C)

- Table `Reactions`: `target_type`, `target_id`, `employee`, `emoji`, `created`;
  unique `(target_type, target_id, employee, emoji)`; indexes inline (DbDelta).
- `Reaction` model with `getModuleAccess()` + ACLs (write own reactions only).
- REST: `POST reactions/toggle`, `GET reactions/(:type)/(:num)` (counts + mine).
- Shared `ReactionBar` component in `web/components`. — M

### C — Kudos pro extension (P2)

- **C1** Scaffold `extensions-pro/kudos/user` (fulfils row 68 — see 3.3). Model
  `Kudos`: `sender`, `recipient`, `message`, `value_tag`, `audience`
  (all/department/team), `image`, `status`, `created`;
  `getModuleAccess() → ModuleAccess('kudos','user')`; employees create/list; delete =
  author or admin. Seeding (if any) lives in the migration. — S
- **C2** Endpoints: `POST kudos/post`, `GET kudos/feed/(:num)` (paginated,
  audience-scoped), `GET kudos/received/(:num)`, `GET kudos/given/(:num)`,
  `DELETE kudos/(:num)`; reactions via D. — M
- **C3** Frontend: Kudos screen (feed + composer) from `discussions`' `PostCard` /
  `PostComposer` / `BlockRenderer`; `KudosComposer` (single-recipient picker, message,
  value tag, audience, optional image, preview); `KudosCard` (sender → recipient,
  message, celebratory graphic, `ReactionBar`). — L
- **C4** Notify the recipient via `NotificationManager`; optional email. — S
- **C5** Home integration: Kudos feed widget (registered via B2); **Give Kudos** and
  **Send wishes** → `openIntent({ module:'kudos', action:'compose', params:{recipient} })`. — S
- **C6** Company values list (admin-managed) feeding `value_tag`. — S
- **C7** Pro gating + *Kudos* docs page. — S

### E — Engagement feed (P2)

One Home feed surface merging recent `discussions` posts and Kudos, read-only, each
item deep-linking. — S (after B2 + C)

### F — Polish (P3)

Pinnable / configurable layout saved per user · Kudos "most appreciated" leaderboard
and analytics widgets (Insights) · mobile / responsive pass · empty and onboarding
states.

## 6. Sequencing

```
A1 openIntent ──► A2 action-items ──► B4 Needs-you
       │
       ├──► B1 scaffold ──► B2 registry ──► B3 aggregation ──► B4 widgets ──► B5 reposition
       │                        │
D reactions ──► C Kudos (C1→C4) ─┴──► C5 / E Home widgets ──► F polish
```

Two parallel tracks — **Track 1** A → B (Home), **Track 2** D → C (Kudos) — joining at
C5/E. Kudos has no dependency on P0/P1; only its Home widget waits on B2.

Suggested first PRs: (1) **A1 `openIntent`**, and in parallel (2) **D Reactions +
C1/C2 Kudos scaffold**.

## 7. Cross-cutting rules

- PHP 7.3 compatible. Every new model declares `getModuleAccess()`.
- New endpoints gated by the module-role model + `checkSecureAccess`; run the security
  integration suite on each.
- Builds: `gulp shell-js` (A1 — no async), `gulp modules-js` / `admin-js` (Home),
  `gulp ejs --xkudos/user` (Kudos). Never commit `dist/` except release `--eprod`.
- DbDelta: inline indexes, comma-free defaults, verify `SHOW TABLES` after each
  migration.
- Docs updated per phase; screenshots at 1440×900.

## 8. Definition of done

- **P0** — any widget can route to any module/tab/record/action through `openIntent`;
  the action-item endpoint returns correctly scoped items for an admin and an
  employee; every pre-existing hash link still resolves.
- **P1** — admins and employees without a `default_module` land on Home; users *with*
  one land exactly where they do today (restricted users included); each sees only
  the widgets their role allows; the page loads in one aggregation call; rows 6 and
  28 remain openable as apps.
- **P2** — an employee gives Kudos to a colleague, the recipient is notified, others
  react; Kudos appears on Home; row 68 shows its tables created.
- **P3** — users can pin/arrange widgets; leaderboard live.

## 9. Verify at build time

- After C1: `SELECT status FROM Migrations WHERE file LIKE '%kudos%'` and `SHOW TABLES`
  (3.3 — silent DbDelta failure).
- After A3: log in as `admin`, `manager`, `user1` (no `default_module`) → Home; a
  restricted test user with a `default_module` → unchanged landing.
- After B4: run the `getModuleAccess()` audit script → still 100%.
