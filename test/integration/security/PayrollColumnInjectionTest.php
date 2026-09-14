<?php
/**
 * Integration test — payroll stored-column SQL injection is neutralized.
 * =====================================================================
 *
 * Admin-configured payroll column lists (salary_components, deductions,
 * add_columns, sub_columns, and the payroll's own `columns`) are stored as JSON
 * and expanded into "id IN (...)" clauses during payslip generation. They were
 * previously imploded verbatim; PayrollActionManager::safeIdInList now casts every
 * element to int. This pins that: any crafted element collapses to a bare integer,
 * so the output is only digits and commas — nothing that can break out of the IN
 * list — while legitimate id lists round-trip unchanged.
 *
 * Run:  php test/integration/security/PayrollColumnInjectionTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Payroll\Admin\Api\PayrollActionManager;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$am = new PayrollActionManager();
$ref = new ReflectionMethod('Payroll\\Admin\\Api\\PayrollActionManager', 'safeIdInList');
$ref->setAccessible(true);
$call = function ($json) use ($ref, $am) {
    return $ref->invoke($am, $json);
};

fwrite(STDOUT, "PayrollActionManager::safeIdInList — injection neutralized\n\n");

// Legitimate id lists are preserved.
verdict("plain id list round-trips", $call(json_encode(array(1, 2, 3))) === '1,2,3');
verdict("numeric-string ids round-trip", $call(json_encode(array('4', '5'))) === '4,5');

// Empty / malformed inputs collapse to a no-match sentinel, never empty SQL.
verdict("empty array -> '0'", $call(json_encode(array())) === '0');
verdict("null / non-array -> '0'", $call('null') === '0' && $call('') === '0');

// Injection attempts collapse to bare integers (intval of the leading number, or 0).
$inj = $call(json_encode(array('1) OR SLEEP(5)-- -')));
verdict("injection element -> leading int only", $inj === '1');

$inj2 = $call(json_encode(array('0 UNION SELECT password FROM Users', '2')));
verdict("union attempt -> digits/commas only", $inj2 === '0,2');

$out = $call(json_encode(array('1', 'abc', '3; DROP TABLE Users')));
verdict(
    "output contains only digits and commas",
    preg_match('/^[0-9,]*$/', $out) === 1
);

echo "\n" . str_repeat('=', 64) . "\n";
printf("PAYROLL COLUMN INJECTION  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
