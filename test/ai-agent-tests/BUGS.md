# IceHRM Open Source — Defects

Run: 2026-08-02 · build: open-source `develop` · production Docker stack (`localhost:5555`)

| ID | Severity | Title | Status |
|---|---|---|---|
| BUG-001 | **High** | Company page fatals — `company_overview` calls the Pro-only `EmployeeLeave` class | Open |
| BUG-002 | **High** | Personal Dashboard API fatals — `getPendingLeaves` calls Pro-only `LeavesActionManager` | Open |
| BUG-003 | **Medium** | 14 further unguarded references to Pro-only namespaces in open-source code | Open |

---

## BUG-001 — Company page returns HTTP 500 for every role

**Severity:** High — a menu item present for all users is permanently broken.
**Affects:** Admin, Manager, Employee (all three reproduced).
**Route:** `#extension::company_overview|user` ("Company", under Collaboration)
**Evidence:** `screenshots/BUG-001-company-overview-500.png`

### Steps to reproduce
1. Sign in as any user (e.g. `thomas.harris601` / `QaTest#2026`).
2. Navigate to `http://localhost:5555/app/ui/#extension%3A%3Acompany_overview%7Cuser`.

### Expected
The Company page renders its widgets.

### Actual
The page renders an empty shell. `GET /app/api/index.php?...` returns **HTTP 500**.

```
PHP Fatal error: Uncaught Error:
Class "Leaves\Common\Model\EmployeeLeave" not found
  in /var/www/html/extensions/company_overview/user/src/ApiController.php:50
Stack trace:
#0 .../ApiController.php(20): Company_overviewUser\ApiController->getUpcomingLeaves()
#1 [internal function]: Company_overviewUser\ApiController->{closure}()
#2 /var/www/html/core/src/Classes/IceRoute.php(106): call_user_func()
#3 /var/www/html/core/api-url-based.php(37): Classes\IceRoute::dispatch()
```

### Root cause
`extensions/company_overview/user/src/ApiController.php:12` imports
`Leaves\Common\Model\EmployeeLeave` and line 50 instantiates it inside
`getUpcomingLeaves()`. The `Leaves` namespace ships only in IceHRM Pro
(`extensions-pro/leave_and_performance`); `core/src/Leaves` does not exist in this
repository. The extension was ported to open source without removing its leave
dependency.

### Suggested fix
Guard the call, or drop the upcoming-leaves widget from the open-source build:

```php
$leaves = [];
if (class_exists('\\Leaves\\Common\\Model\\EmployeeLeave')) {
    $leave = new EmployeeLeave();
    $leaves = $leave->Find(...);
}
```

`core/src/TimeSheets/User/Api/TimeSheetsActionManager.php` already uses exactly
this pattern (5 `class_exists` guards) and is a good reference.

---

## BUG-002 — Personal Dashboard `getPendingLeaves` returns HTTP 500

**Severity:** High — an unauthenticated-to-authenticated API endpoint fatals; any
consumer of the personal dashboard leave counter breaks.
**Affects:** any signed-in user.
**Endpoint:** `GET /app/service.php?a=ca&mod=modules=dashboard&sa=getPendingLeaves`
**Evidence:** `screenshots/BUG-002-dashboard-getPendingLeaves-500.png`

### Steps to reproduce
1. Sign in as `thomas.harris601` / `QaTest#2026`.
2. From the browser console (or any authenticated client):
   ```js
   fetch('/app/service.php?a=ca&mod=' + encodeURIComponent('modules=dashboard')
         + '&sa=getPendingLeaves&req=' + encodeURIComponent('{}'),
         { credentials: 'same-origin' }).then(r => r.status);
   ```

### Expected
A count of the signed-in employee's pending leave requests, or a clean empty
response in a build with no Leave module.

### Actual
**HTTP 500**, empty body.

```
PHP Fatal error: Uncaught Error:
Class "Leaves\User\Api\LeavesActionManager" not found
  in /var/www/html/core/src/Dashboard/User/Api/DashboardActionManager.php:23
```

### Root cause
`core/src/Dashboard/User/Api/DashboardActionManager.php:23,26` instantiates
`LeavesActionManager` and `EmployeeLeave` with no `class_exists` guard. The
sibling `core/src/Dashboard/Admin/Api/DashboardActionManager.php:43` has the same
problem (`new EmployeeLeave()` for the `numberOfLeaves` tile).

Note the SPA dashboard page itself loads (it does not call this method), so the
defect is only visible to a client that calls the action directly — which is why
a page-level smoke test misses it.

### Suggested fix
Guard both dashboard managers as in BUG-001, returning `0` when the Leave module
is absent.

---

## BUG-003 — Unguarded Pro-only namespace references across open-source code

**Severity:** Medium — latent fatals; each becomes a live High the moment its code
path is reached.

A repository scan for Pro-only namespaces (`Leaves\`, `Expenses\`) in open-source
code returns **16 references in 8 files**. Two are confirmed live (BUG-001,
BUG-002). The rest are latent:

| File | Guarded? |
|---|---|
| `extensions/company_overview/user/src/ApiController.php:12` | **No** — live (BUG-001) |
| `core/src/Dashboard/User/Api/DashboardActionManager.php:10,11` | **No** — live (BUG-002) |
| `core/src/Dashboard/Admin/Api/DashboardActionManager.php:11` | **No** — latent |
| `core/src/Charts/Admin/Rest/ChartsRestEndpoint.php:8` | **No** — latent |
| `core/src/Employees/Rest/EmployeeLeavesRestEndPoint.php:9` | **No** — latent |
| `core/src/Employees/Admin/Api/EmployeesActionManager.php:19` (`Expenses\`) | **No** — latent |
| `core/src/Classes/GoogleCalendarApiManager.php:12-15` | **No** — latent |
| `extensions/advance_reports/admin/src/Reports/EmployeeLeaveEntitlementReport.php:6` | **No** — latent |
| `core/src/TimeSheets/User/Api/TimeSheetsActionManager.php:19-22` | **Yes** — 5 `class_exists` guards |

`EmployeeLeaveEntitlementReport` is not currently reachable because the `Reports`
table is empty in this instance — it would fatal as soon as that report is
registered.

### Suggested fix
Apply the `TimeSheetsActionManager` guard pattern consistently, or add a CI check
that fails the open-source build when a `Leaves\`, `Expenses\`, `Recruitment\`,
`Performance\`, `Esign\`, `Learn\`, `Tasks\` or `Team\` symbol is instantiated
without a `class_exists` guard.

---

## Not defects — investigated and cleared

**Cron `getaddrinfo for mysql failed`** — 10 occurrences in `app/data/icehrm.log`.
Historical only, from a window when MySQL was unreachable during container
restarts. `docker exec icehrm-worker php /var/www/html/app/cron.php` now exits 0
and runs Email Sender, Document Expire Alert and Payroll Processor. Not a live
defect.

**`rc-collapse children` console warning** on the Reports pages (4 occurrences) —
an Ant Design deprecation notice, not an error. Logged as WARN, no functional
impact.
