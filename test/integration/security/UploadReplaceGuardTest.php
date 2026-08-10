<?php
/**
 * Integration test — owner-less file overwrite is privileged-only.
 * ===============================================================
 *
 * The upload handlers (fileupload / common-upload / editor-upload) reuse an
 * existing Files row that carries the client-supplied name and overwrite its
 * bytes. iceUploadMayReplaceFile() gates that. An owner-less existing row
 * (settings asset, shared editor image) must be replaceable only by privileged
 * callers — a non-privileged caller posting its name must be refused — while
 * owner-scoped and brand-new uploads keep working.
 *
 * Run:  php test/integration/security/UploadReplaceGuardTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

// upload.auth.inc.php ends with an auth block that exits unless a valid $user is
// present. Provide one before including so the file only *defines* the helpers here.
$user = new stdClass();
$user->id = 1;
$user->email = 'guard-test@example.com';
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
function makeUser($level)
{
    $u = new stdClass();
    $u->id = 99;
    $u->email = 'x@example.com';
    $u->user_level = $level;
    return $u;
}

list($mgr, $sub, $other) = $ctx->ensureManagerFixture();

fwrite(STDOUT, "iceUploadMayReplaceFile — owner-less overwrite is privileged-only\n\n");

// Act as the subordinate employee (non-privileged).
$ctx->actAsRealUser($sub, 'Employee');
$emp = makeUser('Employee');

verdict(
    "Employee is DENIED overwriting an owner-less existing asset",
    iceUploadMayReplaceFile($emp, makeFile(500, $field, null)) === false
);
verdict(
    "Employee is DENIED overwriting another employee's file",
    iceUploadMayReplaceFile($emp, makeFile(501, $field, $other)) === false
);
verdict(
    "Employee MAY overwrite their own file",
    iceUploadMayReplaceFile($emp, makeFile(502, $field, $sub)) === true
);
verdict(
    "A brand-new row (no id) is always allowed",
    iceUploadMayReplaceFile($emp, makeFile(null, $field, null)) === true
);

// Privileged callers keep the settings / editor overwrite flows.
verdict(
    "Admin MAY overwrite an owner-less asset",
    iceUploadMayReplaceFile(makeUser('Admin'), makeFile(500, $field, null)) === true
);
verdict(
    "Manager MAY overwrite an owner-less asset",
    iceUploadMayReplaceFile(makeUser('Manager'), makeFile(500, $field, null)) === true
);

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("UPLOAD REPLACE GUARD  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
