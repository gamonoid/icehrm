<?php

namespace Utils\Js;

/**
 * The `undefined` value.
 *
 * A singleton object rather than PHP null, because JS distinguishes the two: both are
 * loosely equal to each other and to nothing else, but `x === null` is true only for
 * null. Collapsing them onto PHP null would make `Number(undefined)` return 0 instead
 * of NaN, which silently changes a payroll result when a variable is missing.
 */
class JsUndefined
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new JsUndefined();
        }
        return self::$instance;
    }
}
