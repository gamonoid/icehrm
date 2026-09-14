<?php
/**
 * Utils\Js\JsSandbox — the PHP JavaScript sandbox that replaced node + vm2.
 *
 * Three things are checked:
 *
 *   1. SEMANTICS. The subset must behave like real JavaScript, because the stored
 *      payroll functions were written against JS and their results are money. The
 *      cases that matter are where PHP and JS disagree: Math.round on negative
 *      halves, `||` returning an operand, Number('') === 0, fmod-style %, division
 *      by zero, and shortest-round-trip number printing.
 *   2. THE SUPPORTED SURFACE. Functions, calls, arithmetic, Math.*, arrays and
 *      string methods work.
 *   3. THE GUARANTEES. No recursion (direct, indirect, or via a callback), no host
 *      reachability, and every budget terminates.
 *
 * Run:  bash test/integration/run.sh JsSandboxTest
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

/** The script must evaluate to exactly $expected, compared as the string the app sees. */
function is_result($label, $script, $expected, $vars = array())
{
    try {
        $actual = JsSandbox::evaluateToString($script, $vars);
    } catch (JsError $e) {
        bad($label, 'threw: ' . $e->getMessage());
        return;
    } catch (\Throwable $e) {
        bad($label, 'PHP error: ' . get_class($e) . ' ' . $e->getMessage());
        return;
    }
    if ($actual === $expected) {
        ok($label . '  ->  ' . $actual);
    } else {
        bad($label, "expected '$expected', got '$actual'");
    }
}

/** The script must be refused, with $needle somewhere in the message. */
function is_refused($label, $script, $needle = '', $vars = array())
{
    try {
        $actual = JsSandbox::evaluateToString($script, $vars);
    } catch (JsError $e) {
        if ($needle === '' || stripos($e->getMessage(), $needle) !== false) {
            ok($label . '  ->  refused: ' . $e->getMessage());
        } else {
            bad($label, "refused but for the wrong reason: " . $e->getMessage());
        }
        return;
    } catch (\Throwable $e) {
        bad($label, 'leaked a PHP error instead of JsError: ' . get_class($e) . ' ' . $e->getMessage());
        return;
    }
    bad($label, "was NOT refused, returned '$actual'");
}

echo "\nJsSandbox — PHP JavaScript sandbox\n";
echo str_repeat('=', 64) . "\n";

// ---------------------------------------------------------------------------
echo "\n[1] The real payroll functions in the database\n";
// ---------------------------------------------------------------------------
$gpssa = "var base = Number(gpssaBase)||0;\n"
    . "var rate = parseFloat(gpssaRate)||0;\n"
    . "Math.round(base * rate * 100) / 100;";

is_result('GPSSA contribution, int inputs', $gpssa, '25000',
    array('gpssaBase' => 5000, 'gpssaRate' => 5));
is_result('GPSSA contribution, string inputs', $gpssa, '25000',
    array('gpssaBase' => '5000', 'gpssaRate' => '5'));
// 1234 * 0.0725 is 89.46499999999999 in IEEE-754, so *100 lands below the .5
// boundary and rounds DOWN. Pinning the real figure, not the decimal ideal.
is_result('GPSSA contribution, fractional rate', $gpssa, '89.46',
    array('gpssaBase' => 1234, 'gpssaRate' => 0.0725));
is_result('GPSSA contribution, missing variables', $gpssa, '0', array());

is_result(
    'Overtime pay',
    "var hourly = (Number(basic)||0) / 30 / 8;\n"
    . "var pay = hourly * 1.25 * (Number(otNormal)||0) + hourly * 1.5 * (Number(otPremium)||0);\n"
    . "Math.round(pay * 100) / 100;",
    '400',
    array('basic' => 12000, 'otNormal' => 4, 'otPremium' => 2)
);

is_result(
    'EOSB monthly accrual',
    "var days = parseFloat(eosbDays)||0;\n"
    . "var b = Number(basic)||0;\n"
    . "Math.round(((b / 30) * days / 12) * 100) / 100;",
    '577.79',
    array('eosbDays' => 21, 'basic' => 9905)
);

is_result('Bare identifier as the whole function', 'gpssa_employee_rate', '0.05',
    array('gpssa_employee_rate' => 0.05));

// ---------------------------------------------------------------------------
echo "\n[2] Where PHP and JavaScript disagree\n";
// ---------------------------------------------------------------------------
// PHP round() is half-away-from-zero; JS Math.round is half-UP toward +Infinity.
is_result('Math.round(2.5)', 'Math.round(2.5)', '3');
is_result('Math.round(-2.5) is -2, not -3', 'Math.round(-2.5)', '-2');
is_result('Math.round(-0.5) is 0, not -1', 'Math.round(-0.5)', '0');

