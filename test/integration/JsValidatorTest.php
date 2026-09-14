<?php
/**
 * Utils\Js\JsValidator — the "validate function" check behind the payroll-column editor.
 *
 * The point of this component is the MESSAGE, not the verdict. The previous check ran
 * the script and reported whether the result was non-empty, so a typo, an unsupported
 * construct, a misspelled variable and a crashed node process were indistinguishable:
 * all of them said "invalid". These tests therefore assert on what the author is told
 * — the kind of problem, the line, and the specific names involved — not just on
 * valid/invalid.
 *
 * Run:  bash test/integration/run.sh JsValidatorTest
 */

require __DIR__ . '/bootstrap.php';

use Utils\Js\JsError;
use Utils\Js\JsSandbox;

$pass = 0;
$fail = 0;

function ok($label)
{
    global $pass;
    $pass++;
    echo "  PASS  $label\n";
}

function bad($label, $detail = '')
{
    global $fail;
    $fail++;
    echo "  FAIL  $label" . ($detail === '' ? '' : "  ($detail)") . "\n";
}

/** The script validates cleanly and produces $expectedResult. */
function accepts($label, $script, $expectedResult, $vars = array())
{
    $r = JsSandbox::validate($script, $vars);
    if ($r['valid'] && $r['result'] === $expectedResult) {
        ok($label . '  ->  ' . $r['result']);
        return;
    }
    bad($label, $r['valid']
        ? "valid but result '{$r['result']}' != '$expectedResult'"
        : 'rejected: ' . $r['error']);
}

/**
 * The script is rejected. $expect is a map of report keys to expected values;
 * 'error' is matched as a substring (case-insensitive), everything else exactly.
 */
function rejects($label, $script, $expect, $vars = array())
{
    $r = JsSandbox::validate($script, $vars);
    if ($r['valid']) {
        bad($label, 'was accepted, result ' . var_export($r['result'], true));
        return;
    }
    foreach ($expect as $key => $want) {
        $got = $r[$key];
        if ($key === 'error') {
            if (stripos((string) $got, $want) === false) {
                bad($label, "message '$got' does not mention '$want'");
                return;
            }
            continue;
        }
        if ($got !== $want) {
            bad($label, "$key was " . var_export($got, true) . ', expected ' . var_export($want, true));
            return;
        }
    }
    ok($label . '  ->  ' . $r['error']);
}

echo "\nJsValidator — author-facing validation\n";
echo str_repeat('=', 64) . "\n";

$vars = array('gpssaBase' => 5000, 'gpssaRate' => 0.05);

// ---------------------------------------------------------------------------
echo "\n[1] Valid functions are accepted\n";
// ---------------------------------------------------------------------------
accepts('the stored GPSSA function',
    "var base = Number(gpssaBase)||0;\n"
    . "var rate = parseFloat(gpssaRate)||0;\n"
    . "Math.round(base * rate * 100) / 100;", '250', $vars);

accepts('a function declared and called',
    "function fun1(b, r) { return Math.round(b * r * 100) / 100; }\n"
    . "fun1(Number(gpssaBase)||0, parseFloat(gpssaRate)||0);", '250', $vars);

accepts('let / const / Math.pow', "let b = Number(gpssaBase)||0;\n"
    . "const r = parseFloat(gpssaRate)||0;\n"
    . "function f(x, y) { let k = Math.pow(10, 3); return k + Math.round(x * y * 100) / 100; }\n"
    . "f(b, r);", '1250', $vars);

accepts('a script with no variables at all', '2 + 3;', '5');
accepts('zero is a valid result, not "empty"', '0;', '0');

$report = JsSandbox::validate(
    "function a(x) { return x; }\nfunction b(x) { return a(x); }\nb(1);",
    array()
);
if ($report['functions'] === array('a', 'b')) {
    ok('declared functions are reported  ->  ' . implode(', ', $report['functions']));
} else {
    bad('declared functions are reported', 'got ' . var_export($report['functions'], true));
}

