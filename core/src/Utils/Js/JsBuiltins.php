<?php

namespace Utils\Js;

/**
 * The complete built-in surface. If a name is not in one of the tables below, a script
 * cannot reach it — there is no dynamic lookup, no reflection, no fallback to a PHP
 * function of the same name.
 *
 * Math.random is deliberately absent: payroll must be reproducible, so a rerun of the
 * same period has to produce the same figures.
 *
 * Callbacks (map/filter/reduce/sort) are invoked through a callable handed in by the
 * interpreter, which is what keeps the no-recursion rule and the step budget in force
 * inside a built-in.
 */
class JsBuiltins
{

    /**
     * The method names each receiver type accepts.
     *
     * Member access consults this so an unsupported name resolves to `undefined`
     * rather than to a method marker. That matters beyond tidiness: it is what makes
     * `typeof [].constructor` honestly report "undefined", and it keeps a script from
     * distinguishing "name exists but is refused" from "name does not exist" — there is
     * no shape here to probe.
     */
    private static $methods = array(
        'Math' => array(
            'round', 'floor', 'ceil', 'trunc', 'abs', 'sign', 'sqrt', 'cbrt', 'exp',
            'log', 'log2', 'log10', 'sin', 'cos', 'tan', 'atan', 'asin', 'acos',
            'atan2', 'pow', 'hypot', 'min', 'max',
        ),
        'Number' => array('isInteger', 'isFinite', 'isNaN', 'parseFloat', 'parseInt'),
        'Array' => array('isArray', 'of'),
        'String' => array('fromCharCode'),
        'JSON' => array('stringify'),
        '@string' => array(
            'charAt', 'charCodeAt', 'concat', 'indexOf', 'lastIndexOf', 'includes',
            'startsWith', 'endsWith', 'slice', 'substring', 'substr', 'toUpperCase',
            'toLowerCase', 'trim', 'trimStart', 'trimEnd', 'split', 'replace',
            'replaceAll', 'repeat', 'padStart', 'padEnd', 'toString', 'valueOf',
        ),
        '@array' => array(
            'push', 'pop', 'shift', 'unshift', 'join', 'indexOf', 'lastIndexOf',
            'includes', 'slice', 'concat', 'reverse', 'map', 'filter', 'forEach',
            'some', 'every', 'find', 'findIndex', 'reduce', 'sort', 'flat', 'toString',
        ),
        '@number' => array('toFixed', 'toString', 'toPrecision', 'valueOf'),
    );

    public static function hasMethod($owner, $name)
    {
        return isset(self::$methods[$owner]) && in_array($name, self::$methods[$owner], true);
    }

    /** Globals a script may name. Everything else is undefined. */
    public static function globalNames()
    {
        return array('Math', 'Number', 'String', 'Boolean', 'Array', 'JSON',
                     'parseInt', 'parseFloat', 'isNaN', 'isFinite', 'NaN', 'Infinity');
    }

    public static function isGlobal($name)
    {
        return in_array($name, self::globalNames(), true);
    }

    /**
     * A global that is a namespace object (Math.round, Number.isInteger ...) is
     * represented as a marker so member access can dispatch on it. Plain values
     * (NaN, Infinity) return the value itself.
     */
    public static function getGlobal($name)
    {
        switch ($name) {
            case 'NaN':      return NAN;
            case 'Infinity': return INF;
            case 'Math':
            case 'Number':
            case 'String':
            case 'Boolean':
            case 'Array':
            case 'JSON':
                return new JsNamespace($name);
            case 'parseInt':
            case 'parseFloat':
            case 'isNaN':
            case 'isFinite':
                return new JsNativeFunction($name);
        }
        return JsUndefined::instance();
    }

    // ------------------------------------------------------------- global functions

    public static function callGlobalFunction($name, $args)
    {
        $arg0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        switch ($name) {
            case 'parseFloat':
                return self::parseFloatJs(JsRuntime::toString($arg0));
            case 'parseInt':
                $radix = array_key_exists(1, $args) ? JsRuntime::toInteger($args[1]) : 10;
                return self::parseIntJs(JsRuntime::toString($arg0), $radix);
            case 'isNaN':
                return is_nan(JsRuntime::toNumber($arg0));
            case 'isFinite':
                $n = JsRuntime::toNumber($arg0);
                return !is_nan($n) && !is_infinite($n);
        }
        throw new JsError($name . ' is not a function');
    }

