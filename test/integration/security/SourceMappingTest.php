<?php
/**
 * Integration test — the `sm` source-mapping column allowlist.
 * ===========================================================
 *
 * BaseService::populateMappingItem() resolves a client-supplied mapping triple
 * [Model, lookupCol, displayCol] against another model's rows. Both column names
 * come from the request, so before the fix a mapping to ["User","employee",
 * "password"] read bcrypt hashes, and an injection fragment in the lookup column
 * reached the WHERE string.
 *
 * The fix requires every requested column to be a published fieldValueFields()
 * entry of the target model. This test calls populateMappingItem() directly (it is
 * public) and asserts:
 *
 *   - a sensitive column (User.password) is NOT copied onto the item;
 *   - an injection fragment as the lookup column is ignored (no crash, no change);
 *   - a legitimate mapping (Country.name) still resolves.
 *
 * Directly exercising the resolver keeps this fast and independent of HTTP; the
 * end-to-end block is in e2e/tests/security-sm-mapping.spec.js.
 *
 * Run:  php test/integration/security/SourceMappingTest.php
 * Exit 0 = all correct; 1 = any leak or broken legitimate mapping.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$bs = BaseService::getInstance();
$pass = 0;
$fail = 0;
$failures = array();

function check($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) {
        printf("  PASS  %s\n", $desc);
        $pass++;
    } else {
        printf("  FAIL  %s\n", $desc);
        $fail++;
        $failures[] = $desc;
    }
}

fwrite(STDOUT, "BaseService::populateMappingItem — column allowlist\n\n");

// A user with a real password hash to try to steal, and a country to resolve.
$db = BaseService::getInstance()->getDB();
$userRow = $db->Execute("SELECT employee FROM Users WHERE password IS NOT NULL AND password <> '' LIMIT 1");
$anEmployee = (is_array($userRow) && !empty($userRow)) ? $userRow[0]['employee'] : 1;

$countryRow = $db->Execute("SELECT id FROM Country LIMIT 1");
$aCountry = (is_array($countryRow) && !empty($countryRow)) ? $countryRow[0]['id'] : 1;

// --- 1. Sensitive column read must be denied ---------------------------------
$item = (object) array('employee' => $anEmployee);
$bs->populateMappingItem($item, array('employee' => array('User', 'employee', 'password')));
check(
    'mapping employee -> User.password does NOT copy a bcrypt hash',
    strpos((string) $item->employee, '$2') !== 0
);
check(
    'the employee field is left as its original id',
    (string) $item->employee === (string) $anEmployee
);

// --- 2. Injection fragment as the lookup column must be ignored ---------------
$item2 = (object) array('employee' => $anEmployee);
$threw = false;
try {
    $bs->populateMappingItem($item2, array('employee' => array('User', "1=1 OR ('a'='a", 'password')));
} catch (\Throwable $e) {
    $threw = true;
}
check('an injection fragment in the lookup column does not throw (it is skipped)', !$threw);
check('and does not alter the item', (string) $item2->employee === (string) $anEmployee);

// --- 3. A legitimate mapping still resolves ----------------------------------
// Country publishes id/name/code via fieldValueFields, so id -> name must work.
$item3 = (object) array('country' => $aCountry);
$bs->populateMappingItem($item3, array('country' => array('Country', 'id', 'name')));
check(
    'legitimate mapping country -> Country.name resolves to a non-numeric value',
    isset($item3->country) && !is_numeric($item3->country) && $item3->country !== ''
);

echo "\n" . str_repeat('=', 64) . "\n";
printf("SOURCE MAPPING ALLOWLIST  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
