<?php
/**
 * Integration test — file-download authorization (service.php?a=file).
 * ===================================================================
 *
 * a=file resolves a Files.name to a signed download URL. It used to trust that
 * possessing the name implied authorization, so any employee could mint a link for
 * anyone's HR document or the guessable-named employee-export reports.
 *
 * BaseService::currentUserCanAccessFile() restores the gate. This test drives that
 * decision function directly across every case, deterministically (no dependence on
 * which files happen to exist in the fixture); the HTTP block is verified live in
 * the audit probes.
 *
 *   Admin                       -> any file
 *   owner themselves            -> allowed
 *   a different employee        -> denied
 *   manager over the owner      -> allowed
 *   manager NOT over the owner  -> denied
 *   owner-less file, non-admin  -> denied (reports / PII exports live here)
 *   owner-less file, admin      -> allowed
 *
 * Run:  php test/integration/security/FileAccessTest.php
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

/** A throwaway File row object with a given owner (not persisted — the check reads
 *  ->employee off the object). */
function fileOwnedBy($ownerId)
{
    $f = new \Model\File();
    $f->id = 999999;
    $f->employee = $ownerId;
    $f->filename = 'probe.pdf';
    return $f;
}

// Fixture: a manager with one direct subordinate and one unrelated employee.
list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

fwrite(STDOUT, "BaseService::currentUserCanAccessFile — authorization matrix\n\n");

// --- Admin sees everything ----------------------------------------------------
$ctx->actAsRealUser($mgr, 'Admin');
verdict('Admin may access an owned file',      $bs->currentUserCanAccessFile(fileOwnedBy($other)));
verdict('Admin may access an owner-less file', $bs->currentUserCanAccessFile(fileOwnedBy(null)));

// --- Employee: own file yes, others no ---------------------------------------
$ctx->actAsRealUser($sub, 'Employee');
verdict('Employee may access their OWN file',           $bs->currentUserCanAccessFile(fileOwnedBy($sub)));
verdict('Employee may NOT access another employee\'s',  !$bs->currentUserCanAccessFile(fileOwnedBy($other)));
verdict('Employee may NOT access an owner-less file',   !$bs->currentUserCanAccessFile(fileOwnedBy(null)));
verdict('Employee may NOT access an owner-less (0) file', !$bs->currentUserCanAccessFile(fileOwnedBy(0)));

// --- Manager: subordinate yes, non-subordinate no ----------------------------
$ctx->actAsRealUser($mgr, 'Manager');
verdict('Manager may access their SUBORDINATE\'s file',      $bs->currentUserCanAccessFile(fileOwnedBy($sub)));
verdict('Manager may NOT access a NON-subordinate\'s file',  !$bs->currentUserCanAccessFile(fileOwnedBy($other)));
verdict('Manager may NOT access an owner-less file',         !$bs->currentUserCanAccessFile(fileOwnedBy(null)));

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("FILE ACCESS AUTHORIZATION  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