    /** parseFloat reads the longest numeric prefix and ignores the rest. */
    private static function parseFloatJs($s)
    {
        $s = ltrim($s);
        if (preg_match('/^[+-]?(Infinity)/', $s, $m)) {
            return $m[0][0] === '-' ? -INF : INF;
        }
        if (preg_match('/^[+-]?(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?/', $s, $m)) {
            return (float) $m[0];
        }
        return NAN;
    }

    private static function parseIntJs($s, $radix)
    {
        $s = ltrim($s);
        if ($radix === 0) {
            $radix = 10;
        }
        if ($radix < 2 || $radix > 36) {
            return NAN;
        }
        $sign = 1;
        if ($s !== '' && ($s[0] === '+' || $s[0] === '-')) {
            $sign = $s[0] === '-' ? -1 : 1;
            $s = substr($s, 1);
        }
        if ($radix === 16 && preg_match('/^0[xX]/', $s)) {
            $s = substr($s, 2);
        }
        $digits = '0123456789abcdefghijklmnopqrstuvwxyz';
        $valid = substr($digits, 0, $radix);
        $out = '';
        for ($i = 0; $i < strlen($s); $i++) {
            if (strpos($valid, strtolower($s[$i])) === false) {
                break;
            }
            $out .= $s[$i];
        }
        if ($out === '') {
            return NAN;
        }
        return (float) ($sign * intval($out, $radix));
    }

    // ------------------------------------------------------------ namespace members

    public static function namespaceMember($namespace, $property)
    {
        if ($namespace === 'Math') {
            switch ($property) {
                case 'PI':      return M_PI;
                case 'E':       return M_E;
                case 'LN2':     return M_LN2;
                case 'LN10':    return M_LN10;
                case 'SQRT2':   return M_SQRT2;
            }
        }
        if ($namespace === 'Number') {
            switch ($property) {
                case 'MAX_SAFE_INTEGER': return 9007199254740991.0;
                case 'MIN_SAFE_INTEGER': return -9007199254740991.0;
                case 'EPSILON':          return 2.220446049250313e-16;
                case 'MAX_VALUE':        return PHP_FLOAT_MAX;
                case 'MIN_VALUE':        return 5.0e-324;
                case 'POSITIVE_INFINITY': return INF;
                case 'NEGATIVE_INFINITY': return -INF;
                case 'NaN':              return NAN;
            }
        }
        if (self::hasMethod($namespace, $property)) {
            return new JsNativeMethod($namespace, $property);
        }
        return JsUndefined::instance();
    }

    public static function callNamespaceMethod($namespace, $method, $args, $invoker)
    {
        // Member access reports an unlisted name as undefined, so calling one must fail
        // the same way a call on undefined does — otherwise the error message itself
        // would reveal which names exist.
        if (!self::hasMethod($namespace, $method)) {
            throw new JsError($namespace . '.' . $method . ' is not a function');
        }
        if ($namespace === 'Math') {
            return self::callMath($method, $args);
        }
        if ($namespace === 'Number') {
            return self::callNumberStatic($method, $args);
        }
        if ($namespace === 'Array') {
            if ($method === 'isArray') {
                return array_key_exists(0, $args) && $args[0] instanceof JsArray;
            }
            if ($method === 'of') {
                return new JsArray($args);
            }
            throw new JsError('Array.' . $method . ' is not supported');
        }
        if ($namespace === 'JSON') {
            return self::callJson($method, $args);
        }
        if ($namespace === 'String') {
            if ($method === 'fromCharCode') {
                $out = '';
                foreach ($args as $a) {
                    $out .= chr(JsRuntime::toInteger($a) & 0xFF);
                }
                return $out;
            }
            throw new JsError('String.' . $method . ' is not supported');
        }
        throw new JsError($namespace . '.' . $method . ' is not supported');
    }

