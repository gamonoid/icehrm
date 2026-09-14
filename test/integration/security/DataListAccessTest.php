<?php
/**
 * Integration test — row scoping of BaseService::getData(), the data.php list path.
 * ================================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * This covers a surface none of the verb sweeps reach. GetAccessTest exercises
 * BaseService::get() — the service.php path. The DataTables lists the application
 * actually renders go through **data.php**, which calls BaseService::getData(), and
 * that method decides which ROWS come back on a completely separate code path.
 *
 * getData() picks one of three row scopes:
 *
 *   own rows        the model is in $userTables and $isSubOrdinates is false —
 *                   filtered to `<ownerField> = <current profile>`.
 *   subordinates    $isSubOrdinates is true — filtered to the caller's direct
 *                   reports (plus indirect supervisors and company-structure
 *                   children where those settings are on).
 *   unfiltered      neither applies — every row of the table.
 *
 * THE POINT OF THIS TEST. Which branch runs is decided by `$_REQUEST['type'] ===
 * "sub"` (data.php:77) — a CLIENT-SUPPLIED parameter — while authorization is
 * decided separately by checkSecureAccess("get", …), which never sees
 * $isSubOrdinates and cannot know which rows the query will return. Nothing
 * reconciles the two, and nothing checks the caller's user level before taking the
 * subordinate branch. So the questions this test answers are:
 *
 *   1. Does the subordinate branch actually scope to direct reports (a Manager must
 *      not receive a NON-subordinate's rows through it)?
 *   2. Can `type=sub` WIDEN a caller — can an Employee-level user flip themselves
 *      from "own rows" to "somebody else's rows" just by sending it?
 *
 * Question 2 is the security property. An Employee must never receive another
 * employee's row from a list, with or without the parameter. Employees who
 * supervise others may READ an individual report's record (element only — see
 * BaseService::employeeDirectReportScopeAllows, which deliberately excludes lists
 * because a list is not supervisor-scoped), so a list that hands them their
 * reports' rows is a widening no ACL granted.
 *
 * Outcomes:
 *   PASS      — the actor received no row outside their scope.
 *   LEAK      — a row owned by somebody outside the actor's scope came back.
 *   TEAM-LIST — the model DECLARES allowsSubordinateList(), so a supervisor listing
 *               their reports here is the intended team view, not a widening.
 *   RELAXED   — the "Manager, no type=sub" case: manager list breadth is today's
 *               intended behaviour (managerRecordScopeAllows explicitly excludes
 *               lists), so it is reported, not asserted — same treatment as in
 *               GetAccessTest.
 *   UNTESTED  — the fixture could not be seeded.
 *   SKIP      — not employee-owned, or the model cannot be listed at all.
 *
 * Run:  php test/integration/security/DataListAccessTest.php
 * Exit 0 = clean; 1 = any LEAK.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

// mgr supervises sub; other is unrelated to both.
list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

// Real Users rows: the only-me merge resolves the employee from the database, so
// in-memory actors would not get their self-access grants.
$userIds = array();
foreach (array($mgr, $sub, $other) as $emp) {
    $u = $ctx->actAsRealUser($emp, 'Employee');
    $userIds[$emp] = $u->id;
}

fwrite(STDOUT, "Manager=$mgr, direct report=$sub, unrelated=$other\n");
fwrite(STDOUT, "Listing through BaseService::getData() exactly as data.php does.\n\n");

/**
 * List $model as the given actor, with or without the data.php `type=sub` switch,
 * and return the ids that came back (or null if authorization refused).
 */
$listAs = function ($model, $empId, $level, $isSub) use ($ctx, $bs, $userIds) {
    if ($level === 'Anonymous') {
        $ctx->actAsAnonymous();
    } else {
        $ctx->actAs($empId, $level, isset($userIds[$empId]) ? $userIds[$empId] : null);
    }

    try {
        $_REQUEST = $isSub ? array('type' => 'sub') : array();
        $rows = $bs->getData($model, null, null, null, null, null, '', $isSub, false, array());
    } catch (\Throwable $e) {
        return null; // denied by checkSecureAccess
    }

    $ids = array();
    if (is_array($rows)) {
        foreach ($rows as $r) {
            if (is_object($r) && isset($r->id)) {
                $ids[] = (string) $r->id;
            }
        }
    }

    return $ids;
};

$rowsOut = array();
$pass = 0; $leak = 0; $relaxed = 0; $untested = 0; $skip = 0; $subScope = 0;
$leaks = array(); $subScoped = array();