// || returns the OPERAND, not a boolean — every stored formula relies on this.
is_result('0 || 7 yields the operand', '0 || 7', '7');
is_result('"a" || "b" short-circuits', '"a" || "b"', 'a');
is_result('null || "fallback"', 'null || "fallback"', 'fallback');

is_result("Number('') is 0", "Number('')", '0');
is_result("Number('  ') is 0", "Number('  ')", '0');
is_result("Number('abc') is NaN", "Number('abc')", 'NaN');
is_result('Number(null) is 0', 'Number(null)', '0');
is_result('Number(undefined) is NaN', 'Number(undefined)', 'NaN');

// % follows the dividend's sign (fmod), unlike PHP's integer modulo on floats.
is_result('-7 % 3 is -1', '-7 % 3', '-1');
is_result('7 % -3 is 1', '7 % -3', '1');
is_result('5.5 % 2 is 1.5', '5.5 % 2', '1.5');

// Division by zero yields Infinity/NaN; PHP 8 would raise DivisionByZeroError.
is_result('1/0 is Infinity', '1/0', 'Infinity');
is_result('-1/0 is -Infinity', '-1/0', '-Infinity');
is_result('0/0 is NaN', '0/0', 'NaN');

// Shortest round-tripping form, as JS prints it — not PHP's precision setting.
is_result('0.1 + 0.2 prints all the digits', '0.1 + 0.2', '0.30000000000000004');
is_result('Large integers print without exponent', '9007199254740991', '9007199254740991');
is_result('String concatenation beats addition', '1 + "2"', '12');
is_result('Numeric addition when both are numbers', '1 + 2', '3');
is_result('NaN !== NaN', 'NaN === NaN', 'false');

// ---------------------------------------------------------------------------
echo "\n[3] The requested language surface\n";
// ---------------------------------------------------------------------------
is_result('function declaration and call',
    'function add(a, b) { return a + b; } add(2, 3);', '5');
is_result('function expression assigned to a variable',
    'var f = function (x) { return x * 2; }; f(21);', '42');
is_result('function declared after use (hoisting)',
    'var r = twice(4); function twice(n) { return n * 2; } r;', '8');
is_result('closure captures the defining scope',
    'var m = 3; function scale(x) { return x * m; } scale(5);', '15');
is_result('nested calls, not recursion',
    'function a(x){return x+1;} function b(x){return a(x)*2;} b(4);', '10');

is_result('arithmetic incl. modulo', '(2 + 3) * 4 / 2 % 7', '3');
is_result('Math functions', 'Math.max(Math.floor(3.9), Math.abs(-2), Math.sqrt(16))', '4');
is_result('Math.pow and Math.min', 'Math.min(Math.pow(2, 10), 2000)', '1024');

is_result('array literal, length and index', 'var a = [10, 20, 30]; a.length + a[1];', '23');
is_result('array push/pop mutate in place', 'var a = [1]; a.push(2, 3); a.pop(); a.join("-");', '1-2');
is_result('array map with a function', '[1,2,3].map(function (n) { return n * n; }).join(",");', '1,4,9');
is_result('array filter + reduce',
    '[1,2,3,4,5].filter(function(n){return n % 2 === 1;}).reduce(function(a,b){return a+b;}, 0);', '9');
is_result('array reference semantics', 'var a=[1]; var b=a; b.push(2); a.length;', '2');
is_result('array sort with comparator',
    '[3,1,2].sort(function(a,b){return a-b;}).join(",");', '1,2,3');

is_result('string methods', '" Hello World ".trim().toUpperCase().replace("WORLD","THERE");',
    'HELLO THERE');
is_result('string split/slice/indexOf',
    'var s="a,b,c"; s.split(",")[1] + s.slice(-1) + s.indexOf("b");', 'bc2');
is_result('string padStart and repeat', '"7".padStart(3, "0") + "-".repeat(2);', '007--');
is_result('number toFixed', '(3.14159).toFixed(2)', '3.14');

is_result('for loop with accumulator',
    'var t = 0; for (var i = 1; i <= 10; i++) { t += i; } t;', '55');
is_result('while loop with break',
    'var i = 0; while (true) { i++; if (i > 4) { break; } } i;', '5');
is_result('if/else and ternary',
    'var x = 5; var r = x > 3 ? "big" : "small"; if (x === 5) { r = r + "!"; } r;', 'big!');
