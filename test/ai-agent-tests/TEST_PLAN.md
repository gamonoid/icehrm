# IceHRM Open Source — QA Test Plan

Functional, role-based and workflow QA plan for the **open-source** IceHRM SPA UI
(`/app/ui/#`). Adapted from the IceHRM Pro test plan, with every Pro-only module
removed (see §3). Results for each run are recorded in `RESULTS.md`; defects in
`BUGS.md`; evidence in `screenshots/`.

**Target build:** open-source `develop`, production Docker stack.

---

## 1. Environment

| Item | Value |
|---|---|
| App URL | `http://localhost:5555/app/ui/#admin%3A%3Adashboard` |
| Login page | `http://localhost:5555/app/login.php` |
| Stack | `docker-compose-prod.yaml` (`icehrm-app`, `icehrm-mysql`, `icehrm-worker`) |
| Demo data | Generate via `http://localhost:5555/app/ui/#extension%3A%3Ademo-mode%7Cadmin` |

### Accounts

| Role | Username | Password | Employee | Notes |
|---|---|---|---|---|
| Admin | `admin` | `admin` | 1 — IceHrm Employee | Ships with the seed data |
| Manager | `anthony.flores233` | `QaTest#2026` | 2 — Anthony Flores | 4 direct reports (emp 5, 8, 11, 14) |
| Employee | `thomas.harris601` | `QaTest#2026` | 5 — Thomas Harris | Reports to Anthony Flores |

Manager and employee passwords were set for QA. Any test user's password may be
reset with a bcrypt hash written directly to `Users.password` — note that a raw
`$2y$…` hash must not be interpolated through a double-quoted shell string, or the
`$` segments are eaten by the shell and a truncated hash is stored.

---

## 2. Scope — what exists in open source

44 modules are registered (27 admin, 17 user). Two are **Disabled** by default and
superseded by the `advance_reports` extension: `admin::reports`, `user::reports`.

**Manage (admin) area** — Organization Overview, Company Structure, Employees,
Users, Attendance, Overtime, Travel, Loans, Documents, Training Sessions,
Projects, Company Assets, Salary, Job Details Setup, Qualifications Setup, Custom
Fields, Manage Metadata, Field Names, Manage Modules, Manage Permissions,
Settings, System Status, Audit Log, Reports (advance_reports), Extensions
(marketplace), Demo Data Manager.

**Personal (self-service) area** — Dashboard, Personal Details, Qualifications,
Dependents, Emergency Contacts, Attendance, Time Sheets, Overtime Requests,
Travel, Loans, My Documents, Training Sessions, Company, Employee Directory,
Reports, Editor.

**Bundled extensions** — `advance_reports`, `company_assets`, `company_overview`,
`directory`, `demo-mode`, `editor`, `marketplace`.

## 3. Excluded — Pro-only, not present in this build

These suites from the Pro plan are **removed**, having no module in open source:

| Pro area | Pro module |
|---|---|
| **Leave** (all of Pro Suite S3) | `extensions-pro/leave_and_performance` |
| Performance / reviews / goals | `extensions-pro/leave_and_performance` |
| Recruitment, candidates, public job pages | `extensions-pro/recruitment` |
| Learning / courses / lessons | `extensions-pro/learn` |
| Expenses + Expense Insights | `extensions-pro/expenses`, `expense-insights` |
| e-Sign / document signing | `extensions-pro/esign` |
| Workforce & Overtime Insights | `extensions-pro/insights`, `overtime-insights` |
| Tasks / Task Lists | `extensions-pro/tasks` |
| Teams | `extensions-pro/team` |
| Payroll runs & payslip PDFs | `extensions-pro/payroll_config` |

Note: open source has `admin::salary` (salary components) but **no payroll run or
payslip generation**, and **no Leave module at all** — leave entitlement,
applications, approvals, calendar and holidays are entirely Pro.

## 4. Objectives

1. Every menu item loads without a JS console error or failed network request, for
   each applicable role.
2. Every tab under each menu renders and shows the correct data.
3. **Data scope is correct per user level** — Admin sees everything; Manager sees
   own data plus direct reports where documented; Employee sees only their own.
4. CRUD works wherever exposed, with validation.
5. Workflows complete end-to-end with correct state transitions.
6. Files attach, store and download correctly for roles that should have them.
7. Stored text renders faithfully — no visible HTML entities, no lost line breaks.
8. Admin-only areas are hidden from and unreachable by lower roles.

