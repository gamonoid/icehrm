<?php

namespace Utils\Js;

/**
 * Tree-walking evaluator for the parsed subset.
 *
 * Security model, in order of strength:
 *
 *   1. The grammar (JsParser) is the allowlist — unsupported syntax never becomes AST.
 *   2. Identifier resolution reaches only injected variables, script-declared bindings
 *      and the fixed builtin table. There is no host object to name, so there is
 *      nothing for a script to escape TO. This is why the sandbox does not need the
 *      proxy/membrane machinery that vm2 needed and that vm2 kept losing with.
 *   3. Resource budgets below, because "cannot escape" is not the same as "cannot hang
 *      the request".
 *
 * BUDGETS
 *   - No recursion, direct or indirect: a function already on the call stack cannot be
 *     entered again. This is a hard guarantee the caller asked for, not a depth limit.
 *   - Call depth, so deep non-recursive nesting cannot exhaust the PHP stack.
 *   - Step budget, so loops terminate.
 *   - Wall-clock budget, matching the 2s timeout the previous vm2 runner used.
 *   - Value size caps live in JsRuntime (strings and arrays).
 */
class JsInterpreter
{
    const MAX_STEPS = 2000000;
    const MAX_CALL_DEPTH = 64;
    const MAX_SECONDS = 2.0;

    private $steps = 0;
    private $deadline;
    /** @var JsFunction[] functions currently executing, for the no-recursion check */
    private $callStack = array();

    /** Signals used to unwind statement execution; never visible to a script. */
    private static $BREAK = 'break';
    private static $CONTINUE = 'continue';

    private $returnValue = null;

    /**
     * @param array $ast        from JsParser
     * @param array $variables  name => PHP value, injected as the outermost scope
     * @return mixed the completion value (the last expression statement's value)
     */
    public function run($ast, $variables)
    {
        $this->steps = 0;
        $this->deadline = microtime(true) + self::MAX_SECONDS;
        $this->callStack = array();

        $scope = new JsScope();
        foreach ($variables as $name => $value) {
            $scope->declare($name, self::importValue($value));
        }

        // Function declarations are hoisted, so a script may call one defined further
        // down — the same order-independence authors expect from JS.
        $this->hoistFunctions($ast['body'], $scope);

        $completion = JsUndefined::instance();
        foreach ($ast['body'] as $stmt) {
            $signal = $this->execStatement($stmt, $scope, $completion);
            if ($signal === 'return') {
                return $this->returnValue;
            }
        }
        return $completion;
    }