// ---------------------------------------------------------------------------
echo "\n[2] Syntax errors name the problem and the line\n";
// ---------------------------------------------------------------------------
rejects('missing closing brace',
    "function f(x) {\n  return x * 2;\n",
    array('kind' => JsError::KIND_SYNTAX, 'error' => 'unclosed', 'line' => 3));

rejects('missing closing parenthesis',
    "var a = Math.round(1.5;\n",
    array('kind' => JsError::KIND_SYNTAX, 'error' => 'Expected ")"', 'line' => 1));

rejects('stray operator',
    "var a = ;\n",
    array('kind' => JsError::KIND_SYNTAX, 'line' => 1));

rejects('reports the line of a later error',
    "var a = 1;\nvar b = 2;\nvar c = ;\n",
    array('kind' => JsError::KIND_SYNTAX, 'line' => 3));

rejects('unterminated string',
    "var a = 'abc;\n",
    array('kind' => JsError::KIND_SYNTAX, 'error' => 'Unterminated string', 'line' => 1));

rejects('a reserved word used as a variable name',
    "var class = 1;",
    array('kind' => JsError::KIND_SYNTAX, 'error' => 'reserved word', 'line' => 1));

rejects('an empty function is rejected clearly',
    "   ",
    array('error' => 'empty'));

// ---------------------------------------------------------------------------
echo "\n[3] Unsupported constructs say what was used and that the subset is limited\n";
// ---------------------------------------------------------------------------
rejects('arrow function',
    "var f = (x) => x * 2;\nf(1);",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'arrow'));

rejects('new',
    "var d = new Object();",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'new'));

rejects('class',
    "class Foo {}",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'class'));

rejects('try/catch',
    "try { 1; } catch (e) { 2; }",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'try/catch'));

rejects('template literal',
    "var s = `x`;",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'Template'));

rejects('for-of loop',
    "var xs = [1]; for (var x of xs) { x; }",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'for-in / for-of'));

rejects('unsupported errors explain the restricted subset',
    "this;",
    array('kind' => JsError::KIND_UNSUPPORTED, 'error' => 'restricted subset'));

// ---------------------------------------------------------------------------
echo "\n[4] Unknown variables — the failure that used to be invisible\n";
// ---------------------------------------------------------------------------
// This script is valid JavaScript and RUNS. `gpssRate` is undefined, `|| 0` turns it
// into 0, and the column silently computes 0 instead of the contribution. Nothing at
// runtime complains, which is exactly why validation has to.
rejects('a misspelled variable is caught',
    "var base = Number(gpssaBase)||0;\n"
    . "var rate = parseFloat(gpssRate)||0;\n"
    . "Math.round(base * rate * 100) / 100;",
    array('kind' => 'undefined-variable', 'error' => 'gpssRate',
          'undefinedVariables' => array('gpssRate')),
    $vars);

$r = JsSandbox::validate(
    "var rate = parseFloat(gpssRate)||0;\nrate;", $vars);
if (stripos($r['error'], 'did you mean "gpssaRate"') !== false) {
    ok('a near-miss suggests the intended name  ->  ' . $r['error']);
} else {
    bad('a near-miss suggests the intended name', 'got: ' . $r['error']);
}

$r = JsSandbox::validate("somethingEntirelyDifferent;", $vars);
if (stripos($r['error'], 'did you mean') === false) {
    ok('an unrelated name does not get a misleading suggestion');
} else {
    bad('an unrelated name does not get a misleading suggestion', 'got: ' . $r['error']);
}

$r = JsSandbox::validate("unknownOne + unknownTwo;", $vars);
if ($r['undefinedVariables'] === array('unknownOne', 'unknownTwo')
    && stripos($r['error'], 'Available: gpssaBase, gpssaRate') !== false) {
    ok('every unknown name is listed, along with what IS available');
} else {
    bad('every unknown name is listed, along with what IS available',
        var_export($r['undefinedVariables'], true) . ' / ' . $r['error']);
}

// Names the script itself defines are not "unknown".
accepts('locals, params and functions are not reported as unknown',
    "var a = 1;\n"
    . "let b = 2;\n"
    . "const c = 3;\n"
    . "function f(p, q) { var inner = p + q; return inner; }\n"
    . "f(a, b) + c;", '6');

