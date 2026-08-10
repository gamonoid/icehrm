<?php
/**
 * Integration test — login does not leak account existence.
 * ========================================================
 *
 * PasswordManager::verifyPassword is reached with an empty stored hash when the
 * username/email does not exist. It used to return instantly (no bcrypt), so an
 * unknown account answered far faster than a real one — a timing oracle for valid
 * usernames. It now runs one bcrypt verify against a fixed dummy hash before
 * failing, so the empty-hash path costs about the same as a real wrong-password
 * attempt. This pins both the behaviour (always false, real hashes still work) and
 * that the empty-hash path actually does the bcrypt work.
 *
 * Run:  php test/integration/security/LoginTimingTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\PasswordManager;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

fwrite(STDOUT, "PasswordManager::verifyPassword — no existence timing oracle\n\n");

$real = PasswordManager::createPasswordHash('CorrectHorse1$');

// Behaviour.
verdict("correct password against a real hash succeeds", PasswordManager::verifyPassword('CorrectHorse1$', $real) === true);
verdict("wrong password against a real hash fails", PasswordManager::verifyPassword('nope', $real) === false);
verdict("empty stored hash (unknown account) fails", PasswordManager::verifyPassword('anything', '') === false);
verdict("null stored hash fails", PasswordManager::verifyPassword('anything', null) === false);

// Timing: the empty-hash path must still spend bcrypt time. A cost-13 bcrypt verify
// is tens to hundreds of ms; the old instant return was microseconds. Require the
// empty-hash path to be within the same order as a real wrong-password verify, and
// clearly above an instant return.
function timeMs($fn)
{
    $start = microtime(true);
    $fn();
    return (microtime(true) - $start) * 1000.0;
}

$realWrongMs = timeMs(function () use ($real) {
    PasswordManager::verifyPassword('nope', $real);
});
$emptyMs = timeMs(function () {
    PasswordManager::verifyPassword('nope', '');
});

printf("  (real wrong-password: %.1f ms, empty-hash: %.1f ms)\n", $realWrongMs, $emptyMs);

verdict("empty-hash path is not an instant return (does bcrypt work)", $emptyMs > 5.0);
verdict(
    "empty-hash path is the same order as a real verify (>= 40% of it)",
    $emptyMs >= 0.4 * $realWrongMs
);

echo "\n" . str_repeat('=', 64) . "\n";
printf("LOGIN TIMING  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