    private static function callMath($method, $args)
    {
        $n = array_key_exists(0, $args) ? JsRuntime::toNumber($args[0]) : NAN;

        switch ($method) {
            case 'round':  return JsRuntime::mathRound($n);
            case 'floor':  return is_nan($n) || is_infinite($n) ? $n : floor($n);
            case 'ceil':   return is_nan($n) || is_infinite($n) ? $n : ceil($n);
            case 'trunc':  return is_nan($n) || is_infinite($n) ? $n : (float) (int) $n;
            case 'abs':    return is_nan($n) ? NAN : abs($n);
            case 'sign':
                if (is_nan($n)) {
                    return NAN;
                }
                return $n > 0 ? 1.0 : ($n < 0 ? -1.0 : $n);
            case 'sqrt':   return is_nan($n) || $n < 0 ? NAN : sqrt($n);
            case 'cbrt':   return is_nan($n) ? NAN : (float) ($n < 0 ? -pow(-$n, 1 / 3) : pow($n, 1 / 3));
            case 'exp':    return is_nan($n) ? NAN : exp($n);
            case 'log':    return is_nan($n) || $n < 0 ? NAN : ($n == 0 ? -INF : log($n));
            case 'log2':   return is_nan($n) || $n < 0 ? NAN : ($n == 0 ? -INF : log($n, 2));
            case 'log10':  return is_nan($n) || $n < 0 ? NAN : ($n == 0 ? -INF : log10($n));
            case 'sin':    return is_nan($n) || is_infinite($n) ? NAN : sin($n);
            case 'cos':    return is_nan($n) || is_infinite($n) ? NAN : cos($n);
            case 'tan':    return is_nan($n) || is_infinite($n) ? NAN : tan($n);
            case 'atan':   return is_nan($n) ? NAN : atan($n);
            case 'asin':   return is_nan($n) || $n < -1 || $n > 1 ? NAN : asin($n);
            case 'acos':   return is_nan($n) || $n < -1 || $n > 1 ? NAN : acos($n);
            case 'atan2':
                $y = $n;
                $x = array_key_exists(1, $args) ? JsRuntime::toNumber($args[1]) : NAN;
                return is_nan($y) || is_nan($x) ? NAN : atan2($y, $x);
            case 'pow':
                $base = $n;
                $exp = array_key_exists(1, $args) ? JsRuntime::toNumber($args[1]) : NAN;
                if (is_nan($base) || is_nan($exp)) {
                    return NAN;
                }
                return (float) pow($base, $exp);
            case 'hypot':
                $sum = 0.0;
                foreach ($args as $a) {
                    $v = JsRuntime::toNumber($a);
                    if (is_nan($v)) {
                        return NAN;
                    }
                    $sum += $v * $v;
                }
                return sqrt($sum);
            case 'min':
            case 'max':
                if (count($args) === 0) {
                    return $method === 'min' ? INF : -INF;
                }
                $best = null;
                foreach ($args as $a) {
                    $v = JsRuntime::toNumber($a);
                    if (is_nan($v)) {
                        return NAN; // Math.min(1, NaN) is NaN
                    }
                    if ($best === null || ($method === 'min' ? $v < $best : $v > $best)) {
                        $best = $v;
                    }
                }
                return $best;
        }
        throw new JsError('Math.' . $method . ' is not supported');
    }

    private static function callNumberStatic($method, $args)
    {
        $arg0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        switch ($method) {
            case 'isInteger':
                if (!is_float($arg0) && !is_int($arg0)) {
                    return false;
                }
                $f = (float) $arg0;
                return !is_nan($f) && !is_infinite($f) && $f == floor($f);
            case 'isFinite':
                if (!is_float($arg0) && !is_int($arg0)) {
                    return false;
                }
                $f = (float) $arg0;
                return !is_nan($f) && !is_infinite($f);
            case 'isNaN':
                return (is_float($arg0) || is_int($arg0)) && is_nan((float) $arg0);
            case 'parseFloat':
                return self::parseFloatJs(JsRuntime::toString($arg0));
            case 'parseInt':
                $radix = array_key_exists(1, $args) ? JsRuntime::toInteger($args[1]) : 10;
                return self::parseIntJs(JsRuntime::toString($arg0), $radix);
        }
        throw new JsError('Number.' . $method . ' is not supported');
    }

    private static function callJson($method, $args)
    {
        $arg0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        if ($method === 'stringify') {
            return JsRuntime::guardString(self::jsonEncode($arg0));
        }
        throw new JsError('JSON.' . $method . ' is not supported');
    }

