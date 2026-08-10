<?php
/**
 * Integration test — forced password reset for legacy MD5 accounts.
 * ================================================================
 *
 * An account whose stored hash is still unsalted MD5 must be pushed through a
 * password upgrade before it can use the app: the shell serves the reset form
 * instead of the SPA, and service.php / data.php refuse everything except the reset
 * (and the email-recovery actions). This pins the decision function those gates use.
 *
 * The HTTP behaviour (shell serves the form, XHR is refused, reset upgrades the hash
 * and drops the session) is verified live — see the commit message.
 *
 * Run:  php test/integration/security/ForcedPasswordResetTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\PasswordManager;
use Utils\SessionUtils;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

function mkUser($hash)
{
    $u = new stdClass();
    $u->id = 4242;
    $u->username = 'legacy.user';
    $u->email = 'legacy@example.com';
    $u->user_level = 'Employee';
    $u->password = $hash;
    return $u;
}

$md5Hash = md5('Admin123$');
$bcryptHash = PasswordManager::createPasswordHash('Admin123$');

fwrite(STDOUT, "Forced password reset — legacy MD5 detection\n\n");

// --- isLegacyHash -----------------------------------------------------------
verdict('MD5 hash is recognised as legacy', PasswordManager::isLegacyHash($md5Hash) === true);
verdict('bcrypt hash is NOT legacy', PasswordManager::isLegacyHash($bcryptHash) === false);
verdict('empty/non-string is not legacy',
    PasswordManager::isLegacyHash('') === false && PasswordManager::isLegacyHash(null) === false);

// --- userNeedsPasswordReset -------------------------------------------------
PasswordManager::clearPasswordResetRequired();

verdict('an MD5 user needs a reset', PasswordManager::userNeedsPasswordReset(mkUser($md5Hash)) === true);
verdict('a bcrypt user does NOT need a reset',
    PasswordManager::userNeedsPasswordReset(mkUser($bcryptHash)) === false);
verdict('no user + no marker -> no reset', PasswordManager::userNeedsPasswordReset(null) === false);

// The session marker alone is enough (covers a session whose user object was
// refreshed but which authenticated against MD5 earlier in this request cycle).
SessionUtils::saveSessionString(PasswordManager::SESSION_RESET_REQUIRED, '1');
verdict('session marker alone forces a reset (even for a bcrypt user object)',
    PasswordManager::userNeedsPasswordReset(mkUser($bcryptHash)) === true);

PasswordManager::clearPasswordResetRequired();
verdict('clearing the marker releases the gate',
    PasswordManager::userNeedsPasswordReset(mkUser($bcryptHash)) === false);

// --- verifyPassword still behaves, and marks the session on an MD5 match ----
PasswordManager::clearPasswordResetRequired();
verdict('correct password against an MD5 hash still authenticates',
    PasswordManager::verifyPassword('Admin123$', $md5Hash) === true);
verdict('accepting an MD5 hash sets the session marker',
    SessionUtils::getSessionString(PasswordManager::SESSION_RESET_REQUIRED) === '1');

PasswordManager::clearPasswordResetRequired();
verdict('a WRONG password against an MD5 hash does not authenticate',
    PasswordManager::verifyPassword('nope', $md5Hash) === false);
verdict('a failed MD5 attempt does NOT set the marker',
    SessionUtils::getSessionString(PasswordManager::SESSION_RESET_REQUIRED) !== '1');

PasswordManager::clearPasswordResetRequired();
verdict('a bcrypt login does not set the marker',
    PasswordManager::verifyPassword('Admin123$', $bcryptHash) === true
    && SessionUtils::getSessionString(PasswordManager::SESSION_RESET_REQUIRED) !== '1');

PasswordManager::clearPasswordResetRequired();

echo "\n" . str_repeat('=', 64) . "\n";
printf("FORCED PASSWORD RESET  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
