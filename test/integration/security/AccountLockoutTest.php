<?php
/**
 * Integration test — account-lockout logic (login brute-force / DoS controls).
 * ===========================================================================
 *
 * Two HIGH issues in PasswordManager, fixed together:
 *
 *   A. Permanent-lockout DoS — isAccountLocked() had no time component
 *      (last_wrong_attempt_at was written but never read), so five wrong passwords
 *      locked any known account FOREVER. Anyone could lock every admin anonymously.
 *      The lock is now time-boxed (LOCKOUT_WINDOW_MINUTES).
 *
 *   B. Brute-force after lockout — while locked, login.php recorded nothing, so the
 *      6-digit email login code could be guessed without limit. Failures are now
 *      recorded while locked, and past LOGIN_CODE_KILL_THRESHOLD an active code is
 *      invalidated (shouldInvalidateLoginCode()).
 *
 * This pins the decision functions directly (no DB writes, no HTTP): a User object
 * is populated with counter/timestamp values and the predicates are asserted.
 *
 * Run:  php test/integration/security/AccountLockoutTest.php
 * Exit 0 = all correct; 1 = any wrong verdict.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\PasswordManager;

$pass = 0; $fail = 0; $failures = array();
function check($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

function userWith($count, $lastAt, $loginHash = null)
{
    $u = new \Users\Common\Model\User();
    $u->id = 12345;
    $u->wrong_password_count = $count;
    $u->last_wrong_attempt_at = $lastAt;
    $u->login_hash = $loginHash;
    return $u;
}

$now = date('Y-m-d H:i:s');
$oldAt = date('Y-m-d H:i:s', time() - (PasswordManager::LOCKOUT_WINDOW_MINUTES + 5) * 60);
$recentAt = date('Y-m-d H:i:s', time() - 60);

fwrite(STDOUT, "PasswordManager lockout predicates\n\n");

// --- isAccountLocked: threshold ----------------------------------------------
check('below threshold is not locked',
    !PasswordManager::isAccountLocked(userWith(PasswordManager::MAX_FAILED_LOGIN_ATTEMPTS - 1, $now)));
check('at threshold with a recent failure is locked',
    PasswordManager::isAccountLocked(userWith(PasswordManager::MAX_FAILED_LOGIN_ATTEMPTS, $recentAt)));

// --- isAccountLocked: time decay (fixes the permanent-lockout DoS) ------------
check('at threshold but OUTSIDE the window is NOT locked (lock decays)',
    !PasswordManager::isAccountLocked(userWith(PasswordManager::MAX_FAILED_LOGIN_ATTEMPTS, $oldAt)));
check('at threshold with no recorded time stays locked (fail safe)',
    PasswordManager::isAccountLocked(userWith(PasswordManager::MAX_FAILED_LOGIN_ATTEMPTS, null)));

// --- login-code kill threshold (fixes code brute force) ----------------------
check('an active code is NOT killed just below the kill threshold',
    !PasswordManager::shouldInvalidateLoginCode(userWith(PasswordManager::LOGIN_CODE_KILL_THRESHOLD - 1, $recentAt)));
check('an active code IS killed at the kill threshold',
    PasswordManager::shouldInvalidateLoginCode(userWith(PasswordManager::LOGIN_CODE_KILL_THRESHOLD, $recentAt)));

// --- sanity on the constants themselves --------------------------------------
check('the code kill threshold is above the lock threshold',
    PasswordManager::LOGIN_CODE_KILL_THRESHOLD > PasswordManager::MAX_FAILED_LOGIN_ATTEMPTS);
check('the lockout window is positive',
    PasswordManager::LOCKOUT_WINDOW_MINUTES > 0);

echo "\n" . str_repeat('=', 64) . "\n";
printf("ACCOUNT LOCKOUT  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
