<?php

namespace Utils\Js;

/**
 * Marker for a built-in method bound to a receiver type ('@string', '@array',
 * '@number', or a namespace name).
 *
 * Referencing one as a value is allowed so `typeof "".trim` behaves, but calling it
 * detached from its receiver is refused — there is no bind/call/apply in this sandbox.
 */
class JsNativeMethod
{
    public $owner;
    public $name;

    public function __construct($owner, $name)
    {
        $this->owner = $owner;
        $this->name = $name;
    }
}