    /** Convert a PHP value handed in by the application into a sandbox value. */
    public static function importValue($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value) || is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_array($value)) {
            // A list becomes an array; a map becomes an object.
            $isList = array_keys($value) === range(0, count($value) - 1);
            if ($isList) {
                $items = array();
                foreach ($value as $item) {
                    $items[] = self::importValue($item);
                }
                return new JsArray($items);
            }
            $props = array();
            foreach ($value as $k => $item) {
                $props[(string) $k] = self::importValue($item);
            }
            return new JsObject($props);
        }
        if (is_object($value)) {
            // Never expose a host object. Public scalar properties are copied across;
            // everything else (methods, private state, the class itself) stays behind.
            $props = array();
            foreach (get_object_vars($value) as $k => $item) {
                if (is_object($item) || is_resource($item)) {
                    continue;
                }
                $props[(string) $k] = self::importValue($item);
            }
            return new JsObject($props);
        }
        return JsUndefined::instance();
    }

    private function hoistFunctions($body, $scope)
    {
        foreach ($body as $stmt) {
            if ($stmt['type'] === 'FunctionDeclaration') {
                $scope->declare(
                    $stmt['name'],
                    new JsFunction($stmt['name'], $stmt['params'], $stmt['body'], $scope)
                );
            }
        }
    }

    // ------------------------------------------------------------------- statements

    /**
     * @param mixed $completion by-reference: the value of the last expression statement,
     *                          which is what the program as a whole evaluates to
     * @return string|null 'return', 'break', 'continue' or null
     */
    private function execStatement($stmt, $scope, &$completion)
    {
        $this->tick();

        switch ($stmt['type']) {
            case 'ExpressionStatement':
                $completion = $this->evaluate($stmt['expression'], $scope);
                return null;

            case 'VarDeclaration':
                $isConst = isset($stmt['kind']) && $stmt['kind'] === 'const';
                foreach ($stmt['declarations'] as $decl) {
                    $value = $decl['init'] === null
                        ? JsUndefined::instance()
                        : $this->evaluate($decl['init'], $scope);
                    $scope->declare($decl['name'], $value, $isConst);
                }
                return null;

            case 'FunctionDeclaration':
                // Already hoisted for this block; re-declare so a nested block that is
                // executed more than once rebinds against its own scope.
                $scope->declare(
                    $stmt['name'],
                    new JsFunction($stmt['name'], $stmt['params'], $stmt['body'], $scope)
                );
                return null;

            case 'Block':
                $inner = new JsScope($scope);
                $this->hoistFunctions($stmt['body'], $inner);
                foreach ($stmt['body'] as $s) {
                    $signal = $this->execStatement($s, $inner, $completion);
                    if ($signal !== null) {
                        return $signal;
                    }
                }
                return null;

            case 'If':
                if (JsRuntime::toBoolean($this->evaluate($stmt['test'], $scope))) {
                    return $this->execStatement($stmt['then'], $scope, $completion);
                }
                if ($stmt['else'] !== null) {
                    return $this->execStatement($stmt['else'], $scope, $completion);
                }
                return null;

            case 'While':
                while (JsRuntime::toBoolean($this->evaluate($stmt['test'], $scope))) {
                    $this->tick();
                    $signal = $this->execStatement($stmt['body'], $scope, $completion);
                    if ($signal === 'return') {
                        return $signal;
                    }
                    if ($signal === self::$BREAK) {
                        break;
                    }
                }
                return null;

            case 'DoWhile':
                do {
                    $this->tick();
                    $signal = $this->execStatement($stmt['body'], $scope, $completion);
                    if ($signal === 'return') {
                        return $signal;
                    }
                    if ($signal === self::$BREAK) {
                        break;
                    }
                } while (JsRuntime::toBoolean($this->evaluate($stmt['test'], $scope)));
                return null;

            case 'For':
                $loopScope = new JsScope($scope);
                if ($stmt['init'] !== null) {
                    $this->execStatement($stmt['init'], $loopScope, $completion);
                }
                while (true) {
                    $this->tick();
                    if ($stmt['test'] !== null
                        && !JsRuntime::toBoolean($this->evaluate($stmt['test'], $loopScope))
                    ) {
                        break;
                    }
                    $signal = $this->execStatement($stmt['body'], $loopScope, $completion);
                    if ($signal === 'return') {
                        return $signal;
                    }
                    if ($signal === self::$BREAK) {
                        break;
                    }
                    if ($stmt['update'] !== null) {
                        $this->evaluate($stmt['update'], $loopScope);
                    }
                }
                return null;

            case 'Return':
                $this->returnValue = $stmt['argument'] === null
                    ? JsUndefined::instance()
                    : $this->evaluate($stmt['argument'], $scope);
                return 'return';

            case 'Break':
                return self::$BREAK;

            case 'Continue':
                return self::$CONTINUE;

            case 'Empty':
                return null;
        }

        throw new JsError('Unsupported statement: ' . $stmt['type']);
    }

    // ------------------------------------------------------------------ expressions

    private function evaluate($node, $scope)
    {
        $this->tick();

        switch ($node['type']) {
            case 'Literal':
                return $node['value'];

            case 'Identifier':
                return $this->resolveIdentifier($node['name'], $scope);

            case 'ArrayLiteral':
                $items = array();
                foreach ($node['elements'] as $el) {
                    $items[] = $this->evaluate($el, $scope);
                }
                JsRuntime::guardArrayLength(count($items));
                return new JsArray($items);

            case 'ObjectLiteral':
                $props = array();
                foreach ($node['properties'] as $p) {
                    $props[$p['key']] = $this->evaluate($p['value'], $scope);
                }
                return new JsObject($props);

            case 'FunctionExpression':
                if ($node['name'] !== null) {
                    // A named function expression can refer to itself by name. Bind it in
                    // a scope of its own so the name is visible inside the body but does
                    // not leak outward — and so a self-call is refused by the recursion
                    // guard rather than dying as "not a function".
                    $fnScope = new JsScope($scope);
                    $fn = new JsFunction($node['name'], $node['params'], $node['body'], $fnScope);
                    $fnScope->declare($node['name'], $fn);
                    return $fn;
                }
                return new JsFunction($node['name'], $node['params'], $node['body'], $scope);

            case 'Unary':
                return $this->evalUnary($node, $scope);

            case 'Binary':
                return $this->evalBinary(
                    $node['op'],
                    $this->evaluate($node['left'], $scope),
                    $this->evaluate($node['right'], $scope)
                );

            case 'Logical':
                $left = $this->evaluate($node['left'], $scope);
                // && and || return an OPERAND, not a boolean — the `x || 0` idiom in the
                // existing payroll formulas depends on this.
                if ($node['op'] === '&&') {
                    return JsRuntime::toBoolean($left) ? $this->evaluate($node['right'], $scope) : $left;
                }
                return JsRuntime::toBoolean($left) ? $left : $this->evaluate($node['right'], $scope);

            case 'Conditional':
                return JsRuntime::toBoolean($this->evaluate($node['test'], $scope))
                    ? $this->evaluate($node['then'], $scope)
                    : $this->evaluate($node['else'], $scope);

            case 'Sequence':
                $this->evaluate($node['left'], $scope);
                return $this->evaluate($node['right'], $scope);

            case 'Assign':
                return $this->evalAssign($node, $scope);

            case 'Update':
                return $this->evalUpdate($node, $scope);

            case 'Member':
                return $this->evalMember($node, $scope);

            case 'Call':
                return $this->evalCall($node, $scope);
        }

        throw new JsError('Unsupported expression: ' . $node['type']);
    }

    private function resolveIdentifier($name, $scope)
    {
        if ($scope->has($name)) {
            return $scope->get($name);
        }
        if (JsBuiltins::isGlobal($name)) {
            return JsBuiltins::getGlobal($name);
        }
        // Unknown names are `undefined` rather than an error, matching JS for reads of
        // globals. eval/Function/require/process/globalThis all land here.
        return JsUndefined::instance();
    }

    private function evalUnary($node, $scope)
    {
        if ($node['op'] === 'typeof') {
            // typeof on an unbound name must not throw, so resolve it leniently.
            if ($node['argument']['type'] === 'Identifier') {
                return JsRuntime::typeOf($this->resolveIdentifier($node['argument']['name'], $scope));
            }
            return JsRuntime::typeOf($this->evaluate($node['argument'], $scope));
        }

        $value = $this->evaluate($node['argument'], $scope);
        switch ($node['op']) {
            case '-':
                $n = JsRuntime::toNumber($value);
                return is_nan($n) ? NAN : -$n;
            case '+':
                return JsRuntime::toNumber($value);
            case '!':
                return !JsRuntime::toBoolean($value);
        }
        throw new JsError('Unsupported unary operator ' . $node['op']);
    }

    private function evalBinary($op, $left, $right)
    {
        switch ($op) {
            case '+':   return JsRuntime::add($left, $right);
            case '-':   return JsRuntime::toNumber($left) - JsRuntime::toNumber($right);
            case '*':   return JsRuntime::toNumber($left) * JsRuntime::toNumber($right);
            case '/':   return JsRuntime::divide($left, $right);
            case '%':   return JsRuntime::modulo($left, $right);
            case '===': return JsRuntime::strictEquals($left, $right);
            case '!==': return !JsRuntime::strictEquals($left, $right);
            case '==':  return JsRuntime::looseEquals($left, $right);
            case '!=':  return !JsRuntime::looseEquals($left, $right);
            case '<':
            case '>':
            case '<=':
            case '>=':
                return JsRuntime::compare($op, $left, $right);
        }
        throw new JsError('Unsupported operator ' . $op);
    }

    private function evalAssign($node, $scope)
    {
        $target = $node['target'];

        if ($node['op'] === '=') {
            $value = $this->evaluate($node['value'], $scope);
        } else {
            $current = $this->evaluate($target, $scope);
            $operand = $this->evaluate($node['value'], $scope);
            $binaryOp = substr($node['op'], 0, 1);
            $value = $this->evalBinary($binaryOp, $current, $operand);
        }

        $this->assignTo($target, $value, $scope);
        return $value;
    }

    private function assignTo($target, $value, $scope)
    {
        if ($target['type'] === 'Identifier') {
            $scope->set($target['name'], $value);
            return;
        }

        // Member assignment: arrays and plain objects only. Strings are immutable in JS
        // and every other receiver is a builtin, which must stay read-only.
        $object = $this->evaluate($target['object'], $scope);
        $property = $target['computed']
            ? $this->evaluate($target['property'], $scope)
            : $target['property']['value'];

        if ($object instanceof JsArray) {
            $index = JsRuntime::toNumber($property);
            if (!is_nan($index) && $index >= 0 && $index == floor($index)) {
                $i = (int) $index;
                JsRuntime::guardArrayLength($i + 1);
                // Assigning past the end fills the gap with undefined, as JS does.
                for ($j = count($object->items); $j < $i; $j++) {
                    $object->items[$j] = JsUndefined::instance();
                }
                $object->items[$i] = $value;
                return;
            }
            if (JsRuntime::toString($property) === 'length') {
                $newLength = JsRuntime::toInteger($value);
                JsRuntime::guardArrayLength($newLength);
                $object->items = array_slice(
                    array_pad($object->items, $newLength, JsUndefined::instance()),
                    0,
                    max(0, $newLength)
                );
                return;
            }
            throw new JsError('Only numeric indexes can be assigned on an array');
        }

        if ($object instanceof JsObject) {
            $object->props[JsRuntime::toString($property)] = $value;
            return;
        }

        throw new JsError('Cannot assign to a property of this value');
    }

    private function evalUpdate($node, $scope)
    {
        $old = JsRuntime::toNumber($this->evaluate($node['argument'], $scope));
        $new = $node['op'] === '++' ? $old + 1 : $old - 1;
        $this->assignTo($node['argument'], $new, $scope);
        return $node['prefix'] ? $new : $old;
    }

    private function evalMember($node, $scope)
    {
        $object = $this->evaluate($node['object'], $scope);
        $property = $node['computed']
            ? $this->evaluate($node['property'], $scope)
            : $node['property']['value'];

        return $this->getMember($object, $property);
    }

    private function getMember($object, $property)
    {
        $name = JsRuntime::toString($property);

        if ($object === null || JsRuntime::isUndefined($object)) {
            throw new JsError("Cannot read property '" . $name . "' of "
                . ($object === null ? 'null' : 'undefined'));
        }

        if ($object instanceof JsNamespace) {
            return JsBuiltins::namespaceMember($object->name, $name);
        }
        if (is_string($object)) {
            if (ctype_digit($name)) {
                $i = (int) $name;
                return $i < strlen($object) ? $object[$i] : JsUndefined::instance();
            }
            return JsBuiltins::stringMember($object, $name);
        }
        if ($object instanceof JsArray) {
            $index = JsRuntime::toNumber($property);
            if (!is_nan($index) && $index >= 0 && $index == floor($index)) {
                $i = (int) $index;
                return array_key_exists($i, $object->items) ? $object->items[$i] : JsUndefined::instance();
            }
            return JsBuiltins::arrayMember($object, $name);
        }
        if (is_float($object) || is_int($object)) {
            return JsBuiltins::numberMember($object, $name);
        }
        if ($object instanceof JsObject) {
            // A plain map lookup. There is no prototype chain, so '__proto__' and
            // 'constructor' are ordinary absent keys rather than a route to a host
            // constructor — the escape that a JS-hosted interpreter must defend against
            // simply has nowhere to start here.
            return array_key_exists($name, $object->props)
                ? $object->props[$name]
                : JsUndefined::instance();
        }
        if ($object instanceof JsFunction) {
            if ($name === 'name') {
                return $object->name === null ? '' : $object->name;
            }
            if ($name === 'length') {
                return (float) count($object->params);
            }
            return JsUndefined::instance();
        }

        return JsUndefined::instance();
    }

    private function evalCall($node, $scope)
    {
        $callee = $node['callee'];

        $args = array();
        foreach ($node['arguments'] as $argNode) {
            $args[] = $this->evaluate($argNode, $scope);
        }

        // A method call needs its receiver, so member callees are handled before the
        // callee is reduced to a value.
        if ($callee['type'] === 'Member') {
            $object = $this->evaluate($callee['object'], $scope);
            $property = $callee['computed']
                ? JsRuntime::toString($this->evaluate($callee['property'], $scope))
                : $callee['property']['value'];
            return $this->callMethod($object, $property, $args);
        }

        $fn = $this->evaluate($callee, $scope);
        return $this->callValue($fn, $args, $this->describeCallee($callee));
    }

    private function describeCallee($callee)
    {
        return $callee['type'] === 'Identifier' ? $callee['name'] : 'expression';
    }

    private function callMethod($object, $method, $args)
    {
        $invoker = $this->makeInvoker();

        if ($object === null || JsRuntime::isUndefined($object)) {
            throw new JsError("Cannot call '" . $method . "' on "
                . ($object === null ? 'null' : 'undefined'));
        }
        if ($object instanceof JsNamespace) {
            return JsBuiltins::callNamespaceMethod($object->name, $method, $args, $invoker);
        }
        if (is_string($object)) {
            return JsBuiltins::callStringMethod($object, $method, $args, $invoker);
        }
        if ($object instanceof JsArray) {
            return JsBuiltins::callArrayMethod($object, $method, $args, $invoker);
        }
        if (is_float($object) || is_int($object)) {
            return JsBuiltins::callNumberMethod($object, $method, $args, $invoker);
        }
        if ($object instanceof JsObject) {
            $fn = array_key_exists($method, $object->props) ? $object->props[$method] : null;
            if ($fn instanceof JsFunction) {
                return $this->callFunction($fn, $args);
            }
            throw new JsError($method . ' is not a function');
        }
        throw new JsError('Cannot call ' . $method . ' on this value');
    }

    /** A callable the builtins use to run guest callbacks under the same budgets. */
    private function makeInvoker()
    {
        $self = $this;
        return function ($fn, $args) use ($self) {
            return $self->invokeCallback($fn, $args);
        };
    }

    /** Public only so the closure above can reach it on PHP 7.3 (no first-class callables). */
    public function invokeCallback($fn, $args)
    {
        if ($fn instanceof JsFunction) {
            return $this->callFunction($fn, $args);
        }
        throw new JsError('Expected a function');
    }

    private function callValue($fn, $args, $description)
    {
        if ($fn instanceof JsFunction) {
            return $this->callFunction($fn, $args);
        }
        if ($fn instanceof JsNativeFunction) {
            return JsBuiltins::callGlobalFunction($fn->name, $args);
        }
        if ($fn instanceof JsNamespace) {
            // Number(x), String(x), Boolean(x), Array(n) used as conversion functions.
            return $this->callConversion($fn->name, $args);
        }
        if ($fn instanceof JsNativeMethod) {
            throw new JsError($fn->name . ' cannot be called detached from its object');
        }
        throw new JsError($description . ' is not a function');
    }

    private function callConversion($name, $args)
    {
        $arg0 = array_key_exists(0, $args) ? $args[0] : JsUndefined::instance();
        switch ($name) {
            case 'Number':
                return count($args) === 0 ? 0.0 : JsRuntime::toNumber($arg0);
            case 'String':
                return count($args) === 0 ? '' : JsRuntime::toString($arg0);
            case 'Boolean':
                return count($args) !== 0 && JsRuntime::toBoolean($arg0);
            case 'Array':
                if (count($args) === 1 && (is_float($arg0) || is_int($arg0))) {
                    $n = JsRuntime::toInteger($arg0);
                    JsRuntime::guardArrayLength($n);
                    return new JsArray(array_fill(0, max(0, $n), JsUndefined::instance()));
                }
                return new JsArray($args);
        }
        throw new JsError($name . ' is not callable');
    }

    /**
     * Invoke a user-defined function.
     *
     * The no-recursion rule is enforced here by identity: if this exact function object
     * is already executing anywhere up the stack, the call is refused. Identity rather
     * than name is what makes indirect recursion (f -> g -> f) and recursion through a
     * callback (arr.map(f) inside f) fail too, which a name check or a depth limit
     * would both miss.
     */
    private function callFunction($fn, $args)
    {
        foreach ($this->callStack as $active) {
            if ($active === $fn) {
                $label = $fn->name === null ? 'an anonymous function' : '"' . $fn->name . '"';
                throw new JsError(
                    'Recursion is not allowed (' . $label . ' called itself)',
                    null,
                    JsError::KIND_LIMIT
                );
            }
        }
        if (count($this->callStack) >= self::MAX_CALL_DEPTH) {
            throw new JsError('Maximum call depth exceeded', null, JsError::KIND_LIMIT);
        }

        $this->callStack[] = $fn;
        try {
            $callScope = new JsScope($fn->scope);
            foreach ($fn->params as $i => $param) {
                $callScope->declare(
                    $param,
                    array_key_exists($i, $args) ? $args[$i] : JsUndefined::instance()
                );
            }
            $this->hoistFunctions($fn->body['body'], $callScope);

            $completion = JsUndefined::instance();
            foreach ($fn->body['body'] as $stmt) {
                $signal = $this->execStatement($stmt, $callScope, $completion);
                if ($signal === 'return') {
                    $value = $this->returnValue;
                    $this->returnValue = null;
                    return $value;
                }
            }
            return JsUndefined::instance();
        } finally {
            array_pop($this->callStack);
        }
    }

    /** Charge one unit against the step and time budgets. */
    private function tick()
    {
        $this->steps++;
        if ($this->steps > self::MAX_STEPS) {
            throw new JsError('Script exceeded the maximum number of operations', null, JsError::KIND_LIMIT);
        }
        // microtime is not free, so only consult the clock periodically.
        if (($this->steps & 0x3FF) === 0 && microtime(true) > $this->deadline) {
            throw new JsError('Script exceeded the maximum execution time', null, JsError::KIND_LIMIT);
        }
    }
}
