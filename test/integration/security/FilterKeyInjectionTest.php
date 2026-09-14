<?php
/**
 * Integration test — filter-key SQL injection (buildDefaultFilterQuery).
 * =====================================================================
 *
 * BaseService::buildDefaultFilterQuery() concatenates each filter KEY into the WHERE
 * string (values are bound). data.php runs filter keys through
 * DomainAwareInputCleaner, but the REST path (api-rest.php -> DataReader -> getData
 * -> buildDefaultFilterQuery) does not, so a request like
 * ?filters={"1=1 OR (subquery)":"1"} reached SQL with the key unsanitised — CWE-89.
 *
 * The fix rejects any filter key that is not a plain SQL identifier, at the point of
 * concatenation (so every caller is covered). This test inspects the GENERATED query
 * fragment directly — the unambiguous oracle — asserting a malicious key is dropped
 * and a legitimate column key is kept.
 *
 * Run:  php test/integration/security/FilterKeyInjectionTest.php
 * Exit 0 = correct; 1 = a malicious key survived or a legit key was dropped.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$bs = BaseService::getInstance();
$pass = 0; $fail = 0; $failures = array();
function check($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

fwrite(STDOUT, "BaseService::buildDefaultFilterQuery — filter key sanitisation\n\n");

// A legitimate column-name key must be applied.
list($query, $data) = $bs->buildDefaultFilterQuery(array('employee' => '147'));
check('a legitimate key "employee" is included in the query', strpos($query, 'employee') !== false);
check('its value is bound (a placeholder is present)', strpos($query, '?') !== false);
check('the bound value is the supplied one', in_array('147', $data, true));

// An injection key must be dropped entirely — none of its SQL reaches the query.
$evilKey = "1=1) OR (SELECT password FROM Users LIMIT 1)='x";
list($q2, $d2) = $bs->buildDefaultFilterQuery(array($evilKey => '1'));
check('the injection key does not appear in the query', strpos($q2, 'SELECT') === false && strpos($q2, 'OR (') === false);
check('no fragment of the injection key leaks into the query', strpos($q2, '1=1)') === false);
check('the query is effectively empty (only a rejected key was supplied)', trim($q2) === '');

// A key with spaces / operators is rejected; a valid identifier with digits is kept.
list($q3) = $bs->buildDefaultFilterQuery(array('address1' => 'x'));
check('a valid identifier with digits ("address1") is kept', strpos($q3, 'address1') !== false);
list($q4) = $bs->buildDefaultFilterQuery(array('status = 1 OR 1' => 'x'));
check('a key containing spaces/operators is rejected', trim($q4) === '');

echo "\n" . str_repeat('=', 64) . "\n";
printf("FILTER KEY INJECTION  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
