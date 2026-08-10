<?php
/**
 * Integration test — the "save" column of the access matrix, every role.
 * ======================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * "save" means EDIT AN EXISTING OBJECT. The property under test is the one a role
 * grant alone cannot express: holding "save" says which VERB a level may use, never
 * WHICH ROWS. So for every employee-owned model this sweeps all six role scenarios
 * (see VerbAccessSweep) and asserts nobody edits a row outside their own scope:
 *
 *     Employee editing ANOTHER employee's row   -> DENIED   (the IDOR property)
 *     Manager editing a NON-subordinate's row   -> DENIED   (finding 2.11)
 *     Anonymous editing anything                -> DENIED
 *     Employee editing their OWN row            -> allowed iff declared
 *     Manager editing a subordinate's row       -> allowed iff declared
 *     Admin editing anybody's row               -> ALLOWED  (over-restriction guard)
 *
 * The verb runs through the real entry point, BaseService::addElement() with an id
 * present (that is what makes it a "save" rather than an "add"), so the whole
 * production path — ownership re-read, manager record scope, only-me gate — is
 * exercised, not just checkSecureAccess() in isolation.
 *
 * The outcome is measured by RE-READING THE ROW from the database: "allowed" means
 * the edit actually landed. A request that is accepted but silently discards the
 * change is correctly reported as denied, and one that throws yet still mutates the
 * row is correctly reported as a leak.
 *
 * Run:  php test/integration/security/SaveAccessTest.php
 * Exit 0 = clean; 1 = any LEAK / OVER-DENY / BLOCKED.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$sweep = new VerbAccessSweep($ctx, 'save');

/**
 * Seed a row owned by $ownerEmp, try to edit it as whoever is currently acting,
 * and report whether the edit landed.
 */
$seq = 0;
$attempt = function ($model, $ownerEmp) use ($ctx, $bs, &$seq) {
    $fqcn  = $ctx->allModels()[$model];
    $obj   = new $fqcn();
    $table = $obj->table;
    $ownerCol = $ctx->employeeOwnerColumn($obj, $table);

    // A column to scribble on — the edit has to be observable.
    $probe = $ctx->writableProbeColumn($obj, $table, $ownerCol);
    if ($probe === null) {
        throw new \RuntimeException("no writable probe column on $table");
    }
    $col = $probe['column'];

    // Unique per attempt: several models carry UNIQUE constraints on exactly the
    // kind of column we probe (Users.username, …), so a constant would collide with
    // the previous scenario's row and report the model UNTESTED.
    $seq++;
    if ($probe['type'] === 'text') {
        $before = 'ZZBEFORE' . $seq;
        $after  = 'ZZEDITED' . $seq;
    } else {
        $before = 700000 + $seq;
        $after  = 800000 + $seq;
    }

    $seedVals = array($col => $before);
    if ($ownerCol !== null && $ownerEmp !== null) {
        $seedVals[$ownerCol] = $ownerEmp;
    }
    $id = $ctx->seedRow($table, $seedVals);

    $authDenied = false;
    try {
        $bs->addElement($model, array('id' => $id, $col => $after));
    } catch (\Throwable $e) {
        $authDenied = VerbAccessSweep::isAuthDenial($e);
    }

    $rows = $ctx->query("SELECT `$col` AS v FROM `$table` WHERE id = ?", array($id));
    $effect = !empty($rows) && (string) $rows[0]['v'] === (string) $after;

    // Drop the row now: the next scenario seeds its own, and models with composite
    // UNIQUE keys (TaskListAssignment's tasklist+employee, …) cannot hold two.
    $ctx->query("DELETE FROM `$table` WHERE id = ?", array($id));

    return array('effect' => $effect, 'authDenied' => $authDenied);
};

$sweep->run($attempt);

// The vertical half: models with no employee owner — lookups, config and
// admin/system tables. No plain Employee may EDIT one, allowlist or not: being
// allowed to read the country list is not being allowed to rewrite it.
$sweep->runVertical($attempt);

$ctx->cleanup();
exit($sweep->report());