is_result('object literal and property access',
    'var o = { rate: 0.05, name: "gpssa" }; o.name + ":" + o.rate;', 'gpssa:0.05');

// ---------------------------------------------------------------------------
echo "\n[3b] Author-supplied samples (verbatim, as a user would paste them)\n";
// ---------------------------------------------------------------------------
// gpssaBase = 5000, gpssaRate = 0.05  ->  the contribution is 250.
$sampleVars = array('gpssaBase' => 5000, 'gpssaRate' => 0.05);

is_result(
    'sample 1: bare expression result',
    "var base = Number(gpssaBase)||0;\n"
    . "var rate = parseFloat(gpssaRate)||0;\n"
    . "Math.round(base * rate * 100) / 100;",
    '250',
    $sampleVars
);

is_result(
    'sample 2: declare a function and call it',
    "var base = Number(gpssaBase)||0;\n"
    . "var rate = parseFloat(gpssaRate)||0;\n"
    . "function fun1(base1, rate1) {\n"
    . "    return Math.round(base1 * rate1 * 100) / 100;\n"
    . "}\n\n"
    . "fun1(base, rate);",
    '250',
    $sampleVars
);

is_result(
    'sample 3: let/const, a local in the function, Math.pow',
    "let base = Number(gpssaBase)||0;\n"
    . "const rate = parseFloat(gpssaRate)||0;\n"
    . "function fun1(base1, rate1) {\n"
    . "    let r = Math.pow(10, 3);\n\n"
    . "    return r + Math.round(base1 * rate1 * 100) / 100;\n"
    . "}\n\n"
    . "fun1(base, rate);",
    '1250',
    $sampleVars
);

is_result(
    'sample 4: irregular whitespace inside operators and calls',
    "let base = Number(gpssaBase)||     0;\n"
    . "const rate = parseFloat(gpssaRate)||0;\n"
    . "function fun1(base1, rate1) {\n"
    . "    let r = Math.pow(   10,     0);\n"
    . "    \n"
    . "    return r + Math.round(base1 * rate1 * 100) / 100;\n"
    . "}\n\n"
    . "fun1(base, rate);",
    '251',
    $sampleVars
);

// ---------------------------------------------------------------------------
echo "\n[3c] Shapes a user is likely to write\n";
// ---------------------------------------------------------------------------
// These functions are authored by administrators through the payroll-column editor,
// so the parser has to accept ordinary JS habits — trailing semicolons or not, blank
// lines, comments, nested helpers, early returns, loops, guard clauses.

is_result('several functions, one calling another',
    "function gross(b, a) { return b + a; }\n"
    . "function tax(g) { return g * 0.1; }\n"
    . "function net(b, a) { return gross(b, a) - tax(gross(b, a)); }\n"
    . "Math.round(net(1000, 200) * 100) / 100;", '1080');

is_result('early return / guard clause',
    "function bonus(years) {\n"
    . "  if (years < 1) { return 0; }\n"
    . "  if (years < 5) { return 500; }\n"
    . "  return 1500;\n"
    . "}\n"
    . "bonus(0) + '/' + bonus(3) + '/' + bonus(10);", '0/500/1500');

is_result('if / else if / else chain',
    "function band(x) {\n"
    . "  if (x > 100) { return 'high'; }\n"
    . "  else if (x > 50) { return 'mid'; }\n"
    . "  else { return 'low'; }\n"
    . "}\n"
    . "band(120) + band(60) + band(10);", 'highmidlow');

is_result('loop inside a function accumulating a total',
    "function sumTo(n) {\n"
    . "  var t = 0;\n"
    . "  for (var i = 1; i <= n; i++) { t += i; }\n"
    . "  return t;\n"
    . "}\n"
    . "sumTo(100);", '5050');

is_result('function returning an array, then chained',
    "function slabs() { return [1000, 2000, 3000]; }\n"
    . "slabs().map(function (s) { return s * 0.05; }).join('|');", '50|100|150');

is_result('function returning an object, then read',
    "function calc(b) { return { base: b, tax: b * 0.1 }; }\n"
    . "var r = calc(2000);\n"
    . "r.base + r.tax;", '2200');

is_result('function passed as a callback',
    "function double(n) { return n * 2; }\n"
    . "[1, 2, 3].map(double).join(',');", '2,4,6');

is_result('closure over an outer variable, called twice',
    "var rate = 0.1;\n"
    . "function apply(x) { return x * rate; }\n"
    . "apply(100) + apply(200);", '30');

