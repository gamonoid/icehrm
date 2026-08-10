<?php

namespace Utils\Js;

/**
 * A JS object literal: an ordered string->value map.
 *
 * Deliberately has NO prototype. The prototype-pollution escape that a JavaScript-
 * hosted interpreter must defend against — reaching `constructor.prototype` and on to
 * `Function` — has nowhere to start here, because `__proto__` and `constructor` are
 * ordinary absent keys in a PHP map.
 *
 * Reference semantics, for the same reason as JsArray.
 */
class JsObject
{
    public $props;

    public function __construct($props = array())
    {
        $this->props = $props;
    }
}
