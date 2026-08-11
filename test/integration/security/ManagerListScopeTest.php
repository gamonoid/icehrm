<?php
/**
 * Integration test — a Manager's unscoped list is restricted to their team.
 * =======================================================================
 *
 * managerRecordScopeAllows() skips "get" because a list is "already row-scoped by
 * userTables" — but that list is MODULE-scoped. Module managers register their user
 * classes only when MODULE_TYPE != 'admin', so under an admin module path (which a
 * Manager may legitimately request: attendance, overtime, projects, training,
 * travel, documents) $userTables is empty and the list fell through unrestricted,
 * returning every employee's rows. BaseService::getManagerListScopeClause() supplies
 * the missing row restriction; this pins its decisions.
 *
 * Run:  php test/integration/security/ManagerListScopeTest.php
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

$attendance = new \Attendance\Common\Model\Attendance();

fwrite(STDOUT, "BaseService::getManagerListScopeClause — manager list row scope\n\n");

// --- Manager: restricted to their own team ---------------------------------
$ctx->actAsRealUser($mgr, 'Manager');
list($clause, $values) = $bs->getManagerListScopeClause($attendance);

verdict('Manager gets a row restriction on an employee-owned model', $clause !== '');
verdict('restriction targets the owner column', strpos($clause, 'employee in (') !== false);
verdict('restriction is parameterised (no inlined ids)', strpos($clause, '?') !== false);
verdict('the manager\'s own id is included', in_array((string) $mgr, $values, true));
verdict('a managed employee is included', in_array((string) $sub, $values, true));
verdict('a NON-managed employee is excluded', !in_array((string) $other, $values, true));
verdict('placeholder count matches value count', substr_count($clause, '?') === count($values));

// --- Admin: unaffected -----------------------------------------------------
$ctx->actAsRealUser($mgr, 'Admin');
list($adminClause, $adminValues) = $bs->getManagerListScopeClause($attendance);
verdict('Admin list is NOT restricted', $adminClause === '' && empty($adminValues));

// --- Employee: not this gate's job (own-row scoping handles it) ------------
$ctx->actAsRealUser($sub, 'Employee');
list($empClause) = $bs->getManagerListScopeClause($attendance);
verdict('Employee is not restricted by THIS clause (userTables scopes them)', $empClause === '');

// --- Lookup/config models (no employee owner) are untouched ----------------
$ctx->actAsRealUser($mgr, 'Manager');
if (class_exists('\\Jobs\\Common\\Model\\JobTitle')) {
    list($lookupClause) = $bs->getManagerListScopeClause(new \Jobs\Common\Model\JobTitle());
    verdict('lookup model (JobTitle) is not restricted', $lookupClause === '');
}

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("MANAGER LIST SCOPE  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
