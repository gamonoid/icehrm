<?php
/**
 * Integration test — the "delete" column of the access matrix, every role.
 * ========================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * "delete" is the least recoverable verb in the matrix, so the row-scoping property
 * matters most here: holding "delete" says which VERB a level may use, never WHICH
 * ROWS. For every employee-owned model this sweeps all six role scenarios (see
 * VerbAccessSweep) and asserts nobody destroys a row outside their own scope:
 *
 *     Employee deleting ANOTHER employee's row  -> DENIED
 *     Manager deleting a NON-subordinate's row  -> DENIED   (finding 2.11)
 *     Anonymous deleting anything               -> DENIED
 *     Employee deleting their OWN row           -> allowed iff declared
 *     Manager deleting a subordinate's row      -> allowed iff declared
 *     Admin deleting anybody's row              -> allowed iff declared
 *
 * The verb runs through the real entry point, BaseService::deleteElement(), and the
 * oracle is whether the row is actually GONE afterwards — a request that 403s but
 * deletes anyway would be caught, as would one that reports success without
 * deleting.
 *
 * NOTE for anyone extending this: deleteElement() runs the model's
 * executePreDeleteActions() BEFORE checkSecureAccess(), so a model whose pre-delete
 * hook has side effects performs them for callers who are then denied. Nothing in
 * the current models appears to mutate state there, so this test does not assert on
 * it — but it is the reason the oracle re-reads the row rather than trusting the
 * response.
 *
 * Run:  php test/integration/security/DeleteAccessTest.php
 * Exit 0 = clean; 1 = any LEAK / OVER-DENY / BLOCKED.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$sweep = new VerbAccessSweep($ctx, 'delete');

/**
 * Seed a row owned by $ownerEmp, try to delete it as whoever is currently acting,
 * and report whether the row is gone.
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
    try {
        $bs->deleteElement($model, $id);
    } catch (\Throwable $e) {
        $authDenied = VerbAccessSweep::isAuthDenial($e);
    }

    $effect = !$ctx->rowExists($table, $id);

    // Tidy up whatever survived, so the next scenario starts clean.
    if (!$effect) {
        $ctx->query("DELETE FROM `$table` WHERE id = ?", array($id));
    }

    return array('effect' => $effect, 'authDenied' => $authDenied);
};

$sweep->run($attempt);

// The vertical half: models with no employee owner — lookups, config and
// admin/system tables. No plain Employee may DELETE one, allowlist or not.
$sweep->runVertical($attempt);

$ctx->cleanup();
exit($sweep->report());
