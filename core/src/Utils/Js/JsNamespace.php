<?php

namespace Utils\Js;

/**
 * Marker for a built-in namespace object: Math, Number, String, Boolean, Array, JSON.
 *
 * Holds only a name. Member access on it is resolved by JsBuiltins against a fixed
 * table, so there is no underlying object for a script to enumerate or reach past.
 */
class JsNamespace
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
