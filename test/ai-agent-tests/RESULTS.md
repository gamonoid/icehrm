# IceHRM Open Source — QA Run Results

**Run date:** 2026-08-02
**Build:** open-source `develop`, production Docker stack
**URL:** `http://localhost:5555/app/ui/#admin%3A%3Adashboard`
**Method:** Playwright (headless Chrome) driving the real SPA; console errors,
failed requests and 4xx/5xx responses captured per route. Server-side causes
confirmed against `docker logs icehrm-app` and the application log.

## Summary

| | Count |
|---|---|
| Cases executed | **118** |
| Passed | 109 |
| Warnings (non-blocking) | 6 |
| **Failed** | **3** (all instances of 2 distinct defects) |
| Defects raised | **3** (2 High, 1 Medium) |

All three failures trace to the same root cause: **open-source code calling
IceHRM Pro–only classes**. See `BUGS.md`.

### Accounts used

| Role | Username | Password | Employee |
|---|---|---|---|
| Admin | `admin` | `admin` | 1 — IceHrm Employee |
| Manager | `anthony.flores233` | `QaTest#2026` | 2 — Anthony Flores (4 direct reports) |
| Employee | `thomas.harris601` | `QaTest#2026` | 5 — Thomas Harris |

Manager and employee passwords were set for this run. Demo data was already
present (16 active employees) — no regeneration required.

---

## 1. Route load sweep — 74 cases

Every registered module visited per applicable role; asserts no uncaught console
error, no failed/5xx request, and a non-trivial content region.

| Role | Routes | Pass | Warn | Fail |
|---|---|---|---|---|
| Admin — Manage area | 26 | 25 | 1 | 0 |
| Admin — Personal area | 16 | 14 | 1 | 1 |
| Manager — Personal area | 16 | 14 | 1 | 1 |
| Employee — Personal area | 16 | 14 | 1 | 1 |

**All 26 admin/manage routes render** — Organization Overview, Company Structure,
Employees, Users, Attendance, Overtime, Travel, Loans, Documents, Training,
Projects, Salary, Job Details, Qualifications, Custom Fields, Metadata, Field
Names, Modules, Permissions, Settings, System Status, Audit Log, Company Assets,
Reports, Marketplace, Demo Data Manager.

**Failure (×3 roles):** `extension::company_overview|user` → HTTP 500 → **BUG-001**.

**Warnings (×4):** `advance_reports` emits an Ant Design `rc-collapse children`
deprecation notice. Cosmetic, no functional impact.

## 2. Role & data-scope enforcement — 36 probes

Employee and Manager attempted direct-hash access to all 18 admin-gated routes.

**Result: 0 leaks.** Every probe blocked.

The evidence is unambiguous in the byte counts: the Employee session returns a
constant **1812 chars** (the bare shell) on all 18 admin routes, and the Manager
session returns a constant **1852 chars** on the 15 `["Admin"]`-only routes — but
**2662 / 2453 / 1560 chars** on `admin::employees`, `admin::attendance` and
`admin::documents`, which are the exact three carrying `["Admin","Manager"]` in
the module registry. Access control matches the registry precisely.

| Case | Role | Result |
|---|---|---|
| S9.2 — `admin::users` by direct hash | Employee | **PASS** — no admin content |
| S9.2b — `admin::settings` by direct hash | Employee | **PASS** — no settings content |
| S9.1 — 18 admin routes, menu + direct route | Employee, Manager | **PASS** — 0 of 36 leaked |
| S11.5 — signed-out SPA route | anonymous | **PASS** — redirects to `login.php` |

## 3. Data scope — S1.5

| Role | Employees visible | Expected | Result |
|---|---|---|---|
| Admin | **16** | all 16 active | **PASS** |
| Manager | **4** | 4 direct reports | **PASS** |

The Manager's four rows are exactly Thomas Harris, Margaret Nguyen, Linda Williams
and Elizabeth Perez — matching `SELECT … WHERE supervisor=2` in the database.
Evidence: `screenshots/S1.5-admin-employees.png`, `S1.5-manager-employees.png`.

## 4. Authentication — S11

| Case | Result |
|---|---|
| S11.1 — sign-in for Admin / Manager / Employee | **PASS** — all three land in the SPA |
| S11.5 — signed-out route redirects to login | **PASS** |

Note: the `Users` table carries `wrong_password_count` and `last_wrong_attempt_at`
columns, so failed-login lockout is implemented in this build.

## 5. Text rendering fidelity — S12

| Case | Result |
|---|---|
| S12.2 — Employee Directory shows no raw HTML entities | **PASS** — 2948 chars, clean |
| S12.1 — QA string in a form field | **INCONCLUSIVE** — see below |

S12.1 could not be completed: the harness targeted the first generic
`input[type=text]` on the Emergency Contacts form and read back an empty value.
That is a limitation of the test, not evidence of a defect — **not** raised as a
bug. It needs a re-run against a specifically selected field.

## 6. Source scan — Pro-only dependencies

A repository scan found **16 references to Pro-only namespaces in 8
open-source files**. Two are confirmed live failures (BUG-001, BUG-002); the rest
are latent. Full table in `BUGS.md` → BUG-003.

---

## Coverage gaps — not executed this run

Stated plainly rather than implied as passing:

- **CRUD suites** (S1.6, S2.1, S3.1, S7.x) — create/edit/delete round-trips not executed.
- **Workflow suites** (S2.W1–W3 timesheet & overtime approval, S3.W1 travel approval)
  — require multi-session orchestration; not executed.
- **File suites** (S10.1–S10.5) — upload, download, oversize and disallowed-extension
  rejection not executed.
- **S12.1** — inconclusive, see above.
- **S8.6 Marketplace catalogue browse** — page loads, but outbound catalogue fetch
  not exercised.
- Email verification — the OS build has no Leave/Expense/Recruitment workflows, and
  remaining email paths were not triggered.

The executed portion covers every route for every role, full admin-route scope
enforcement, and employee-list data scope — which is where the defects surfaced.