## 5. Methodology

- **Load check:** navigate to the route hash, wait for render, assert (a) no
  uncaught console error, (b) no failed/4xx/5xx network request, (c) content
  region non-trivial. Record PASS/FAIL plus the first error.
- **Scope check:** compare visible record counts across admin / manager / employee
  for the same route against the expected rule.
- **CRUD check:** create a record prefixed `QA-`, edit a field, delete it.
- **Rendering check:** use the QA string ``QA & "quoted" <tag> 'single' — café 🎉``
  plus a second line; assert it displays exactly, with the break preserved and no
  `&amp;` / `&lt;` artefacts.
- **Evidence:** screenshot any unexpected result into `screenshots/`.

## 6. Severity

- **Critical** — data loss, security exposure, workflow blocked, install unusable.
- **High** — feature broken for a role, wrong data scope, files unreadable.
- **Medium** — partial breakage with a workaround, validation gap, formatting lost.
- **Low/UX** — cosmetic, slow load, confusing copy.

---

## 7. Test suites

Roles: **A**=admin, **M**=manager, **E**=employee.

### Suite S1 — People / Core HR
| ID | Title | Roles | Expected |
|---|---|---|---|
| S1.1 | Organization Overview loads; KPI tiles + charts render | A,M | Tiles and charts present |
| S1.2 | Org Overview data scope | A vs M | Admin all; Manager team only |
| S1.3 | Company Structure list + Org Chart tab | A | Structure and chart render |
| S1.4 | Employees list + profile tabs | A | All rollup tabs render, no "Unknown model" |
| S1.5 | Employees list scope for Manager | M | Only own team members |
| S1.6 | Add employee → edit → mark resigned → restore | A | Each step persists |
| S1.7 | Employee number uniqueness enforced | A | Duplicate blocked |
| S1.8 | Users / User Invitations / User Roles tabs | A | All three load |
| S1.9 | Personal pages load own data only | E | Dashboard, Personal Details, Qualifications, Dependents, Emergency Contacts |
| S1.10 | Employee Directory + Company are company-wide read-only | E | Both render |
| S1.11 | Employee profile photo renders in header, list and directory | A,E | No broken images |

### Suite S2 — Time & Work
| ID | Title | Roles | Expected |
|---|---|---|---|
| S2.1 | Projects: Projects/Clients/Assignments tabs; add Client→Project | A | CRUD works |
| S2.2 | Attendance (Manage) list + scope | A vs M | Admin all; Manager team only |
| S2.3 | Attendance (Personal) punch in → punch out | E | Record created, hours computed |
| S2.4 | Overtime (Manage) Categories + Requests tabs | A,M | Load; correct scope |
| S2.5 | Time Sheets tabs per role | E,M | Correct tab set; Direct Reports for M |
| S2.6 | Punch note with the QA string displays intact to admin | E→A | Verbatim, no entities |
| **S2.W1** | **Timesheet submit → manager approve** | E→M | Pending → Approved |
| **S2.W2** | **Timesheet reject** | E→M | Rejected state |
| **S2.W3** | **Overtime request → approve** | E→M | Pending → Approved |

### Suite S3 — Travel, Loans & Salary
| ID | Title | Roles | Expected |
|---|---|---|---|
| S3.1 | Travel (Manage) Projects + Requests; add Travel Project | A | CRUD |
| S3.2 | Travel scope A vs M vs E | A,M,E | A all; M team; E own |
| S3.3 | Loans (Manage + Personal) load | A,E | Lists load |
| S3.4 | Salary tabs (Component Types, Components, Salary) | A | Load; add a component type |
| **S3.W1** | **Travel request → manager approve** | E→M | State transitions correctly |

### Suite S4 — Documents
| ID | Title | Roles | Expected |
|---|---|---|---|
| S4.1 | Documents (Manage) tabs load | A | Company Docs, Document Types, Employee Docs |
| S4.2 | My Documents (Personal) | E | Own documents only |
| S4.3 | Company Doc share validation | A | Exactly one of Departments/Employees required |
| S4.4 | Employee Document "Visible To" enforced | A,M,E | Permitted viewer can open; others see no link |
| S4.5 | Employee uploads own document; admin uploads on their behalf | E,A | Both land against the right employee |