is_result('missing argument is undefined, so ||0 defaults it',
    "function f(a, b) { return (Number(a)||0) + (Number(b)||0); }\n"
    . "f(5);", '5');

is_result('extra arguments are ignored',
    "function f(a) { return a; }\n"
    . "f(1, 2, 3);", '1');

is_result('parameter shadows an outer variable of the same name',
    "var x = 100;\n"
    . "function f(x) { return x; }\n"
    . "f(1) + '/' + x;", '1/100');

is_result('a function with no explicit return yields undefined',
    "function f() { var a = 1; }\n"
    . "String(f());", 'undefined');

is_result('comments, blank lines and no trailing semicolon',
    "// compute the contribution\n"
    . "var base = 1000 /* inline */ ;\n"
    . "\n"
    . "function fn(b) {\n"
    . "   return b * 2   // double it\n"
    . "}\n"
    . "\n"
    . "fn(base)", '2000');

is_result('multiple declarations on one line',
    "var a = 1, b = 2, c = a + b;\n"
    . "c;", '3');

is_result('nested function declared inside another function',
    "function outer(n) {\n"
    . "  function inner(m) { return m + 1; }\n"
    . "  return inner(n) * 2;\n"
    . "}\n"
    . "outer(4);", '10');

is_result('function stored in an object and called',
    "var lib = { twice: function (n) { return n * 2; } };\n"
    . "lib.twice(21);", '42');

is_result('immediately-called function expression',
    "var r = (function (n) { return n + 1; })(41);\n"
    . "r;", '42');

is_result('while loop with continue',
    "var t = 0; var i = 0;\n"
    . "while (i < 10) { i++; if (i % 2 === 0) { continue; } t += i; }\n"
    . "t;", '25');

is_result('do-while runs at least once',
    "var n = 0; do { n++; } while (n < 1); n;", '1');

is_result('string building in a loop',
    "var out = '';\n"
    . "for (var i = 0; i < 3; i++) { out += i + ';'; }\n"
    . "out;", '0;1;2;');

is_result('array built then reduced',
    "var xs = [];\n"
    . "for (var i = 1; i <= 5; i++) { xs.push(i * i); }\n"
    . "xs.reduce(function (a, b) { return a + b; }, 0);", '55');

is_result('ternary inside a return',
    "function cap(x, m) { return x > m ? m : x; }\n"
    . "cap(150, 100) + '/' + cap(50, 100);", '100/50');

is_result('nested ternary',
    "var x = 5; x > 10 ? 'a' : x > 3 ? 'b' : 'c';", 'b');

is_result('compound assignment operators',
    "var x = 10; x += 5; x -= 3; x *= 2; x /= 4; x %= 4; x;", '2');

is_result('unary minus and logical not',
    "var x = 5; (-x) + '/' + (!x) + '/' + (!!x);", '-5/false/true');

is_result('prefix and postfix increment differ',
    "var i = 1; var a = i++; var b = ++i; a + '/' + b + '/' + i;", '1/3/3');

is_result('typeof on each supported value kind',
    "typeof 1 + ',' + typeof 'a' + ',' + typeof true + ',' + typeof [] "
    . "+ ',' + typeof {} + ',' + typeof undefined + ',' + typeof (function(){});",
    'number,string,boolean,object,object,undefined,function');

is_result('deeply chained member and call expressions',
    "'  a,b,c  '.trim().split(',').reverse().join('-').toUpperCase();", 'C-B-A');

is_result('a realistic multi-step payroll function',
    "var basic = Number(basicSalary) || 0;\n"
    . "var allow = Number(allowances) || 0;\n"
    . "var days  = Number(unpaidDays) || 0;\n"
    . "\n"
    . "function dailyRate(b, a) { return (b + a) / 30; }\n"
    . "function deduction(b, a, d) { return dailyRate(b, a) * d; }\n"
    . "\n"
    . "var gross = basic + allow;\n"
    . "var ded = deduction(basic, allow, days);\n"
    . "Math.round((gross - ded) * 100) / 100;",
    '9200',
    array('basicSalary' => 9000, 'allowances' => 3000, 'unpaidDays' => 7));

// ---------------------------------------------------------------------------
echo "\n[3d] const and let behave as declared\n";
// ---------------------------------------------------------------------------
is_result('const holds its value', 'const r = 0.05; r * 100;', '5');
is_refused('reassigning a const is refused', 'const r = 1; r = 2; r;', 'constant');
is_result('let can be reassigned', 'let x = 1; x = 2; x;', '2');
is_result('let inside a block does not leak out',
    'let x = 1; if (true) { let x = 2; } x;', '1');

