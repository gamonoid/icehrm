<?php
/**
 * Integration test — managers are team-scoped in the upload handlers.
 * ===================================================================
 *
 * github.com/gamonoid/icehrm/discussions/386, F-02. iceUploadMayActOnEmployee() and
 * iceUploadMayReplaceFile() treated every Manager / Restricted Manager as globally
 * privileged, so a manager could attach a file to - or overwrite the bytes of a file
 * owned by - ANY employee by naming them in the request, while the rest of the app
 * (BaseService scoping, PermissionManager::manipulationAllowed) confines managers to
 * their subordinates. The gate must apply the same rule.
 *
 * Fixture: a manager, one direct subordinate, one unrelated employee.
 *
 * Run:  php test/integration/security/UploadManagerScopeTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

// upload.auth.inc.php ends with an auth block that exits unless a valid $user is set.
$user = new stdClass();
$user->id = 1;
$user->email = 'scope-test@example.com';
$user->user_level = 'Admin';
require_once dirname(__DIR__, 2) . '/../core/upload.auth.inc.php';

$ctx = new TestContext();

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$field = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;

function makeFile($id, $ownerField, $ownerId)
{
    $f = new stdClass();
    $f->id = $id;
    $f->$ownerField = $ownerId;
    return $f;
}

list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

foreach (array('Manager', 'Restricted Manager') as $level) {
    fwrite(STDOUT, "\n$level acting through a real Users row (employee $mgr; subordinate $sub; unrelated $other)\n\n");
    $u = $ctx->actAsRealUser($mgr, $level);

    // --- iceUploadMayActOnEmployee: the ?user=<id> the upload is filed under ---
    verdict("$level MAY upload for themselves",
        iceUploadMayActOnEmployee($u, $mgr) === true);
    verdict("$level MAY upload for a direct subordinate",
        iceUploadMayActOnEmployee($u, $sub) === true);
    verdict("$level MAY upload for a direct subordinate (id given as string)",
        iceUploadMayActOnEmployee($u, (string) $sub) === true);
    verdict("$level is DENIED uploading for an unrelated employee",
        iceUploadMayActOnEmployee($u, $other) === false);
    verdict("$level MAY upload an owner-less file (_NONE_)",
        iceUploadMayActOnEmployee($u, '_NONE_') === true);

    // --- iceUploadMayReplaceFile: overwriting the row that already has the name ---
    verdict("$level MAY overwrite their own existing file",
        iceUploadMayReplaceFile($u, makeFile(601, $field, $mgr)) === true);
    verdict("$level MAY overwrite a direct subordinate's existing file",
        iceUploadMayReplaceFile($u, makeFile(602, $field, $sub)) === true);
    verdict("$level is DENIED overwriting an unrelated employee's existing file",
        iceUploadMayReplaceFile($u, makeFile(603, $field, $other)) === false);
    verdict("$level MAY overwrite an owner-less existing asset (editor/settings flows)",
        iceUploadMayReplaceFile($u, makeFile(604, $field, null)) === true);
    verdict("$level MAY write a brand-new row",
        iceUploadMayReplaceFile($u, makeFile(null, $field, null)) === true);
}

// --- Admin is unchanged: unrestricted ---
fwrite(STDOUT, "\nAdmin (unrestricted)\n\n");
$admin = $ctx->actAsRealUser($mgr, 'Admin');
verdict("Admin MAY upload for an unrelated employee",
    iceUploadMayActOnEmployee($admin, $other) === true);
verdict("Admin MAY overwrite an unrelated employee's existing file",
    iceUploadMayReplaceFile($admin, makeFile(605, $field, $other)) === true);

// --- Employee is unchanged: self only ---
fwrite(STDOUT, "\nEmployee (self only)\n\n");
$emp = $ctx->actAsRealUser($sub, 'Employee');
verdict("Employee MAY upload for themselves",
    iceUploadMayActOnEmployee($emp, $sub) === true);
verdict("Employee is DENIED uploading for their own manager",
    iceUploadMayActOnEmployee($emp, $mgr) === false);
verdict("Employee is DENIED uploading for an unrelated employee",
    iceUploadMayActOnEmployee($emp, $other) === false);
verdict("Employee is DENIED overwriting an owner-less existing asset",
    iceUploadMayReplaceFile($emp, makeFile(606, $field, null)) === false);

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("UPLOAD MANAGER SCOPE  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
