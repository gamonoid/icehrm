<?php

namespace Utils\Js;

/**
 * A user-defined function value.
 *
 * `scope` is the lexical scope captured at definition, which is what makes closures
 * work. `body` is the AST — never PHP source and never a PHP callable — so a function
 * value can only ever be invoked by the interpreter that created it.
 *
 * Identity matters: the no-recursion rule compares function OBJECTS on the call stack,
 * so the same declaration evaluated twice yields two values that may each be running.
 */
class JsFunction
{
    public $name;
    public $params;
    public $body;
    public $scope;

    public function __construct($name, $params, $body, $scope)
    {
        $this->name = $name;
        $this->params = $params;
        $this->body = $body;
        $this->scope = $scope;
    }
}
