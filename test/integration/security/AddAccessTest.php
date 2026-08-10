<?php
/**
 * Integration test — the "add" column of the access matrix, every role.
 * =====================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * "add" is the one verb with no existing row to own, so the question it answers is
 * different from the other three: not "may you touch THIS row?" but **"may you
 * create a row owned by SOMEBODY ELSE?"** That is the ownership-spoofing case —
 * filing an expense, a leave request or a goal against another employee's name.
 *
 * For every employee-owned model this sweeps all six role scenarios (see
 * VerbAccessSweep), each attempting to create a record owned by the scenario's
 * target employee:
 *
 *     Employee creating a row owned by ANOTHER employee -> DENIED
 *     Manager creating one owned by a NON-subordinate   -> DENIED  (finding 2.11)
 *     Anonymous creating anything                       -> DENIED
 *     Employee creating their OWN row                   -> allowed iff declared
 *     Manager creating one for a subordinate            -> allowed iff declared
 *     Admin creating one for anybody                    -> allowed iff declared
 *
 * The oracle is deliberately "did a row owned by the TARGET appear?", not "was the
 * call accepted". For a registered user table addElement() overwrites the submitted
 * owner with the caller's own profile id, so a spoofing attempt is accepted but
 * lands harmlessly on the caller's own row — that is a correct defence and is
 * reported as denied, exactly as a 403 would be.
 *
 * Run:  php test/integration/security/AddAccessTest.php
 * Exit 0 = clean; 1 = any LEAK / OVER-DENY / BLOCKED.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$sweep = new VerbAccessSweep($ctx, 'add');

$seq = 0;

/**
 * Try to CREATE a row owned by $ownerEmp as whoever is currently acting, and report
 * whether such a row actually materialised.
 */
$attempt = function ($model, $ownerEmp) use ($ctx, $bs, &$seq) {
    $fqcn  = $ctx->allModels()[$model];
    $obj   = new $fqcn();
    $table = $obj->table;
    $ownerCol = $ctx->employeeOwnerColumn($obj, $table);

    // A mapped column carrying a value unique to this attempt, so the row this call
    // creates can be told apart from every other row in the table.
    $probe = $ctx->writableProbeColumn($obj, $table, $ownerCol);
    if ($probe === null) {
        throw new \RuntimeException("no writable probe column on $table");
    }
    $col = $probe['column'];
    $seq++;
    $marker = $probe['type'] === 'text' ? ('ZZADD' . $seq) : (600000 + $seq);

    // Every NOT NULL column without a default still has to be filled, or the insert
    // fails for reasons that have nothing to do with authorization.
    $overrides = array($col => $marker);
    if ($ownerCol !== null && $ownerEmp !== null) {
        $overrides[$ownerCol] = $ownerEmp;
    }
    $payload = $ctx->requiredInsertValues($table, $overrides);

    $authDenied = false;
    try {
        $bs->addElement($model, $payload);
    } catch (\Throwable $e) {
        $authDenied = VerbAccessSweep::isAuthDenial($e);
    }

    // Did a row owned by the TARGET appear? An accepted call that silently
    // re-owned the row to the caller is not a successful "add for somebody else".
    if ($ownerCol !== null && $ownerEmp !== null) {
        $rows = $ctx->query(
            "SELECT id FROM `$table` WHERE `$col` = ? AND `$ownerCol` = ?",
            array($marker, $ownerEmp)
        );
    } else {
        // No owner column (a lookup/config/system table): the question is simply
        // whether the caller managed to create a row at all.
        $rows = $ctx->query("SELECT id FROM `$table` WHERE `$col` = ?", array($marker));
    }
    $effect = !empty($rows);

    // Remove anything this attempt created, whoever ended up owning it.
    $ctx->query("DELETE FROM `$table` WHERE `$col` = ?", array($marker));

    return array('effect' => $effect, 'authDenied' => $authDenied);
};

$sweep->run($attempt);

// The vertical half: models with no employee owner — lookups, config and
// admin/system tables. No plain Employee may CREATE one, allowlist or not.
$sweep->runVertical($attempt);

$ctx->cleanup();
exit($sweep->report());
