<?php

namespace Utils\Js;

/**
 * A JS array: dense, 0-indexed, stored as a PHP list in `items`.
 *
 * A wrapper object rather than a bare PHP array because JS arrays have REFERENCE
 * semantics — `var b = a; b.push(1)` must be visible through `a`. A PHP array is
 * copied on assignment and would diverge silently.
 */
class JsArray
{
    public $items;

    public function __construct($items = array())
    {
        $this->items = array_values($items);
    }
}
