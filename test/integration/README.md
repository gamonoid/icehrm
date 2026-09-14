# Integration tests

Framework-free PHP integration tests that boot the **whole** IceHRM application
(all core modules + every extension) and exercise `Classes\BaseService` against a
real database — the same convention as `test/appshell/menu_filter_test.php`
(a plain script that `exit()`s non-zero on failure), so there is no dependency on a
particular PHPUnit version. (The vendored PHPUnit is PHP-7 era and will not run on
the PHP 8.1 runtime these containers use; a standalone script sidesteps that
entirely — hence no PHPUnit dependency was added.)

## What's here

| File | Purpose |
|---|---|
| `bootstrap.php` | Boots the full app in CLI (config → `config.base.php` → `include.common.php` → `server.includes.inc.php`), exactly as a web request does. Picks the app config from `ICEHRM_TEST_CONFIG`, else the testing container config, else the dev config. |
| `lib/TestContext.php` | Helpers: act as a given user, describe a model's owner column, generically seed a throwaway row (fills NOT-NULL columns, FK checks off), and clean up. |
| `security/ElementAccessTest.php` | The **`element`** column, all roles — PART 1 role sweep + vertical default-deny; PART 2 the anonymous *declared* policy audit. |
| `security/GetAccessTest.php` | The **`get` (list)** column, all roles — a list leaks a whole table at once, so this pins who may see whose rows in a listing. |
| `security/AddAccessTest.php` | The **`add`** column, all roles — ownership spoofing: may you create a row owned by somebody else? |
| `security/SaveAccessTest.php` | The **`save`** column, all roles — may you edit a row outside your scope? |
| `security/DeleteAccessTest.php` | The **`delete`** column, all roles — may you destroy a row outside your scope? |
| `security/DataListAccessTest.php` | Row scoping of **`BaseService::getData()`** — the `data.php` list path the verb sweeps never reach. Asserts `type=sub` cannot widen a caller. |
| `lib/VerbAccessSweep.php` | Shared sweep the five verb tests are built on: seven role scenarios, the vertical phase, expectations, reporting, exit code. |
| `run.sh` | Brings up `mysql-testing` and runs the tests inside the `icehrm` testing container. |

The security tests live under `security/`, one file per **verb** of the
[model access matrix](../../docs/MODEL_ACCESS_MATRIX.md), each sweeping every role
inside. `run.sh` with no argument runs every `*Test.php` (subdirectories included);
`run.sh <TestFile>` runs one — the path prefix is optional.

## Running

```bash
# whole flow (build image, start DB, run) — via docker-compose-testing.yaml
test/integration/run.sh                 # runs ALL *Test.php
test/integration/run.sh --fresh         # wipe docker/testing/db_data first (clean seed)
test/integration/run.sh EmployeeElement # one test — exact file name, name without
                                        # .php, or any unique substring; unknown or
                                        # ambiguous names list the available tests

# a single test, or against an already-running container:
docker compose -f docker-compose-testing.yaml run --rm --no-deps icehrm \
  php -d xdebug.mode=off test/integration/security/EmployeeElementAccessTest.php
```

The DB seeds from `docker/init.sql` (schema + master data — a single admin
employee). The tests seed the fixtures they need and delete them again, so the DB is
left as it was found. To reset completely, delete `docker/testing/db_data` (or use
`--fresh`).

Exit code `0` = no leaks; `1` = at least one leak.

## ElementAccessTest — the `element` column

The element counterpart of the other four verb sweeps; it replaced the five
per-role files (`Admin…`, `Manager…`, `Employee…`, `EmployeeOnlyMe…`,
`Anonymous…ElementAccessTest`), which asked the same question from five angles and
duplicated their fixtures. Three parts:

