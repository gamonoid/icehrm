<?php

namespace Utils\Js;

/**
 * JavaScript value semantics and the built-in allowlist.
 *
 * Two jobs:
 *
 * 1. CONVERSIONS. PHP and JS disagree in ways that would quietly change payroll
 *    numbers, so the JS rule is implemented explicitly rather than leaned on PHP for.
 *    The ones that actually bite:
 *      - Math.round is half-up toward +Infinity; PHP's round() is half-away-from-zero.
 *        They differ on every negative .5 (JS -0.5 -> -0, PHP -> -1), and deductions
 *        are negative.
 *      - Number->string must be the shortest round-tripping form (JS prints
 *        0.30000000000000004), while PHP's default precision would print 0.3.
 *      - '%' is fmod (sign follows the dividend), not PHP's integer modulo.
 *      - Division by zero yields Infinity/NaN instead of raising.
 *
 * 2. BUILT-INS. Every global and method a script can reach is named here. There is no
 *    fallback path: an unknown global resolves to nothing and an unknown method throws.
 *    `eval`, `Function`, `require`, `process`, `globalThis`, `constructor` and
 *    `__proto__` need no special-casing — they are simply absent, and absence is not
 *    something a script can work around.
 */
class JsRuntime
{
    /** Guards against a script building a value large enough to exhaust memory. */
    const MAX_STRING_LENGTH = 1048576;   // 1 MiB
    const MAX_ARRAY_LENGTH  = 100000;

    // ------------------------------------------------------------- type predicates

    public static function isUndefined($v)
    {
        return $v instanceof JsUndefined;
    }

    public static function typeOf($v)
    {
        if (self::isUndefined($v)) {
            return 'undefined';
        }
        if ($v === null) {
            return 'object'; // typeof null === 'object', a JS wart worth keeping
        }
        if (is_bool($v)) {
            return 'boolean';
        }
        if (is_float($v) || is_int($v)) {
            return 'number';
        }
        if (is_string($v)) {
            return 'string';
        }
        if ($v instanceof JsFunction || $v instanceof JsNativeFunction || $v instanceof JsNativeMethod) {
            return 'function';
        }
        return 'object'; // JsArray, JsObject and the builtin namespaces
    }

    // ----------------------------------------------------------------- conversions

    /** JS ToBoolean. */
    public static function toBoolean($v)
    {
        if (is_bool($v)) {
            return $v;
        }
        if ($v === null || self::isUndefined($v)) {
            return false;
        }
        if (is_float($v) || is_int($v)) {
            return !($v == 0 || is_nan((float) $v));
        }
        if (is_string($v)) {
            return $v !== '';
        }
        return true; // arrays, objects and functions are always truthy — even []
    }

    /** JS ToNumber. Returns a float, possibly NAN. */
    public static function toNumber($v)
    {
        if (is_float($v)) {
            return $v;
        }
        if (is_int($v)) {
            return (float) $v;
        }
        if (is_bool($v)) {
            return $v ? 1.0 : 0.0;
        }
        if ($v === null) {
            return 0.0;          // Number(null) === 0
        }
        if (self::isUndefined($v)) {
            return NAN;          // Number(undefined) is NaN
        }
        if (is_string($v)) {
            $t = trim($v);
            if ($t === '') {
                return 0.0;      // Number('') === 0, and Number('   ') too
            }
            if (preg_match('/^[+-]?(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?$/', $t)) {
                return (float) $t;
            }
            if (preg_match('/^[+-]?0[xX][0-9a-fA-F]+$/', $t)) {
                return (float) intval($t, 16);
            }
            if ($t === 'Infinity' || $t === '+Infinity') {
                return INF;
            }
            if ($t === '-Infinity') {
                return -INF;
            }
            return NAN;
        }
        if ($v instanceof JsArray) {
            // JS: [] -> 0, [5] -> 5, [1,2] -> NaN (via ToPrimitive -> join)
            if (count($v->items) === 0) {
                return 0.0;
            }
            if (count($v->items) === 1) {
                return self::toNumber($v->items[0]);
            }
            return NAN;
        }
        return NAN; // objects and functions
    }

