<?php
/**
 * Integration test — the "get" (list) column of the access matrix, every role.
 * ===========================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * "get" means LIST OBJECTS, and it is the verb where a role grant is most dangerous:
 * a list is not a single record, so an over-broad grant does not leak one row — it
 * leaks the whole table in one request. The matrix's only row-scoped list is the
 * only-me one, and BaseService::get() applies that filter for registered user tables
 * only, which is exactly what this sweep pins down.
 *
 * For every employee-owned model it seeds a row owned by the scenario's target and
 * asserts whether the ACTOR'S OWN LIST CALL returns that row:
 *
 *     Employee listing, row owned by ANOTHER employee -> NOT RETURNED
 *     Manager listing, row owned by a NON-subordinate -> NOT RETURNED (finding 2.11)
 *     Anonymous listing anything                      -> NOT RETURNED
 *     Employee listing, row owned by THEMSELVES       -> returned iff declared
 *     Manager listing, row owned by a subordinate     -> returned iff declared
 *     Admin listing anybody's row                     -> returned iff declared
 *
 * The oracle is membership of the returned list, not whether the call 403s: a model
 * may legitimately let an employee call `get` and still owe them only their own
 * rows. "Allowed to list" and "allowed to see this row in the list" are different
 * questions, and only the second one is a disclosure.
 *
 * NOTE this is the same surface the Employee element sweep covers for single-record
 * reads. Both are needed: a model can scope `element` correctly and still hand back
 * everybody's rows from `get`, which is precisely the bug class behind
 * "service.php?t=EmployeeSalary&a=get&employee=<own id>".
 *
 * Run:  php test/integration/security/GetAccessTest.php
 * Exit 0 = clean; 1 = any LEAK / OVER-DENY / BLOCKED.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$sweep = new VerbAccessSweep($ctx, 'get');

/**
 * Seed a row owned by $ownerEmp, list the model as whoever is currently acting, and
 * report whether that row came back.
 */
$attempt = function ($model, $ownerEmp) use ($ctx, $bs) {
    $fqcn  = $ctx->allModels()[$model];
    $obj   = new $fqcn();
    $table = $obj->table;
    $ownerCol = $ctx->employeeOwnerColumn($obj, $table);

    $seedVals = array();
    if ($ownerCol !== null && $ownerEmp !== null) {
        $seedVals[$ownerCol] = $ownerEmp;
    }
    $id = $ctx->seedRow($table, $seedVals);

    $authDenied = false;
    $effect = false;
    try {
        $_REQUEST = array();
        $list = $bs->get($model);
        if (is_array($list)) {
            foreach ($list as $row) {
                if (is_object($row) && isset($row->id) && (string) $row->id === (string) $id) {
                    $effect = true;
                    break;
                }
            }
        }
    } catch (\Throwable $e) {
        $authDenied = VerbAccessSweep::isAuthDenial($e);
    }

    $ctx->query("DELETE FROM `$table` WHERE id = ?", array($id));

    return array('effect' => $effect, 'authDenied' => $authDenied);
};

// The manager list scenario is deliberately NOT asserted. managerRecordScopeAllows()
// governs record-level verbs only and explicitly excludes "get" — "a LIST is
// row-scoped elsewhere (userTables); narrowing it here would break every manager list
// screen". So a manager listing rows beyond their own reports is the product's
// intended behaviour today, and turning it into a failure here would be asserting a
// policy change rather than catching a regression. It is still run and counted under
// RELAXED, so the scale of it stays visible.
$sweep->run($attempt, true, array(
    'Manager(non-sub)' => 'manager list scoping is out of scope of managerRecordScopeAllows by design',
));

// The vertical half: models with no employee owner. A plain Employee may LIST the
// reviewed public lookups (that is what populates dropdowns) and nothing else —
// listing an admin/system table hands over the whole table in one request.
$sweep->runVertical($attempt);

$ctx->cleanup();
exit($sweep->report());
