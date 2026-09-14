<?php

namespace Utils\Js;

/**
 * A lexical scope. Chained to a parent, which is what gives closures their captured
 * environment.
 *
 * There is no global object behind the last link: when a lookup falls off the end of
 * the chain, the name resolves through JsBuiltins and then to `undefined`. A script
 * therefore cannot enumerate what exists, only ask for names one at a time — and
 * assigning to an undeclared name creates a binding in the current scope rather than
 * leaking one somewhere shared.
 */
class JsScope
{
    private $vars = array();
    private $consts = array();
    private $parent;

    public function __construct($parent = null)
    {
        $this->parent = $parent;
    }

    public function declare($name, $value, $isConst = false)
    {
        $this->vars[$name] = $value;
        if ($isConst) {
            $this->consts[$name] = true;
        } else {
            unset($this->consts[$name]);
        }
    }

    public function has($name)
    {
        if (array_key_exists($name, $this->vars)) {
            return true;
        }
        return $this->parent !== null && $this->parent->has($name);
    }

    public function get($name)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        if ($this->parent !== null) {
            return $this->parent->get($name);
        }
        return JsUndefined::instance();
    }

    /**
     * Assign to the nearest enclosing binding, or declare here if there is none.
     *
     * Reassigning a const is refused rather than ignored. A script author who writes
     * `const rate = 0.05;` and later assigns to it expects the JS behaviour (a
     * TypeError); silently accepting the write would hand back a number they did not
     * intend, and payroll figures are the output.
     */
    public function set($name, $value)
    {
        $scope = $this;
        while ($scope !== null) {
            if (array_key_exists($name, $scope->vars)) {
                if (isset($scope->consts[$name])) {
                    throw new JsError('Assignment to constant variable "' . $name . '"');
                }
                $scope->vars[$name] = $value;
                return;
            }
            $scope = $scope->parent;
        }
        $this->vars[$name] = $value;
    }
}