    /**
     * JS ToString.
     *
     * Numbers use the shortest representation that round-trips, which is what JS
     * prints. PHP's default float formatting is precision-ini dependent, so the
     * precision is searched explicitly instead — the result is identical on any
     * install rather than dependent on php.ini.
     */
    public static function toString($v)
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if ($v === null) {
            return 'null';
        }
        if (self::isUndefined($v)) {
            return 'undefined';
        }
        if (is_float($v) || is_int($v)) {
            return self::numberToString((float) $v);
        }
        if ($v instanceof JsArray) {
            $parts = array();
            foreach ($v->items as $item) {
                // JS Array.prototype.join renders null/undefined as empty.
                $parts[] = ($item === null || self::isUndefined($item)) ? '' : self::toString($item);
            }
            return implode(',', $parts);
        }
        if ($v instanceof JsFunction) {
            return 'function ' . ($v->name === null ? '' : $v->name) . '() { [sandboxed code] }';
        }
        return '[object Object]';
    }

    public static function numberToString($n)
    {
        $n = (float) $n;

        if (is_nan($n)) {
            return 'NaN';
        }
        if (is_infinite($n)) {
            return $n > 0 ? 'Infinity' : '-Infinity';
        }
        if ($n == 0) {
            return '0';   // JS prints -0 as "0" via String(); only Object.is sees the sign
        }

        // Integers up to 2^53 print without a decimal point or exponent.
        if ($n == floor($n) && abs($n) < 9007199254740992.0) {
            return sprintf('%.0f', $n);
        }

        // Shortest round-tripping decimal form.
        for ($precision = 1; $precision <= 17; $precision++) {
            $s = sprintf('%.' . $precision . 'G', $n);
            if ((float) $s === $n) {
                return self::normalizeExponent($s);
            }
        }
        return self::normalizeExponent(sprintf('%.17G', $n));
    }

    /** PHP prints 1.0E-7; JS prints 1e-7. */
    private static function normalizeExponent($s)
    {
        if (strpos($s, 'E') === false) {
            return $s;
        }
        list($mantissa, $exponent) = explode('E', $s, 2);
        if (strpos($mantissa, '.') !== false) {
            $mantissa = rtrim(rtrim($mantissa, '0'), '.');
        }
        $sign = '';
        if ($exponent[0] === '+' || $exponent[0] === '-') {
            $sign = $exponent[0] === '-' ? '-' : '+';
            $exponent = substr($exponent, 1);
        }
        $exponent = ltrim($exponent, '0');
        if ($exponent === '') {
            $exponent = '0';
        }
        return $mantissa . 'e' . $sign . $exponent;
    }

    /** JS ToInt32-ish truncation used by array index maths and parseInt. */
    public static function toInteger($v)
    {
        $n = self::toNumber($v);
        if (is_nan($n)) {
            return 0;
        }
        if (is_infinite($n)) {
            return $n > 0 ? PHP_INT_MAX : PHP_INT_MIN;
        }
        return (int) ($n < 0 ? ceil($n) : floor($n));
    }

    // ------------------------------------------------------------------ comparison

    /** JS === */
    public static function strictEquals($a, $b)
    {
        $ta = self::typeOf($a);
        $tb = self::typeOf($b);

        // typeOf(null) is 'object', so separate the null/undefined cases explicitly.
        if ($a === null || $b === null) {
            return $a === null && $b === null;
        }
        if (self::isUndefined($a) || self::isUndefined($b)) {
            return self::isUndefined($a) && self::isUndefined($b);
        }
        if ($ta !== $tb) {
            return false;
        }
        if ($ta === 'number') {
            $fa = (float) $a;
            $fb = (float) $b;
            if (is_nan($fa) || is_nan($fb)) {
                return false; // NaN !== NaN
            }
            return $fa == $fb;
        }
        if ($ta === 'string' || $ta === 'boolean') {
            return $a === $b;
        }
        return $a === $b; // arrays/objects/functions compare by identity
    }

    /** JS == (loose), restricted to the types this sandbox has. */
    public static function looseEquals($a, $b)
    {
        $aNullish = ($a === null || self::isUndefined($a));
        $bNullish = ($b === null || self::isUndefined($b));
        if ($aNullish || $bNullish) {
            return $aNullish && $bNullish; // null == undefined, neither == anything else
        }

        $ta = self::typeOf($a);
        $tb = self::typeOf($b);
        if ($ta === $tb) {
            return self::strictEquals($a, $b);
        }
        if ($ta === 'boolean') {
            return self::looseEquals(self::toNumber($a), $b);
        }
        if ($tb === 'boolean') {
            return self::looseEquals($a, self::toNumber($b));
        }
        if (($ta === 'number' && $tb === 'string') || ($ta === 'string' && $tb === 'number')) {
            return self::strictEquals(self::toNumber($a), self::toNumber($b));
        }
        if ($a instanceof JsArray || $b instanceof JsArray) {
            // ToPrimitive on one side, then compare again.
            if ($a instanceof JsArray && !($b instanceof JsArray)) {
                return self::looseEquals(self::toString($a), $b);
            }
            if ($b instanceof JsArray && !($a instanceof JsArray)) {
                return self::looseEquals($a, self::toString($b));
            }
        }
        return false;
    }

    // ------------------------------------------------------------------- operators

    /** The `+` operator: string concatenation if either side is a string. */
    public static function add($a, $b)
    {
        $pa = self::toPrimitive($a);
        $pb = self::toPrimitive($b);
        if (is_string($pa) || is_string($pb)) {
            $s = self::toString($pa) . self::toString($pb);
            self::guardString($s);
            return $s;
        }
        return self::toNumber($pa) + self::toNumber($pb);
    }

    private static function toPrimitive($v)
    {
        if ($v instanceof JsArray || $v instanceof JsObject || $v instanceof JsFunction) {
            return self::toString($v);
        }
        return $v;
    }

    public static function divide($a, $b)
    {
        $x = self::toNumber($a);
        $y = self::toNumber($b);
        if (is_nan($x) || is_nan($y)) {
            return NAN;
        }
        if ($y == 0) {
            // JS: x/0 is +/-Infinity, 0/0 is NaN. PHP 8 raises DivisionByZeroError, so
            // the result is produced without ever performing the division — including
            // the sign test, which is why negative zero is detected by its string form
            // ("-0") rather than by the usual 1/$y trick.
            if ($x == 0) {
                return NAN;
            }
            $negZero = ((string) $y === '-0');
            $sign = (($x < 0) xor $negZero) ? -1 : 1;
            return $sign * INF;
        }
        return $x / $y;
    }

    /** JS % keeps the sign of the dividend — that is fmod, not PHP's integer %. */
    public static function modulo($a, $b)
    {
        $x = self::toNumber($a);
        $y = self::toNumber($b);
        if (is_nan($x) || is_nan($y) || is_infinite($x) || $y == 0) {
            return NAN;
        }
        if (is_infinite($y)) {
            return $x;
        }
        return fmod($x, $y);
    }

    /** JS relational operators: string comparison when both sides are strings. */
    public static function compare($op, $a, $b)
    {
        $pa = self::toPrimitive($a);
        $pb = self::toPrimitive($b);

        if (is_string($pa) && is_string($pb)) {
            $c = strcmp($pa, $pb);
            switch ($op) {
                case '<':  return $c < 0;
                case '>':  return $c > 0;
                case '<=': return $c <= 0;
                case '>=': return $c >= 0;
            }
        }

        $x = self::toNumber($pa);
        $y = self::toNumber($pb);
        if (is_nan($x) || is_nan($y)) {
            return false; // every comparison with NaN is false
        }
        switch ($op) {
            case '<':  return $x < $y;
            case '>':  return $x > $y;
            case '<=': return $x <= $y;
            case '>=': return $x >= $y;
        }
        return false;
    }

    // ---------------------------------------------------------------------- guards

    public static function guardString($s)
    {
        if (strlen($s) > self::MAX_STRING_LENGTH) {
            throw new JsError('String exceeds the maximum allowed length', null, JsError::KIND_LIMIT);
        }
        return $s;
    }

    public static function guardArrayLength($n)
    {
        if ($n > self::MAX_ARRAY_LENGTH) {
            throw new JsError('Array exceeds the maximum allowed length', null, JsError::KIND_LIMIT);
        }
        return $n;
    }

    /**
     * JS Math.round: half rounds UP (toward +Infinity), unlike PHP's round() which
     * rounds half away from zero. Math.round(-0.5) is -0 in JS but -1 in PHP.
     */
    public static function mathRound($n)
    {
        $n = self::toNumber($n);
        if (is_nan($n) || is_infinite($n)) {
            return $n;
        }
        return floor($n + 0.5);
    }
}
