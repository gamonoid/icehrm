<?php
/**
 * Integration test — the per-action guard fires at the method boundary.
 * =====================================================================
 *
 * EmployeeDataAccessTest pins the shared ownership rule; this proves one
 * representative custom-action method (TimeSheets getTimeEntries) actually
 * consults it. A timesheet is seeded for a non-subordinate, and the method is
 * invoked as each role: the non-subordinate's manager is denied, while the owner
 * and an admin are served. If a future refactor drops the guard, this fails.
 *
 * Run:  php test/integration/security/TimeSheetActionGuardTest.php
 * Exit 0 = correct; 1 = any wrong verdict; 2 = fixture unavailable (skips).
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;
use Classes\IceResponse;
use TimeSheets\User\Api\TimeSheetsActionManager;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$table = 'EmployeeTimeSheets';
if (!$ctx->tableExists($table)) {
    fwrite(STDOUT, "SKIP — $table not present in this fixture\n");
    exit(2);
}

list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

// A timesheet owned by $other, who is NOT a subordinate of $mgr.
$tsId = $ctx->seedRow($table, array(
    'employee'   => $other,
    'date_start' => '2020-01-06',
    'date_end'   => '2020-01-12',
    'status'     => 'Pending',
));

function invokeGetTimeEntries($bs, $empId, $level, $tsId)
{
    // Build a fresh action manager bound to the shared BaseService, acting as $empId.
    global $ctx;
    $ctx->actAsRealUser($empId, $level);
    $am = new TimeSheetsActionManager();
    $am->setBaseService($bs);
    $am->setUser($bs->getCurrentUser());
    $req = new \stdClass();
    $req->id = $tsId;
    $req->sm = '';
    return $am->getTimeEntries($req);
}

fwrite(STDOUT, "TimeSheetsActionManager::getTimeEntries — ownership enforced\n\n");

$r = invokeGetTimeEntries($bs, $mgr, 'Manager', $tsId);
verdict(
    "Manager is DENIED a non-subordinate's timesheet entries",
    $r->getStatus() === IceResponse::ERROR
);

$r = invokeGetTimeEntries($bs, $other, 'Employee', $tsId);
verdict(
    "Owner is SERVED their own timesheet entries",
    $r->getStatus() === IceResponse::SUCCESS
);

$r = invokeGetTimeEntries($bs, $mgr, 'Admin', $tsId);
verdict(
    "Admin is SERVED any timesheet entries",
    $r->getStatus() === IceResponse::SUCCESS
);

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("TIMESHEET ACTION GUARD  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