### Suite S5 — Training
| ID | Title | Roles | Expected |
|---|---|---|---|
| S5.1 | Training Sessions (Manage) tabs | A | Load |
| S5.2 | Training Sessions (Personal) tabs | E,M | Direct Reports tab for M |
| S5.3 | Create a training session; employee sees it | A,E | Visible to the enrolled employee |

### Suite S6 — Reports
| ID | Title | Roles | Expected |
|---|---|---|---|
| S6.1 | Reports (Manage / advance_reports) catalogue → filter → preview | A | Org-wide data |
| S6.2 | Report CSV download | A | Non-empty, header row, matches preview |
| S6.3 | Reports (Personal) scoped to self | E | Never exposes another employee's rows |

### Suite S7 — Company Setup
| ID | Title | Roles | Expected |
|---|---|---|---|
| S7.1 | Job Details Setup tabs | A | CRUD |
| S7.2 | Qualifications Setup tabs | A | CRUD |
| S7.3 | Custom Fields: add a field to a module | A | Appears on the form, saves, displays on read-only view |
| S7.4 | Company Assets + Asset Types; assign to employee | A | Status → Assigned |
| S7.5 | Manage Metadata, Field Names load | A | Load |
| S7.6 | Company logo upload appears in header and login page | A | Rendered in both |

### Suite S8 — System
| ID | Title | Roles | Expected |
|---|---|---|---|
| S8.1 | Settings load + save a setting | A | Persists |
| S8.2 | Manage Modules list; enable/disable follows into the menu | A | Menu tracks module state |
| S8.3 | Manage Permissions loads | A | Load |
| S8.4 | System Status loads | A | Load |
| S8.5 | Demo Data Manager loads (do NOT wipe) | A | Load |
| S8.6 | Marketplace tabs; catalogue browses | A | Lists without unhandled error |
| S8.7 | Audit Log entries present | A | Auth/add/edit/delete logged |
| S8.8 | Editor extension loads and saves content | A,E | Round-trips |

### Suite S9 — Role & data-scope enforcement (cross-cutting)
| ID | Title | Roles | Expected |
|---|---|---|---|
| S9.1 | Admin-only routes hidden from Manager/Employee menu | M,E | Not offered |
| S9.2 | Admin-only routes unreachable by direct hash | M,E | Refused or redirected, not a broken page |
| S9.3 | Personal areas never show another employee's record | E | Own data only |
| S9.4 | Manager approval limited to direct reports | M | Cannot approve a non-report's request |

### Suite S10 — Files & downloads
| ID | Title | Roles | Expected |
|---|---|---|---|
| S10.1 | Employee uploads their own profile photo | E | Visible immediately |
| S10.2 | Admin sets another employee's profile photo | A | Lands on the right employee |
| S10.3 | Uploaded file types open: pdf, png, jpg, csv, docx | A | Correct content-type, bytes intact |
| S10.4 | Oversized upload rejected with a clear message | A,E | Friendly validation, not a raw error |
| S10.5 | Disallowed extension rejected | A,E | `.php` and similar refused |

### Suite S11 — Authentication & session
| ID | Title | Roles | Expected |
|---|---|---|---|
| S11.1 | Username/password sign-in for each role | A,M,E | Lands on the correct default page |
| S11.2 | Wrong password is refused with a readable message | — | No stack trace |
| S11.3 | Repeated wrong passwords lock the account | E | `wrong_password_count` increments; lockout message |
| S11.4 | Sign out ends the session | E | Back/refresh does not restore |
| S11.5 | Direct SPA route while signed out redirects to login | — | Redirect, not a broken shell |

### Suite S12 — Text rendering fidelity
| ID | Title | Roles | Expected |
|---|---|---|---|
| S12.1 | Employee names with `&`, `'`, accents | A,E | Correct in lists, detail, directory |
| S12.2 | Read-only field view after save matches input | A,E | Verbatim |
| S12.3 | Custom field values (text + textarea) | A,E | Correct on form and read-only view |
| S12.4 | Multi-line notes (punch note, travel note) | E,M | Line breaks preserved |
| S12.5 | Sign-in page messages | — | Readable, no markup artefacts |

## 8. Exit criteria

- All load cases executed for applicable roles with a clean console and network panel.
- All workflow suites (S2.W*, S3.W1) executed.
- S9 (role scope), S10 (files), S11 (auth), S12 (rendering) executed in full.
- Every failure logged in `BUGS.md` with severity and a screenshot.
