<?php

namespace Utils\Js;

/** Marker for a built-in global function (parseInt, parseFloat, isNaN, isFinite). */
class JsNativeFunction
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