**PART 1 — role sweep** over employee-owned models: the seven shared scenarios
(below), covering Admin, Manager (subordinate and non-subordinate), Employee (own
row and another's), Anonymous, and the Employee-level supervisor.

**PART 1b — vertical default-deny** (`runVertical`, shared with the other verbs):
models with **no employee owner** — the lookup, config and admin/system tables. A
plain Employee must be denied `Audit`, `RestAccessToken`, `SystemData`,
`Migration`, `EmailLog`, backups and candidate data, and is allowed only the
reviewed public-lookup allowlist. The oracle is deliberately an explicit
allowlist, **not** each model's own ACL — deriving it from the ACL would let a
mis-declared model pass itself, and `RestAccessToken`'s inherited default granting
Employee `element` is exactly that bug.

**PART 2 — anonymous declared policy**: every model's anonymous matrix must be
empty unless reviewed public, and must **never** contain `add`/`save`/`delete`.
This audits the *declaration*; the runtime side is scenario 6 plus the vertical
phase. It is a policy guard, not proof the live public surface is enforced —
`getAnonymousAccess()` is consulted on no real request path today (see
`docs/MODEL_ACCESS_MATRIX.md`).

One documented exception: `Audit [Employee(self)]` is relaxed. The inherited
only-me default declares `element`, but the generic path has never granted an
employee their own audit entries — verified identical before the ACL tightening.

Current result: **PART 1 PASS=49 clean; vertical PASS=92 clean; PART 2 PASS=148
with no unreviewed or writable declarations.**

## DataListAccessTest — the getData() list path

The verb sweeps call `BaseService::get()` (the `service.php` path). The DataTables
lists the application actually renders go through **`data.php`** →
`BaseService::getData()`, which chooses the returned rows on a separate code path:
own rows (model in `$userTables`), the caller's direct reports (`$isSubOrdinates`),
or unfiltered.

That branch used to be picked by `$_REQUEST['type'] === "sub"` — a client-supplied
parameter — while authorization was decided by `checkSecureAccess("get", …)`, a
verb+model check made before the rows are chosen and therefore unable to govern
them. Any caller with direct reports could flip an employee-owned list from "my
rows" to "my reports' rows" on a model with no team view at all. This test found it
across 23 models.

It is now gated per model: `BaseModel::allowsSubordinateList()` defaults to false,
and `BaseService::resolveSubordinateListScope()` narrows the requested scope to
what the model permits (see the access matrix doc).

Asserted, as hard failures:

- a Manager sending `type=sub` receives no non-subordinate row;
- an Employee receives nobody else's row, with or without the parameter;
- `type=sub` returns a report's rows **only** on a model declaring
  `allowsSubordinateList()`;
- an Anonymous visitor receives nothing.

Outcomes: **PASS**; **LEAK**; **TEAM-LIST** (the model opts in, so a supervisor
listing their reports is the intended team view); **RELAXED** (a Manager *without*
`type=sub` seeing broadly — today's intended behaviour, same treatment as in
GetAccessTest); **UNTESTED**; **SKIP**.

Current result: **PASS=32, LEAK=0, TEAM-LIST=17.**

## The verb sweeps — GetAccessTest / AddAccessTest / SaveAccessTest / DeleteAccessTest

The `element` column is sliced per role (one file each, above) because each role
scopes single-record reads differently enough to deserve its own narrative. The
other four columns are sliced **per verb** instead: how a verb is invoked is what
differs, while the role is a cheap loop parameter. Each verb test supplies one
callback to `lib/VerbAccessSweep.php`, which runs the same six scenarios per model:

| # | Actor | Row owner | Expectation |
|---|---|---|---|
| 1 | Admin | someone else | allowed iff Admin declares the verb |
| 2 | Manager | their subordinate | allowed iff Manager declares it |
| 3 | Manager | **non**-subordinate | denied — row scope (2.11) |
| 4 | Employee | themselves | allowed iff declared (role ∪ only-me) |
| 5 | Employee | another employee | denied — the IDOR property |
| 6 | Anonymous | someone else | denied |
| 7 | Employee **supervisor** | their direct report | `element` only, iff Manager may — never a write |

Scenario 7 is why the direct-report widening is safe to have: it grants the read
in one direction and pins the read-only limit in the other, on every verb.

Each test then calls `runVertical()` for the same verb against models with **no
employee owner** (lookups, config, admin/system tables). The rule differs by verb,
because "shared reference data" and "data an employee may change" are different
sets: `get`/`element` are allowed only for the reviewed allowlists, while
`add`/`save`/`delete` are **never** allowed — being able to read the country list
is not being able to rewrite it.

**Two signals, because "it didn't happen" has two causes.** Each callback reports
`effect` (did the verb actually take effect — row created/modified/deleted/returned)
and `authDenied` (did authorization specifically refuse). A scenario that should be
denied fails only if the verb **took effect**; one that should be allowed fails only
if **authorization** was what stopped it. Without that split, every licence check
and approval-status guard would be reported as an access-control regression, and an
`addElement()` that safely re-owns a spoofed row to the caller would look like a
leak.

Expectations come from a **loaded sample row**, not a blank model instance: several
matrices are record-conditional (`EmployeeOvertime` grants its owner `delete` only
while the request is still Pending), and a blank object has no status.

### Reading the output

Each test prints **one table per permission method** — the method is what is
actually under test, so that is how the results are grouped:

```
================================================================================
getUserAccess()  —  verb "get"
  Employee user level — applies to ALL rows, with no ownership scoping. Also
  covers the vertical phase (models with no employee owner).
================================================================================
  RESULT      CLASS                              "get" ALLOWED?
  ----------- ---------------------------------- ------------------------
  LEAK        ArchivedEmployee                   allowed
                employee reached another employee's row
  PASS        Attendance                         allowed
  PASS        Audit                              not allowed
  -- 141 classes, 7 finding(s)
```

Three columns: the **result**, the **class**, and whether that method **allows the
verb under test** for that class. Findings sort to the top, each with a one-line
reason underneath. `getAdminAccess`, `getManagerAccess`, `getUserAccess`,
`getUserOnlyMeAccess`, `getAnonymousAccess` and the
`employeeDirectReportScopeAllows` runtime gate each get their own table, followed
by a TOTALS line.

Results: **PASS**; **LEAK** (verb reached a row outside the actor's scope);
**LEAK-VERT** (reached a lookup/admin/system table); **UNDECLARED** (runtime
allowed a verb the matrix does not declare); **OVER-DENY** / **BLOCKED**
(authorization refused something declared); **RELAXED** and **UNTESTED** (reported,
never counted as findings).

Current results:

| Verb | Role sweep | Vertical |
|---|---|---|
| `element` | PASS=49 clean | PASS=92 clean |
| `add` | PASS=49 clean | PASS=92 clean |
| `save` | PASS=49 clean | PASS=92 clean |
| `delete` | PASS=49 clean | PASS=92 clean |
| `get` | PASS=47, **LEAK=4, UNDECLARED=2** | PASS=87, **LEAK=5** |

### GetAccessTest is expected to be RED

Every finding is a full-table list handed to a plain Employee. None of the other
four verbs has any.

The shape of the bug: a model declares `getUserAccess()` → `array("get")`, which is
a **role-level** grant applying to every row with no ownership scoping, and the
model is not a registered user table, so `BaseService::get()` has no row filter to
apply. A plain Employee listing it receives **every employee's** rows. This is the
same bug class as the original horizontal IDOR findings; the earlier sweep missed
it only because it probed `element`, not `get`.

Two ways to close each one, and the choice is a product decision:
1. `addUserClass()` for the model, so `get()` row-scopes the list — employees keep
   their own list and the leak closes. (Matches `EmployeeTimeSheet` /
   `EmployeeTrainingSession`.)
2. `getUserAccess()` → `array()`, which closes the leak but removes the employee
   list feature entirely, since the table is not row-scopable today.

In this build the known instances are **`ArchivedEmployee`** (former staff) and
**`ImmigrationDocument`**. Run the sweep for the current list rather than trusting
this paragraph — the counts in the sample output above are illustrative.

Four other models list to employees *by design* and are allowlisted in
`VerbAccessSweep::$LIST_PUBLIC` rather than reported: `CompanyDocument` and
`CompanyLoan` (the employee Documents tab and the loan-type lookup), and
`TrainingSession`/`TrainingSessionWithCourse` (open sign-up sessions).

Two related informational categories the sweep reports rather than failing on:

- **RELAXED** — the `Manager(non-sub)` scenario is not asserted for `get`.
  `managerRecordScopeAllows()` governs record-level verbs only and explicitly
  excludes lists ("narrowing it here would break every manager list screen"), so a
  manager listing beyond their reports is today's intended behaviour. Still run and
  counted, so its scale stays visible.
- **INERT DECLARATIONS** — models declaring only-me `get` without being registered
  user tables. `checkSecureAccess()` refuses to authorise such a list (nothing would
  constrain the rows), so the grant does nothing. Fail-closed, not a hole, but the
  declaration misleads anyone reading the model.
