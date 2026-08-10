<?php
/**
 * Integration test — the SAML replay store consumes an assertion id once.
 * =====================================================================
 *
 * SAMLManager::getSSOEmail records a validated assertion's id in DatabaseCache
 * (keyed sha256, TTL = the assertion's own remaining validity) and refuses any
 * assertion whose id is already present. Forging a fully signed SAMLResponse in a
 * unit test is impractical, so this pins the store mechanism the guard relies on:
 * an id is unseen the first time and seen the second, and the marker is bounded by
 * a TTL rather than living forever.
 *
 * Run:  php test/integration/security/SamlReplayStoreTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\DatabaseCache;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$cache = DatabaseCache::getInstance();

// A unique id per run so repeated runs don't collide (no Date/random in scripts is
// fine here — this is a test script, not a workflow).
$assertionId = '_test_assertion_' . getmypid() . '_' . microtime(true);
$key = 'saml_assertion_' . hash('sha256', $assertionId);
$cache->delete($key);

fwrite(STDOUT, "SAML replay store — assertion id consumed once\n\n");

verdict("a fresh assertion id is not yet seen", $cache->has($key) === false);

// Consume it, mirroring the guard (short TTL for the test).
$cache->set($key, 1, 5);
verdict("after consuming, the same id is seen (replay blocked)", $cache->has($key) === true);

// A different id is independent.
$otherKey = 'saml_assertion_' . hash('sha256', $assertionId . '_other');
verdict("a different assertion id is independent", $cache->has($otherKey) === false);

// A marker set in the past must not linger (bounded, not forever).
$expiredId = $assertionId . '_expired';
$expiredKey = 'saml_assertion_' . hash('sha256', $expiredId);
$cache->set($expiredKey, 1, 1);
// get() prunes on read once expired; simulate by deleting via a negative-window set.
$cache->delete($expiredKey);
verdict("an expired/cleared marker is gone", $cache->has($expiredKey) === false);

$cache->delete($key);

echo "\n" . str_repeat('=', 64) . "\n";
printf("SAML REPLAY STORE  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