accepts('a loop counter is not reported as unknown',
    "var t = 0; for (var i = 0; i < 3; i++) { t += i; } t;", '3');

accepts('typeof on an unbound name is an existence check, not an error',
    "typeof maybeMissing === 'undefined' ? 'absent' : 'present';", 'absent');

accepts('assigning to a new name declares it',
    "total = 5; total * 2;", '10');

// ---------------------------------------------------------------------------
echo "\n[5] Runtime problems are explained, not just failed\n";
// ---------------------------------------------------------------------------
rejects('calling something that is not a function',
    "var x = 5;\nx();",
    array('kind' => JsError::KIND_RUNTIME, 'error' => 'not a function'));

rejects('reading a property of undefined',
    "var o;\no.total;",
    array('kind' => JsError::KIND_RUNTIME, 'error' => 'undefined'));

rejects('an unsupported method on a string',
    "'abc'.toTitleCase();",
    array('kind' => JsError::KIND_RUNTIME, 'error' => 'not supported'));

// Recursion is a bound on execution, like the step and depth budgets, so it reports
// as a limit rather than as a mistake in the author's logic.
rejects('recursion is refused with the function named',
    "function f(n) { return n <= 0 ? 0 : f(n - 1); }\nf(3);",
    array('kind' => JsError::KIND_LIMIT, 'error' => 'Recursion is not allowed'));

rejects('an infinite loop is stopped',
    "while (true) { }",
    array('kind' => JsError::KIND_LIMIT, 'error' => 'exceeded'));

rejects('reassigning a const',
    "const rate = 0.05;\nrate = 0.06;\nrate;",
    array('error' => 'constant'));

// ---------------------------------------------------------------------------
echo "\n[6] The result itself is sanity-checked\n";
// ---------------------------------------------------------------------------
rejects('a script that produces no value is explained',
    "function f() { return 1; }",
    array('error' => 'did not produce a value'));

rejects('a NaN result is explained',
    "Number('abc') * 2;",
    array('error' => 'NaN'));

rejects('a division by zero is explained',
    "var d = 0;\n1 / d;",
    array('error' => 'Infinity'));

$r = JsSandbox::validate("'some text';");
if ($r['valid'] && $r['isNumeric'] === false) {
    ok('a non-numeric result is allowed but flagged as not numeric');
} else {
    bad('a non-numeric result is allowed but flagged as not numeric',
        'valid=' . var_export($r['valid'], true) . ' isNumeric=' . var_export($r['isNumeric'], true));
}

// ---------------------------------------------------------------------------
echo "\n[7] Validation never throws, whatever it is given\n";
// ---------------------------------------------------------------------------
// The editor calls this with whatever is in the textarea, so it has to survive
// anything without turning into a 500.
$hostile = array(
    'empty string' => '',
    'only whitespace' => "\n\t  \n",
    'only a comment' => '// nothing here',
    'unbalanced braces' => '}}}{{{',
    'binary junk' => "\x00\x01\xff\xfe",
    'a very long line' => str_repeat('1+', 5000) . '1',
    'deep nesting' => str_repeat('(', 5000) . '1' . str_repeat(')', 5000),
    'huge allocation' => '"x".repeat(999999999)',
    'null bytes in a string' => "var a = 'x\x00y'; a;",
);
foreach ($hostile as $label => $script) {
    try {
        $r = JsSandbox::validate($script, $vars);
        if (is_array($r) && array_key_exists('valid', $r)) {
            ok('survives: ' . $label . ($r['valid'] ? ' (accepted)' : ' -> ' . $r['error']));
        } else {
            bad('survives: ' . $label, 'malformed report');
        }
    } catch (\Throwable $e) {
        bad('survives: ' . $label, 'threw ' . get_class($e) . ': ' . $e->getMessage());
    }
}

echo "\n" . str_repeat('=', 64) . "\n";
echo "JS VALIDATOR  PASS=$pass  FAIL=$fail\n";
echo str_repeat('=', 64) . "\n";
exit($fail === 0 ? 0 : 1);