// ---------------------------------------------------------------------------
echo "\n[4] Recursion is refused\n";
// ---------------------------------------------------------------------------
is_refused('direct recursion',
    'function f(n) { return n <= 0 ? 0 : f(n - 1); } f(5);', 'recursion');
is_refused('indirect recursion (f -> g -> f)',
    'function f(n){ return g(n); } function g(n){ return n<=0 ? 0 : f(n-1); } f(3);', 'recursion');
is_refused('recursion through a callback',
    'function f(a){ return a.map(function(x){ return f([x]); }); } f([1]);', 'recursion');
is_refused('self-referential function expression',
    'var f = function loop(n) { return loop(n); }; f(1);', 'recursion');

// ---------------------------------------------------------------------------
echo "\n[5] The host is unreachable\n";
// ---------------------------------------------------------------------------
// These are not blocked by name — they simply do not exist, so they read as undefined.
is_result('process is undefined', 'typeof process', 'undefined');
is_result('require is undefined', 'typeof require', 'undefined');
is_result('eval is undefined', 'typeof eval', 'undefined');
is_result('Function is undefined', 'typeof Function', 'undefined');
is_result('globalThis is undefined', 'typeof globalThis', 'undefined');
is_result('constructor is undefined', 'typeof constructor', 'undefined');
is_result('__proto__ on an object is absent', 'var o = {}; typeof o.__proto__;', 'undefined');
is_result('constructor on an object is absent', 'var o = {}; typeof o.constructor;', 'undefined');
is_result('no prototype chain to climb', 'var a = []; typeof a.constructor;', 'undefined');
// Absent rather than blocked: an unsupported name is not even reported as existing,
// so a script cannot tell "refused" from "never implemented".
is_result('Math.random does not exist (payroll must be reproducible)',
    'typeof Math.random', 'undefined');
is_refused('calling Math.random is refused', 'Math.random()', 'not a function');
is_refused('calling an unlisted string method', '"a".toUpperCaseX()', 'not supported');
is_result('an unlisted string method reads as undefined', 'typeof "a".blah', 'undefined');

is_refused('calling an undefined global', 'eval("1+1")', 'not a function');
is_refused('new is unparseable', 'new Object()', 'new');
is_refused('class is unparseable', 'class Foo {}', 'class');
is_refused('arrow functions are unparseable', 'var f = (x) => x; f(1);', 'arrow');
is_refused('this is unparseable', 'this;', 'this');
is_refused('import is unparseable', 'import x from "y";', 'import');
is_refused('try/catch is unparseable', 'try { 1; } catch (e) { 2; }', 'try');
is_refused('for-in is unparseable', 'var o={}; for (var k in o) { k; }', 'for-in');
is_refused('template literals are unparseable', 'var a = `x`;', 'Template');
is_refused('regex literals are unparseable', 'var re = /a/; 1;', '');

// ---------------------------------------------------------------------------
echo "\n[6] Budgets terminate\n";
// ---------------------------------------------------------------------------
is_refused('unbounded loop hits a budget', 'while (true) { }', 'exceeded');
is_refused('huge string allocation is capped', '"x".repeat(100000000)', 'maximum');
is_refused('growing an array without bound is capped',
    'var a=[]; for (var i=0;i<200000;i++){ a.push(i); } a.length;', 'maximum');
// Deeply nested source recurses in the PARSER, so the limit protects the PHP C stack.
// This is the one failure mode that would otherwise be a segfault rather than an
// exception, which no caller could catch.
is_refused('deeply nested parentheses are refused, not a segfault',
    str_repeat('(', 5000) . '1' . str_repeat(')', 5000), 'deeply');
is_refused('deeply nested blocks are refused',
    str_repeat('{', 5000) . '1' . str_repeat('}', 5000), 'deeply');

// ---------------------------------------------------------------------------
echo "\n[7] Failure modes callers depend on\n";
// ---------------------------------------------------------------------------
is_refused('syntax errors are JsError, never a PHP fatal', 'var = = ;', '');
is_refused('reading a property of undefined', 'undefined.x', 'undefined');
is_result('an unknown variable is undefined, not an error', 'typeof nosuchvar', 'undefined');

echo "\n" . str_repeat('=', 64) . "\n";
echo "JS SANDBOX  PASS=$pass  FAIL=$fail\n";
echo str_repeat('=', 64) . "\n";
exit($fail === 0 ? 0 : 1);
