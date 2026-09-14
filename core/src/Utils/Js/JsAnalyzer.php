<?php

namespace Utils\Js;

/**
 * Static analysis of a parsed script, for validation feedback.
 *
 * Its job is to answer the question a payroll-column author actually has: "which
 * names does my formula depend on?" A script that reads `gpssRate` when the column
 * supplies `gpssaRate` is perfectly valid JavaScript — it silently evaluates to
 * NaN, or to 0 once `|| 0` catches it, and the column quietly computes the wrong
 * figure. Nothing at runtime will complain. Reporting the free variable up front is
 * the only way the author finds out.
 *
 * Walks the AST tracking declared names per scope, and collects every identifier read
 * that is neither declared nor a built-in global.
 */
class JsAnalyzer
{
    private $scopes = array();
    private $free = array();
    private $functions = array();

    /**
     * @param array $ast from JsParser
     * @return array{free: string[], functions: string[]}
     */
    public static function analyze($ast)
    {
        $analyzer = new JsAnalyzer();
        return $analyzer->inspect($ast);
    }

    private function inspect($ast)
    {
        $this->scopes = array(array());
        $this->free = array();
        $this->functions = array();

        $this->hoist($ast['body']);
        foreach ($ast['body'] as $stmt) {
            $this->walkStatement($stmt);
        }

        return array(
            'free' => array_values(array_unique($this->free)),
            'functions' => array_values(array_unique($this->functions)),
        );
    }

    // ------------------------------------------------------------------ scope helpers

    private function push()
    {
        $this->scopes[] = array();
    }

    private function pop()
    {
        array_pop($this->scopes);
    }

    private function declareName($name)
    {
        $this->scopes[count($this->scopes) - 1][$name] = true;
    }

    private function isDeclared($name)
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$i][$name])) {
                return true;
            }
        }
        return false;
    }

    private function useName($name)
    {
        if ($this->isDeclared($name) || JsBuiltins::isGlobal($name)) {
            return;
        }
        $this->free[] = $name;
    }

    /** Function declarations are visible before their definition, so record them first. */
    private function hoist($body)
    {
        foreach ($body as $stmt) {
            if ($stmt['type'] === 'FunctionDeclaration') {
                $this->declareName($stmt['name']);
                $this->functions[] = $stmt['name'];
            }
        }
    }

    // --------------------------------------------------------------------- statements

    private function walkStatement($stmt)
    {
        switch ($stmt['type']) {
            case 'ExpressionStatement':
                $this->walkExpression($stmt['expression']);
                return;

            case 'VarDeclaration':
                foreach ($stmt['declarations'] as $decl) {
                    if ($decl['init'] !== null) {
                        $this->walkExpression($decl['init']);
                    }
                    // Declared AFTER walking the initialiser, so `var x = x` correctly
                    // reports the right-hand x as free.
                    $this->declareName($decl['name']);
                }
                return;

            case 'FunctionDeclaration':
                $this->declareName($stmt['name']);
                $this->functions[] = $stmt['name'];
                $this->walkFunctionBody($stmt['params'], $stmt['body']);
                return;

            case 'Block':
                $this->push();
                $this->hoist($stmt['body']);
                foreach ($stmt['body'] as $s) {
                    $this->walkStatement($s);
                }
                $this->pop();
                return;

            case 'If':
                $this->walkExpression($stmt['test']);
                $this->walkStatement($stmt['then']);
                if ($stmt['else'] !== null) {
                    $this->walkStatement($stmt['else']);
                }
                return;

            case 'While':
            case 'DoWhile':
                $this->walkExpression($stmt['test']);
                $this->walkStatement($stmt['body']);
                return;

            case 'For':
                $this->push();
                if ($stmt['init'] !== null) {
                    $this->walkStatement($stmt['init']);
                }
                if ($stmt['test'] !== null) {
                    $this->walkExpression($stmt['test']);
                }
                if ($stmt['update'] !== null) {
                    $this->walkExpression($stmt['update']);
                }
                $this->walkStatement($stmt['body']);
                $this->pop();
                return;

            case 'Return':
                if ($stmt['argument'] !== null) {
                    $this->walkExpression($stmt['argument']);
                }
                return;

            case 'Break':
            case 'Continue':
            case 'Empty':
                return;
        }
    }

    private function walkFunctionBody($params, $body)
    {
        $this->push();
        foreach ($params as $param) {
            $this->declareName($param);
        }
        $this->hoist($body['body']);
        foreach ($body['body'] as $stmt) {
            $this->walkStatement($stmt);
        }
        $this->pop();
    }

    // -------------------------------------------------------------------- expressions

    private function walkExpression($node)
    {
        switch ($node['type']) {
            case 'Literal':
                return;

            case 'Identifier':
                $this->useName($node['name']);
                return;

            case 'ArrayLiteral':
                foreach ($node['elements'] as $el) {
                    $this->walkExpression($el);
                }
                return;

            case 'ObjectLiteral':
                // Keys are not identifiers; only the values are expressions.
                foreach ($node['properties'] as $p) {
                    $this->walkExpression($p['value']);
                }
                return;

            case 'FunctionExpression':
                $this->push();
                if ($node['name'] !== null) {
                    $this->declareName($node['name']); // visible to itself
                }
                $this->walkFunctionBody($node['params'], $node['body']);
                $this->pop();
                return;

            case 'Unary':
                // `typeof x` on an unbound name is the idiomatic existence check and
                // must not be reported as a missing variable.
                if ($node['op'] === 'typeof' && $node['argument']['type'] === 'Identifier') {
                    return;
                }
                $this->walkExpression($node['argument']);
                return;

            case 'Binary':
            case 'Logical':
                $this->walkExpression($node['left']);
                $this->walkExpression($node['right']);
                return;

            case 'Sequence':
                $this->walkExpression($node['left']);
                $this->walkExpression($node['right']);
                return;

            case 'Conditional':
                $this->walkExpression($node['test']);
                $this->walkExpression($node['then']);
                $this->walkExpression($node['else']);
                return;

            case 'Assign':
                $this->walkExpression($node['value']);
                if ($node['target']['type'] === 'Identifier') {
                    // Assigning to an undeclared name creates it, so it is not "missing"
                    // from here on — but a compound assignment READS it first.
                    if ($node['op'] !== '=') {
                        $this->useName($node['target']['name']);
                    }
                    $this->declareName($node['target']['name']);
                } else {
                    $this->walkExpression($node['target']);
                }
                return;

            case 'Update':
                $this->walkExpression($node['argument']);
                return;

            case 'Member':
                $this->walkExpression($node['object']);
                // A dotted property name is not a variable reference; a computed one is.
                if ($node['computed']) {
                    $this->walkExpression($node['property']);
                }
                return;

            case 'Call':
                $this->walkExpression($node['callee']);
                foreach ($node['arguments'] as $arg) {
                    $this->walkExpression($arg);
                }
                return;
        }
    }
}