    private static function jsonEncode($v)
    {
        if ($v === null) {
            return 'null';
        }
        if (JsRuntime::isUndefined($v)) {
            return 'null';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_float($v) || is_int($v)) {
            $f = (float) $v;
            return (is_nan($f) || is_infinite($f)) ? 'null' : JsRuntime::numberToString($f);
        }
        if (is_string($v)) {
            return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        if ($v instanceof JsArray) {
            $parts = array();
            foreach ($v->items as $item) {
                $parts[] = self::jsonEncode($item);
            }
            return '[' . implode(',', $parts) . ']';
        }
        if ($v instanceof JsObject) {
            $parts = array();
            foreach ($v->props as $k => $item) {
                if ($item instanceof JsFunction || JsRuntime::isUndefined($item)) {
                    continue; // JSON.stringify drops these, as JS does
                }
                $parts[] = json_encode((string) $k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    . ':' . self::jsonEncode($item);
            }
            return '{' . implode(',', $parts) . '}';
        }
        return 'null';
    }

    // ---------------------------------------------------------------- string methods

    public static function stringMember($str, $property)
    {
        if ($property === 'length') {
            return (float) strlen($str);
        }
        if (self::hasMethod('@string', $property)) {
            return new JsNativeMethod('@string', $property);
        }
        return JsUndefined::instance();
    }

    public static function callStringMethod($str, $method, $args, $invoker)
    {
        $a0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        $a1 = array_key_exists(1, $args) ? $args[1] : JsUndefined::instance();
        $len = strlen($str);

        switch ($method) {
            case 'charAt':
                $i = JsRuntime::toInteger($a0);
                return ($i >= 0 && $i < $len) ? $str[$i] : '';
            case 'charCodeAt':
                $i = JsRuntime::toInteger($a0);
                return ($i >= 0 && $i < $len) ? (float) ord($str[$i]) : NAN;
            case 'concat':
                $out = $str;
                foreach ($args as $a) {
                    $out .= JsRuntime::toString($a);
                }
                return JsRuntime::guardString($out);
            case 'indexOf':
                $needle = JsRuntime::toString($a0);
                $from = JsRuntime::isUndefined($a1) ? 0 : max(0, JsRuntime::toInteger($a1));
                if ($needle === '') {
                    return (float) min($from, $len);
                }
                $p = strpos($str, $needle, min($from, $len));
                return $p === false ? -1.0 : (float) $p;
            case 'lastIndexOf':
                $needle = JsRuntime::toString($a0);
                if ($needle === '') {
                    return (float) $len;
                }
                $p = strrpos($str, $needle);
                return $p === false ? -1.0 : (float) $p;
            case 'includes':
                $needle = JsRuntime::toString($a0);
                return $needle === '' ? true : strpos($str, $needle) !== false;
            case 'startsWith':
                $needle = JsRuntime::toString($a0);
                $from = JsRuntime::isUndefined($a1) ? 0 : JsRuntime::toInteger($a1);
                return substr($str, $from, strlen($needle)) === $needle;
            case 'endsWith':
                $needle = JsRuntime::toString($a0);
                $end = JsRuntime::isUndefined($a1) ? $len : JsRuntime::toInteger($a1);
                if ($needle === '') {
                    return true;
                }
                return substr(substr($str, 0, $end), -strlen($needle)) === $needle;
            case 'slice':
                return self::sliceString($str, $args);
            case 'substring':
                $start = JsRuntime::isUndefined($a0) ? 0 : JsRuntime::toInteger($a0);
                $end = JsRuntime::isUndefined($a1) ? $len : JsRuntime::toInteger($a1);
                $start = max(0, min($len, $start));
                $end = max(0, min($len, $end));
                if ($start > $end) {
                    $t = $start;
                    $start = $end;
                    $end = $t;
                }
                return substr($str, $start, $end - $start);
            case 'substr':
                $start = JsRuntime::toInteger($a0);
                if ($start < 0) {
                    $start = max(0, $len + $start);
                }
                $count = JsRuntime::isUndefined($a1) ? ($len - $start) : JsRuntime::toInteger($a1);
                if ($count <= 0) {
                    return '';
                }
                $r = substr($str, $start, $count);
                return $r === false ? '' : $r;
            case 'toUpperCase':
                return strtoupper($str);
            case 'toLowerCase':
                return strtolower($str);
            case 'trim':
                return trim($str);
            case 'trimStart':
                return ltrim($str);
            case 'trimEnd':
                return rtrim($str);
            case 'split':
                if (JsRuntime::isUndefined($a0)) {
                    return new JsArray(array($str));
                }
                $sep = JsRuntime::toString($a0);
                if ($sep === '') {
                    $chars = $str === '' ? array() : str_split($str);
                    JsRuntime::guardArrayLength(count($chars));
                    return new JsArray($chars);
                }
                $parts = explode($sep, $str);
                JsRuntime::guardArrayLength(count($parts));
                return new JsArray($parts);
            case 'replace':
                // String search only — no regular expressions exist in this sandbox,
                // so this replaces the FIRST occurrence, matching JS with a string arg.
                $search = JsRuntime::toString($a0);
                $replacement = JsRuntime::toString($a1);
                if ($search === '') {
                    return JsRuntime::guardString($replacement . $str);
                }
                $p = strpos($str, $search);
                if ($p === false) {
                    return $str;
                }
                return JsRuntime::guardString(substr_replace($str, $replacement, $p, strlen($search)));
            case 'replaceAll':
                $search = JsRuntime::toString($a0);
                $replacement = JsRuntime::toString($a1);
                if ($search === '') {
                    return $str;
                }
                return JsRuntime::guardString(str_replace($search, $replacement, $str));
            case 'repeat':
                $count = JsRuntime::toInteger($a0);
                if ($count < 0) {
                    throw new JsError('Invalid count value for repeat()');
                }
                if ($count * $len > JsRuntime::MAX_STRING_LENGTH) {
                    throw new JsError('String exceeds the maximum allowed length', null, JsError::KIND_LIMIT);
                }
                return str_repeat($str, $count);
            case 'padStart':
            case 'padEnd':
                $target = JsRuntime::toInteger($a0);
                JsRuntime::guardString(str_repeat('x', max(0, min($target, JsRuntime::MAX_STRING_LENGTH))));
                $pad = JsRuntime::isUndefined($a1) ? ' ' : JsRuntime::toString($a1);
                if ($pad === '' || $target <= $len) {
                    return $str;
                }
                return str_pad($str, $target, $pad, $method === 'padStart' ? STR_PAD_LEFT : STR_PAD_RIGHT);
            case 'toString':
            case 'valueOf':
                return $str;
        }
        throw new JsError('String method "' . $method . '" is not supported');
    }

    private static function sliceString($str, $args)
    {
        $len = strlen($str);
        $a0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        $a1 = array_key_exists(1, $args) ? $args[1] : JsUndefined::instance();

        $start = JsRuntime::isUndefined($a0) ? 0 : JsRuntime::toInteger($a0);
        $end = JsRuntime::isUndefined($a1) ? $len : JsRuntime::toInteger($a1);
        if ($start < 0) {
            $start = max(0, $len + $start);
        }
        if ($end < 0) {
            $end = max(0, $len + $end);
        }
        $start = min($start, $len);
        $end = min($end, $len);
        if ($start >= $end) {
            return '';
        }
        return substr($str, $start, $end - $start);
    }

    // ----------------------------------------------------------------- number methods

    public static function numberMember($num, $property)
    {
        if (self::hasMethod('@number', $property)) {
            return new JsNativeMethod('@number', $property);
        }
        return JsUndefined::instance();
    }

    public static function callNumberMethod($num, $method, $args, $invoker)
    {
        $n = JsRuntime::toNumber($num);
        switch ($method) {
            case 'toFixed':
                $digits = array_key_exists(0, $args) ? JsRuntime::toInteger($args[0]) : 0;
                if ($digits < 0 || $digits > 100) {
                    throw new JsError('toFixed() digits argument must be between 0 and 100');
                }
                if (is_nan($n)) {
                    return 'NaN';
                }
                if (is_infinite($n)) {
                    return $n > 0 ? 'Infinity' : '-Infinity';
                }
                return number_format($n, $digits, '.', '');
            case 'toString':
                $radix = array_key_exists(0, $args) ? JsRuntime::toInteger($args[0]) : 10;
                if ($radix === 10) {
                    return JsRuntime::numberToString($n);
                }
                if ($radix < 2 || $radix > 36) {
                    throw new JsError('toString() radix must be between 2 and 36');
                }
                if (is_nan($n) || is_infinite($n)) {
                    return JsRuntime::numberToString($n);
                }
                return base_convert((string) (int) $n, 10, $radix);
            case 'toPrecision':
                if (!array_key_exists(0, $args)) {
                    return JsRuntime::numberToString($n);
                }
                $p = JsRuntime::toInteger($args[0]);
                if ($p < 1 || $p > 100) {
                    throw new JsError('toPrecision() argument must be between 1 and 100');
                }
                return sprintf('%.' . $p . 'G', $n);
            case 'valueOf':
                return $n;
        }
        throw new JsError('Number method "' . $method . '" is not supported');
    }

    // ------------------------------------------------------------------ array methods

    public static function arrayMember($array, $property)
    {
        if ($property === 'length') {
            return (float) count($array->items);
        }
        if (self::hasMethod('@array', $property)) {
            return new JsNativeMethod('@array', $property);
        }
        return JsUndefined::instance();
    }

    /**
     * @param JsArray  $array
     * @param callable $invoker function(JsFunction|JsNativeMethod $fn, array $args) => mixed
     */
    public static function callArrayMethod($array, $method, $args, $invoker)
    {
        $items = $array->items;
        $count = count($items);
        $a0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        $a1 = array_key_exists(1, $args) ? $args[1] : JsUndefined::instance();

        switch ($method) {
            case 'push':
                foreach ($args as $a) {
                    $array->items[] = $a;
                }
                JsRuntime::guardArrayLength(count($array->items));
                return (float) count($array->items);
            case 'pop':
                if (count($array->items) === 0) {
                    return JsUndefined::instance();
                }
                return array_pop($array->items);
            case 'shift':
                if (count($array->items) === 0) {
                    return JsUndefined::instance();
                }
                return array_shift($array->items);
            case 'unshift':
                foreach (array_reverse($args) as $a) {
                    array_unshift($array->items, $a);
                }
                JsRuntime::guardArrayLength(count($array->items));
                return (float) count($array->items);
            case 'join':
                $sep = JsRuntime::isUndefined($a0) ? ',' : JsRuntime::toString($a0);
                $parts = array();
                foreach ($items as $item) {
                    $parts[] = ($item === null || JsRuntime::isUndefined($item)) ? '' : JsRuntime::toString($item);
                }
                return JsRuntime::guardString(implode($sep, $parts));
            case 'indexOf':
                foreach ($items as $i => $item) {
                    if (JsRuntime::strictEquals($item, $a0)) {
                        return (float) $i;
                    }
                }
                return -1.0;
            case 'lastIndexOf':
                for ($i = $count - 1; $i >= 0; $i--) {
                    if (JsRuntime::strictEquals($items[$i], $a0)) {
                        return (float) $i;
                    }
                }
                return -1.0;
            case 'includes':
                foreach ($items as $item) {
                    if (JsRuntime::strictEquals($item, $a0)) {
                        return true;
                    }
                    // includes() finds NaN, unlike indexOf()
                    if (is_float($item) && is_float($a0) && is_nan($item) && is_nan($a0)) {
                        return true;
                    }
                }
                return false;
            case 'slice':
                $start = JsRuntime::isUndefined($a0) ? 0 : JsRuntime::toInteger($a0);
                $end = JsRuntime::isUndefined($a1) ? $count : JsRuntime::toInteger($a1);
                if ($start < 0) {
                    $start = max(0, $count + $start);
                }
                if ($end < 0) {
                    $end = max(0, $count + $end);
                }
                $start = min($start, $count);
                $end = min($end, $count);
                return new JsArray($start >= $end ? array() : array_slice($items, $start, $end - $start));
            case 'concat':
                $out = $items;
                foreach ($args as $a) {
                    if ($a instanceof JsArray) {
                        foreach ($a->items as $item) {
                            $out[] = $item;
                        }
                    } else {
                        $out[] = $a;
                    }
                }
                JsRuntime::guardArrayLength(count($out));
                return new JsArray($out);
            case 'reverse':
                $array->items = array_reverse($array->items);
                return $array;
            case 'map':
                self::requireCallback($a0, 'map');
                $out = array();
                foreach ($items as $i => $item) {
                    $out[] = call_user_func($invoker, $a0, array($item, (float) $i, $array));
                }
                return new JsArray($out);
            case 'filter':
                self::requireCallback($a0, 'filter');
                $out = array();
                foreach ($items as $i => $item) {
                    if (JsRuntime::toBoolean(call_user_func($invoker, $a0, array($item, (float) $i, $array)))) {
                        $out[] = $item;
                    }
                }
                return new JsArray($out);
            case 'forEach':
                self::requireCallback($a0, 'forEach');
                foreach ($items as $i => $item) {
                    call_user_func($invoker, $a0, array($item, (float) $i, $array));
                }
                return JsUndefined::instance();
            case 'some':
                self::requireCallback($a0, 'some');
                foreach ($items as $i => $item) {
                    if (JsRuntime::toBoolean(call_user_func($invoker, $a0, array($item, (float) $i, $array)))) {
                        return true;
                    }
                }
                return false;
            case 'every':
                self::requireCallback($a0, 'every');
                foreach ($items as $i => $item) {
                    if (!JsRuntime::toBoolean(call_user_func($invoker, $a0, array($item, (float) $i, $array)))) {
                        return false;
                    }
                }
                return true;
            case 'find':
                self::requireCallback($a0, 'find');
                foreach ($items as $i => $item) {
                    if (JsRuntime::toBoolean(call_user_func($invoker, $a0, array($item, (float) $i, $array)))) {
                        return $item;
                    }
                }
                return JsUndefined::instance();
            case 'findIndex':
                self::requireCallback($a0, 'findIndex');
                foreach ($items as $i => $item) {
                    if (JsRuntime::toBoolean(call_user_func($invoker, $a0, array($item, (float) $i, $array)))) {
                        return (float) $i;
                    }
                }
                return -1.0;
            case 'reduce':
                self::requireCallback($a0, 'reduce');
                $i = 0;
                if (count($args) >= 2) {
                    $acc = $a1;
                } else {
                    if ($count === 0) {
                        throw new JsError('Reduce of empty array with no initial value');
                    }
                    $acc = $items[0];
                    $i = 1;
                }
                for (; $i < $count; $i++) {
                    $acc = call_user_func($invoker, $a0, array($acc, $items[$i], (float) $i, $array));
                }
                return $acc;
            case 'sort':
                return self::sortArray($array, $a0, $invoker);
            case 'flat':
                $out = array();
                foreach ($items as $item) {
                    if ($item instanceof JsArray) {
                        foreach ($item->items as $inner) {
                            $out[] = $inner;
                        }
                    } else {
                        $out[] = $item;
                    }
                }
                JsRuntime::guardArrayLength(count($out));
                return new JsArray($out);
            case 'toString':
                return JsRuntime::toString($array);
        }
        throw new JsError('Array method "' . $method . '" is not supported');
    }

    private static function requireCallback($fn, $method)
    {
        if (!($fn instanceof JsFunction)) {
            throw new JsError($method . '() requires a function argument');
        }
    }

    private static function sortArray($array, $comparator, $invoker)
    {
        $items = $array->items;

        if ($comparator instanceof JsFunction) {
            // usort with a guest comparator: the invoker keeps the step budget and the
            // no-recursion rule in force for each comparison.
            usort($items, function ($a, $b) use ($comparator, $invoker) {
                $r = JsRuntime::toNumber(call_user_func($invoker, $comparator, array($a, $b)));
                if (is_nan($r) || $r == 0) {
                    return 0;
                }
                return $r < 0 ? -1 : 1;
            });
        } else {
            if (!JsRuntime::isUndefined($comparator) && $comparator !== null) {
                throw new JsError('sort() comparator must be a function');
            }
            // Default sort is lexicographic on the string form, as in JS.
            usort($items, function ($a, $b) {
                return strcmp(JsRuntime::toString($a), JsRuntime::toString($b));
            });
        }

        $array->items = $items;
        return $array;
    }
}
