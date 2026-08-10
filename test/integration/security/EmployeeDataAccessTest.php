<?php
/**
 * Integration test — the shared per-action ownership rule.
 * =======================================================
 *
 * BaseService::currentUserCanAccessEmployeeData($employeeId) is the single check the
 * custom-action handlers use to close cross-employee IDORs (timesheets, overtime and
 * travel approval + logs, goal ratings, attendance punch, ...). It must match:
 *
 *   Admin / all-employee-data role  -> anyone
 *   the employee themselves         -> own data
 *   a manager / department head     -> those they manage
 *   anyone else                     -> denied
 *
 * Pinning it directly guarantees every guard built on it is correct; the per-method
 * HTTP behaviour is covered by the security e2e specs.
 *
 * Run:  php test/integration/security/EmployeeDataAccessTest.php
 * Exit 0 = all correct; 1 = any wrong verdict.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

fwrite(STDOUT, "BaseService::currentUserCanAccessEmployeeData — ownership matrix\n\n");

$ctx->actAsRealUser($mgr, 'Admin');
verdict('Admin may access anyone\'s data', $bs->currentUserCanAccessEmployeeData($other));
verdict('Admin: empty employee id is denied', !$bs->currentUserCanAccessEmployeeData(''));

$ctx->actAsRealUser($sub, 'Employee');
verdict('Employee may access their OWN data', $bs->currentUserCanAccessEmployeeData($sub));
verdict('Employee may NOT access another employee\'s data', !$bs->currentUserCanAccessEmployeeData($other));
verdict('Employee may NOT access the manager\'s data', !$bs->currentUserCanAccessEmployeeData($mgr));

$ctx->actAsRealUser($mgr, 'Manager');
verdict('Manager may access a SUBORDINATE\'s data', $bs->currentUserCanAccessEmployeeData($sub));
verdict('Manager may NOT access a NON-subordinate\'s data', !$bs->currentUserCanAccessEmployeeData($other));
verdict('Manager may access their OWN data', $bs->currentUserCanAccessEmployeeData($mgr));

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("EMPLOYEE DATA ACCESS  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