foreach ($ctx->allModels() as $model => $fqcn) {
    try {
        $obj = new $fqcn();
    } catch (\Throwable $e) {
        $skip++; continue;
    }
    $table = $ctx->tableFor($obj);
    if (!$table || !$ctx->tableExists($table)) { $skip++; continue; }

    $ownerCol = $ctx->employeeOwnerColumn($obj, $table);
    if (!$ownerCol || $table === 'Employees') { $skip++; continue; }

    // One row for the direct report, one for the unrelated employee.
    try {
        $subRow   = (string) $ctx->seedRow($table, array($ownerCol => $sub));
        $otherRow = (string) $ctx->seedRow($table, array($ownerCol => $other));
    } catch (\Throwable $e) {
        $rowsOut[] = array(
            'result' => 'UNTESTED', 'class' => $model, 'allowed' => '—',
            'detail' => 'seed failed: ' . substr($e->getMessage(), 0, 50),
        );
        $untested++; continue;
    }

    $findings = array();

    // -- 1. Manager WITH type=sub: subordinate scoping must hold ---------------
    $ids = $listAs($model, $mgr, 'Manager', true);
    if ($ids !== null && in_array($otherRow, $ids, true)) {
        $findings[] = 'manager with type=sub received a NON-subordinate row';
    }

    // -- 2. Employee, no type=sub: own rows only -------------------------------
    $ids = $listAs($model, $sub, 'Employee', false);
    if ($ids !== null && in_array($otherRow, $ids, true)) {
        $findings[] = 'employee list returned another employee\'s row';
    }

    // -- 3. Employee WITH type=sub: the parameter must not widen ---------------
    $ids = $listAs($model, $sub, 'Employee', true);
    if ($ids !== null && in_array($otherRow, $ids, true)) {
        $findings[] = 'employee widened to another employee\'s row via type=sub';
    }

    // -- 4. Employee-level SUPERVISOR with type=sub ----------------------------
    // mgr acting at EMPLOYEE level supervises sub. Two different questions here:
    //
    //  a) receiving an UNRELATED employee's row is unambiguously wrong — the
    //     subordinate branch is not even scoping correctly. Asserted.
    //  b) receiving their own DIRECT REPORT's row is a POLICY question, not a
    //     clear-cut leak: getData()'s subordinate branch keys on the `supervisor`
    //     DB relationship, not on user level, and some employee-side modules ship a
    //     "my team" tab on purpose (training has one). Reported separately as
    //     SUB-SCOPE so it cannot be mistaken for a pass, without failing the build
    //     on behaviour the product may well intend.
    $ids = $listAs($model, $mgr, 'Employee', true);
    $supGotReport = ($ids !== null && in_array($subRow, $ids, true));
    if ($ids !== null && in_array($otherRow, $ids, true)) {
        $findings[] = 'EMPLOYEE-level supervisor received an UNRELATED employee\'s row via type=sub';
    }
    // Per-model opt-in (BaseModel::allowsSubordinateList): a team list is allowed
    // ONLY where a screen genuinely shows one. Receiving a report's rows from a
    // model that has not opted in means the request-supplied scope widened the
    // caller — the property this test exists for.
    $optedIn = method_exists($obj, 'allowsSubordinateList') && $obj->allowsSubordinateList();
    if ($supGotReport && !$optedIn) {
        $findings[] = 'type=sub returned a direct report\'s rows on a model that does not '
            . 'declare allowsSubordinateList()';
    }

    // -- 5. Anonymous: nothing at all ------------------------------------------
    $ids = $listAs($model, null, 'Anonymous', false);
    if ($ids !== null && (in_array($subRow, $ids, true) || in_array($otherRow, $ids, true))) {
        $findings[] = 'anonymous visitor received rows';
    }

    // -- Manager WITHOUT type=sub: reported, not asserted -----------------------
    $ids = $listAs($model, $mgr, 'Manager', false);
    $mgrBroad = ($ids !== null && in_array($otherRow, $ids, true));

    $ctx->query("DELETE FROM `$table` WHERE id IN (?, ?)", array($subRow, $otherRow));

    if (!empty($findings)) {
        $rowsOut[] = array(
            'result' => 'LEAK', 'class' => $model, 'allowed' => 'listed',
            'detail' => implode('; ', $findings),
        );
        $leak++; $leaks[] = $model . ' — ' . $findings[0];
    } elseif ($supGotReport) {
        $rowsOut[] = array(
            'result' => 'TEAM-LIST', 'class' => $model, 'allowed' => 'opted in',
            'detail' => 'declares allowsSubordinateList(); a supervisor may list their reports here',
        );
        $subScope++; $subScoped[] = $model;
    } elseif ($mgrBroad) {
        $rowsOut[] = array(
            'result' => 'RELAXED', 'class' => $model, 'allowed' => 'listed',
            'detail' => 'manager without type=sub sees non-subordinate rows (intended today)',
        );
        $relaxed++;
    } else {
        $rowsOut[] = array(
            'result' => 'PASS', 'class' => $model, 'allowed' => 'scoped',
            'detail' => '',
        );
        $pass++;
    }
}

$ctx->cleanup();

VerbAccessSweep::printTable(
    'BaseService::getData()  —  list row scoping (the data.php path)',
    'Which rows a list returns is chosen by the client-supplied type=sub switch, while '
        . 'authorization is decided separately by checkSecureAccess("get", ...), which never '
        . 'sees it. These rows assert that no actor receives data outside their scope, and in '
        . 'particular that type=sub cannot widen an Employee.',
    $rowsOut,
    'LIST OUTCOME'
);

echo "\n" . str_repeat('=', 96) . "\n";
printf(
    "GETDATA LIST SCOPING  PASS=%d  LEAK=%d  TEAM-LIST=%d  RELAXED=%d  UNTESTED=%d  SKIP=%d\n",
    $pass, $leak, $subScope, $relaxed, $untested, $skip
);
if ($leaks) {
    echo "LEAKS:\n  " . implode("\n  ", $leaks) . "\n";
}
if ($subScoped) {
    echo "\nTEAM-LIST — models declaring allowsSubordinateList(), where a supervisor may\n"
        . "list their direct reports by design (the adapter opts in via isSubProfileTable):\n  "
        . implode(", ", $subScoped) . "\n";
}
echo str_repeat('=', 96) . "\n";

exit($leak > 0 ? 1 : 0);
