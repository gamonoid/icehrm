<?php
/**
 * Integration test — the "element" column of the access matrix, every role.
 * =========================================================================
 * (see docs/MODEL_ACCESS_MATRIX.md)
 *
 * "element" means READ A SINGLE OBJECT BY ID, the surface every object-level IDOR
 * lives on. This is the element counterpart of the get/add/save/delete sweeps, and
 * like them it covers all roles in one file.
 *
 * Most of it is the shared seven-scenario sweep (lib/VerbAccessSweep.php). Three
 * questions do not fit that shape, because they are not "may this actor touch this
 * employee's row?" — they are about models with no employee owner at all, and about
 * declarations rather than behaviour. Those are PARTS 2 and 3.
 *
 * PART 1 — role sweep over employee-owned models (VerbAccessSweep):
 *
 *     Admin reading anybody's record            -> allowed iff declared
 *     Manager reading a subordinate's           -> allowed iff declared
 *     Manager reading a NON-subordinate's       -> DENIED  (finding 2.11)
 *     Employee reading their OWN                -> allowed iff declared
 *     Employee reading ANOTHER employee's       -> DENIED  (the IDOR property)
 *     Anonymous reading anything                -> DENIED
 *     Employee-SUPERVISOR reading a direct report's -> allowed iff Manager may
 *
 * PART 2 — vertical default-deny (models with NO employee owner), via
 * VerbAccessSweep::runVertical(), shared with the other four verb tests:
 *
 *     A plain Employee must be denied every admin/system table — Audit,
 *     RestAccessToken, SystemData, Migration, EmailLog, backups, candidate data —
 *     unless the model is on the reviewed public-lookup allowlist of reference data
 *     everybody may read. Default-deny against an explicit allowlist, NOT against
 *     each model's own ACL: deriving it from the ACL would let a mis-declared model
 *     (RestAccessToken, whose inherited default grants Employee "element") pass
 *     itself. A vertical finding is a TRIAGE item — either the model needs a tighter
 *     ACL, or it is genuinely public and belongs on the allowlist after review.
 *
 * PART 3 — anonymous declared policy (all models):
 *
 *     "Anonymous" has no matching user level, so a grant means any non-logged-in
 *     visitor may read the data — it is public to the internet. Every model's
 *     anonymous matrix must be empty unless reviewed public, and must NEVER contain
 *     a write verb. This audits the DECLARATION; PART 1 scenario 6 and PART 2 cover
 *     the runtime behaviour.
 *
 *     NOTE this is a policy guard, not proof the live public surface is enforced:
 *     getAnonymousAccess() is consulted on no real request path today (service.php
 *     cannot produce an Anonymous session, and the public job portal bypasses the
 *     ACL via $skipSecurityCheck and direct DataReader queries). What it pins is
 *     what would go live if the level were ever wired up. See
 *     docs/MODEL_ACCESS_MATRIX.md.
 *
 * Run:  php test/integration/security/ElementAccessTest.php
 * Exit 0 = clean; 1 = a finding in any part.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

// =============================================================================
// PART 1 — the shared role sweep, verb "element".
// =============================================================================

fwrite(STDOUT, "PART 1 — role sweep over employee-owned models\n\n");

$sweep = new VerbAccessSweep($ctx, 'element');

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
        $_POST = array(); // checkSecureAccess reads $_POST for the request-field path
        $got = $bs->getElement($model, $id);
        $effect = is_object($got) && !empty($got->id) && (string) $got->id === (string) $id;
    } catch (\Throwable $e) {
        $authDenied = VerbAccessSweep::isAuthDenial($e);
    }

    $ctx->query("DELETE FROM `$table` WHERE id = ?", array($id));

    return array('effect' => $effect, 'authDenied' => $authDenied);
};

$sweep->run($attempt, true, array(
    // Verified identical on the tree BEFORE the Employee ACL tightening: the model's
    // inherited only-me default declares "element", but the generic path has never
    // granted an employee their own audit entries — audit is an admin surface. Kept
    // as a documented exception rather than silently expected to pass.
    'Audit [Employee(self)]' => 'pre-existing: audit entries are admin-surface only',
));

// PART 2 — the vertical half of the same verb: models with NO employee owner.
// Default-deny against the reviewed public-lookup allowlist, so an admin/system
// table (Audit, RestAccessToken, SystemData, Migration, EmailLog, backups,
// candidate data) is never readable by a plain Employee.
$sweep->runVertical($attempt);

$sweepExit = $sweep->report();

// =============================================================================
// PART 3 — anonymous DECLARED policy across every model.
// =============================================================================

$map = $ctx->allModels();

$ANON_PUBLIC = array(
    'Country', 'Province', 'Nationality', 'SupportedLanguage', 'Timezone',
    'CurrencyType', 'ImmigrationStatus', 'Ethnicity',
);
$ANON_PUBLIC = array_flip($ANON_PUBLIC);
$WRITE_VERBS = array('add', 'save', 'delete');

$aPass = 0; $aLeak = 0; $aWrite = 0; $aSkip = 0;
$aRows = array();

foreach ($map as $model => $fqcn) {
    try {
        $obj = new $fqcn();
        $matrix = (array) $obj->getRoleBasedAccess('Anonymous', null);
    } catch (\Throwable $e) {
        $aSkip++; continue;
    }

    $writes = array_intersect($WRITE_VERBS, $matrix);
    if (!empty($writes)) {
        $aWrite++;
        $aRows[] = array(
            'result' => 'LEAK-WRITE', 'class' => $model,
            'allowed' => implode(', ', $matrix),
            'detail' => 'anonymous may ' . implode('/', $writes) . ' — never acceptable',
        );
        continue;
    }

    if (empty($matrix)) {
        $aPass++;
        $aRows[] = array('result' => 'PASS', 'class' => $model, 'allowed' => 'nothing', 'detail' => '');
    } elseif (isset($ANON_PUBLIC[$model])) {
        $aPass++;
        $aRows[] = array(
            'result' => 'PASS', 'class' => $model,
            'allowed' => implode(', ', $matrix), 'detail' => 'reviewed public',
        );
    } else {
        $aLeak++;
        $aRows[] = array(
            'result' => 'LEAK-DECL', 'class' => $model,
            'allowed' => implode(', ', $matrix),
            'detail' => 'declares anonymous access but is not reviewed public',
        );
    }
}

VerbAccessSweep::printTable(
    'getAnonymousAccess()  —  DECLARED policy (all verbs)',
    'No user level: a grant means any non-logged-in visitor may do this, so the data is '
        . 'public to the internet. Must be empty unless reviewed public, and must never '
        . 'contain add/save/delete. This audits the DECLARATION — see the header for why '
        . 'nothing currently reads it.',
    $aRows,
    'ANONYMOUS MAY'
);

$ctx->cleanup();

// =============================================================================
// Combined summary
// =============================================================================

echo "\n" . str_repeat('=', 96) . "\n";
printf(
    "ANONYMOUS DECLARED POLICY  clean=%d  unreviewed=%d  writable=%d  skipped=%d\n",
    $aPass, $aLeak, $aWrite, $aSkip
);
echo str_repeat('=', 96) . "\n";

exit(($sweepExit + $aLeak + $aWrite) > 0 ? 1 : 0);
