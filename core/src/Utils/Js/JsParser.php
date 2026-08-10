<?php

namespace Utils\Js;

/**
 * Recursive-descent parser for the accepted JavaScript subset.
 *
 * THE GRAMMAR IS THE ALLOWLIST. This is the sandbox's first and strongest layer:
 * a construct with no production here cannot be represented in the AST, so the
 * interpreter never has to decide whether to permit it. Rejected outright:
 *
 *   new / class / this      no object construction, no receiver binding
 *   => / async / await      no arrow or asynchronous forms
 *   import / export         no module system
 *   yield / function*       no generators
 *   with / label:           no dynamic scope, no non-local jumps beyond break/continue
 *   get / set               no accessors, so no code can run on property access
 *   delete / in / instanceof
 *   ?. and ??               kept out to hold the surface at ES5-level
 *
 * `eval` and `Function` need no special case: they are ordinary identifiers that
 * resolve to nothing, because the runtime never defines them (see JsRuntime).
 *
 * AST nodes are plain arrays keyed by 'type'.
 */
class JsParser
{
    /** Reserved words that must never be usable as a binding name. */
    private static $reserved = array(
        'break' => 1, 'case' => 1, 'catch' => 1, 'const' => 1, 'continue' => 1,
        'default' => 1, 'delete' => 1, 'do' => 1, 'else' => 1, 'false' => 1,
        'finally' => 1, 'for' => 1, 'function' => 1, 'if' => 1, 'in' => 1,
        'instanceof' => 1, 'let' => 1, 'new' => 1, 'null' => 1, 'return' => 1,
        'switch' => 1, 'this' => 1, 'throw' => 1, 'true' => 1, 'try' => 1,
        'typeof' => 1, 'undefined' => 1, 'var' => 1, 'void' => 1, 'while' => 1,
        'with' => 1, 'class' => 1, 'export' => 1, 'import' => 1, 'super' => 1,
        'yield' => 1, 'async' => 1, 'await' => 1, 'enum' => 1, 'extends' => 1,
    );

    /** Constructs rejected by name, with an explanation instead of a bare syntax error. */
    private static $banned = array(
        'new' => 'object construction (new)',
        'class' => 'class declarations',
        'this' => 'this',
        'import' => 'import',
        'export' => 'export',
        'async' => 'async functions',
        'await' => 'await',
        'yield' => 'generators',
        'with' => 'with blocks',
        'try' => 'try/catch',
        'throw' => 'throw',
        'switch' => 'switch statements',
        'delete' => 'the delete operator',
        'instanceof' => 'instanceof',
        'void' => 'the void operator',
        'super' => 'super',
    );

    /**
     * Cap on how deeply expressions and statements may nest.
     *
     * The parser is recursive descent, so nesting depth in the SOURCE becomes PHP call
     * depth. Without this, a script of a few thousand open parentheses overflows the C
     * stack and segfaults the process — an uncatchable crash, unlike every other
     * failure here. 200 is far past anything a payroll formula needs.
     */
    const MAX_NESTING_DEPTH = 200;

    private $tokens;
    private $pos = 0;
    private $depth = 0;

    public function __construct($tokens)
    {
        $this->tokens = $tokens;
    }

    public static function parseSource($source)
    {
        $lexer = new JsLexer($source);
        $parser = new JsParser($lexer->tokenize());
        return $parser->parseProgram();
    }

    public function parseProgram()
    {
        $body = array();
        while (!$this->atEof()) {
            $body[] = $this->parseStatement();
        }
        return array('type' => 'Program', 'body' => $body);
    }

    // ---------------------------------------------------------------- statements

    private function parseStatement()
    {
        $this->enter();
        try {
            return $this->parseStatementInner();
        } finally {
            $this->depth--;
        }
    }

    private function parseStatementInner()
    {
        $tok = $this->peek();

        if ($tok['type'] === 'name') {
            $kw = $tok['value'];

            if (isset(self::$banned[$kw])) {
                throw new JsError(
                    'Unsupported: ' . self::$banned[$kw],
                    $tok['line'],
                    JsError::KIND_UNSUPPORTED
                );
            }
            if ($kw === 'var' || $kw === 'let' || $kw === 'const') {
                return $this->parseVarStatement();
            }
            if ($kw === 'function') {
                return $this->parseFunctionDeclaration();
            }
            if ($kw === 'if') {
                return $this->parseIf();
            }
            if ($kw === 'for') {
                return $this->parseFor();
            }
            if ($kw === 'while') {
                return $this->parseWhile();
            }
            if ($kw === 'do') {
                return $this->parseDoWhile();
            }
            if ($kw === 'return') {
                return $this->parseReturn();
            }
            if ($kw === 'break' || $kw === 'continue') {
                $this->next();
                $this->consumeSemicolon();
                return array('type' => $kw === 'break' ? 'Break' : 'Continue');
            }
        }

        if ($this->isPunct('{')) {
            return $this->parseBlock();
        }
        if ($this->isPunct(';')) {
            $this->next();
            return array('type' => 'Empty');
        }

        $expr = $this->parseExpression();
        $this->consumeSemicolon();
        return array('type' => 'ExpressionStatement', 'expression' => $expr);
    }

    private function parseBlock()
    {
        $this->expectPunct('{');
        $body = array();
        while (!$this->isPunct('}')) {
            if ($this->atEof()) {
                throw new JsError('Unexpected end of input: unclosed \'{\'', $this->peek()['line'], JsError::KIND_SYNTAX);
            }
            $body[] = $this->parseStatement();
        }
        $this->expectPunct('}');
        return array('type' => 'Block', 'body' => $body);
    }

    private function parseVarStatement()
    {
        $decl = $this->parseVarDeclaration();
        $this->consumeSemicolon();
        return $decl;
    }

    /** Shared by statements and the `for (var i = 0; ...)` initialiser. */
    private function parseVarDeclaration()
    {
        $kindToken = $this->next(); // var | let | const
        $kind = $kindToken['value'];
        $decls = array();
        while (true) {
            $name = $this->expectIdentifier();
            $init = null;
            if ($this->isPunct('=')) {
                $this->next();
                $init = $this->parseAssignment();
            }
            $decls[] = array('name' => $name, 'init' => $init);
            if ($this->isPunct(',')) {
                $this->next();
                continue;
            }
            break;
        }
        return array('type' => 'VarDeclaration', 'kind' => $kind, 'declarations' => $decls);
    }

    private function parseFunctionDeclaration()
    {
        $this->next(); // function
        if ($this->isPunct('*')) {
            throw new JsError('Unsupported: generator functions', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
        }
        $name = $this->expectIdentifier();
        $params = $this->parseParams();
        $body = $this->parseBlock();
        return array('type' => 'FunctionDeclaration', 'name' => $name, 'params' => $params, 'body' => $body);
    }

    private function parseParams()
    {
        $this->expectPunct('(');
        $params = array();
        while (!$this->isPunct(')')) {
            if ($this->isPunct('.')) {
                throw new JsError('Unsupported: rest parameters', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
            }
            $params[] = $this->expectIdentifier();
            if ($this->isPunct(',')) {
                $this->next();
            }
        }
        $this->expectPunct(')');
        return $params;
    }

    private function parseIf()
    {
        $this->next();
        $this->expectPunct('(');
        $test = $this->parseExpression();
        $this->expectPunct(')');
        $then = $this->parseStatement();
        $else = null;
        if ($this->isName('else')) {
            $this->next();
            $else = $this->parseStatement();
        }
        return array('type' => 'If', 'test' => $test, 'then' => $then, 'else' => $else);
    }

    private function parseWhile()
    {
        $this->next();
        $this->expectPunct('(');
        $test = $this->parseExpression();
        $this->expectPunct(')');
        $body = $this->parseStatement();
        return array('type' => 'While', 'test' => $test, 'body' => $body);
    }

    private function parseDoWhile()
    {
        $this->next();
        $body = $this->parseStatement();
        if (!$this->isName('while')) {
            throw new JsError('Expected "while" after a do block', $this->peek()['line'], JsError::KIND_SYNTAX);
        }
        $this->next();
        $this->expectPunct('(');
        $test = $this->parseExpression();
        $this->expectPunct(')');
        $this->consumeSemicolon();
        return array('type' => 'DoWhile', 'test' => $test, 'body' => $body);
    }

    /**
     * Only the three-part `for (init; test; update)` form. for-in and for-of are
     * rejected: iterating an object's keys is the one place a script could start
     * probing the shape of what it was handed.
     */
    private function parseFor()
    {
        $this->next();
        $this->expectPunct('(');

        $init = null;
        if (!$this->isPunct(';')) {
            if ($this->isName('var') || $this->isName('let') || $this->isName('const')) {
                $init = $this->parseVarDeclaration();
            } else {
                $init = array('type' => 'ExpressionStatement', 'expression' => $this->parseExpression());
            }
        }
        if ($this->isName('in') || $this->isName('of')) {
            throw new JsError('Unsupported: for-in / for-of loops', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
        }
        $this->expectPunct(';');

        $test = $this->isPunct(';') ? null : $this->parseExpression();
        $this->expectPunct(';');
        $update = $this->isPunct(')') ? null : $this->parseExpression();
        $this->expectPunct(')');

        $body = $this->parseStatement();
        return array('type' => 'For', 'init' => $init, 'test' => $test, 'update' => $update, 'body' => $body);
    }

    private function parseReturn()
    {
        $this->next();
        $arg = null;
        if (!$this->isPunct(';') && !$this->isPunct('}') && !$this->atEof()) {
            $arg = $this->parseExpression();
        }
        $this->consumeSemicolon();
        return array('type' => 'Return', 'argument' => $arg);
    }

    // --------------------------------------------------------------- expressions

    private function parseExpression()
    {
        $this->enter();
        try {
            return $this->parseExpressionInner();
        } finally {
            $this->depth--;
        }
    }

    private function parseExpressionInner()
    {
        $expr = $this->parseAssignment();
        // The comma operator: keep the last value, as JS does.
        while ($this->isPunct(',')) {
            $this->next();
            $right = $this->parseAssignment();
            $expr = array('type' => 'Sequence', 'left' => $expr, 'right' => $right);
        }
        return $expr;
    }

    private function parseAssignment()
    {
        $left = $this->parseConditional();

        foreach (array('=', '+=', '-=', '*=', '/=', '%=') as $op) {
            if ($this->isPunct($op)) {
                if ($left['type'] !== 'Identifier' && $left['type'] !== 'Member') {
                    throw new JsError('Invalid assignment target', $this->peek()['line'], JsError::KIND_SYNTAX);
                }
                $this->next();
                $right = $this->parseAssignment();
                return array('type' => 'Assign', 'op' => $op, 'target' => $left, 'value' => $right);
            }
        }
        return $left;
    }

    private function parseConditional()
    {
        $test = $this->parseLogicalOr();
        if ($this->isPunct('?')) {
            $this->next();
            $then = $this->parseAssignment();
            $this->expectPunct(':');
            $else = $this->parseAssignment();
            return array('type' => 'Conditional', 'test' => $test, 'then' => $then, 'else' => $else);
        }
        return $test;
    }

    private function parseLogicalOr()
    {
        $left = $this->parseLogicalAnd();
        while ($this->isPunct('||')) {
            $this->next();
            $left = array('type' => 'Logical', 'op' => '||', 'left' => $left, 'right' => $this->parseLogicalAnd());
        }
        return $left;
    }

    private function parseLogicalAnd()
    {
        $left = $this->parseEquality();
        while ($this->isPunct('&&')) {
            $this->next();
            $left = array('type' => 'Logical', 'op' => '&&', 'left' => $left, 'right' => $this->parseEquality());
        }
        return $left;
    }

    private function parseEquality()
    {
        $left = $this->parseRelational();
        while ($this->isPunct('===') || $this->isPunct('!==') || $this->isPunct('==') || $this->isPunct('!=')) {
            $op = $this->next();
            $left = array('type' => 'Binary', 'op' => $op['value'], 'left' => $left, 'right' => $this->parseRelational());
        }
        return $left;
    }

    private function parseRelational()
    {
        $left = $this->parseAdditive();
        while ($this->isPunct('<') || $this->isPunct('>') || $this->isPunct('<=') || $this->isPunct('>=')) {
            $op = $this->next();
            $left = array('type' => 'Binary', 'op' => $op['value'], 'left' => $left, 'right' => $this->parseAdditive());
        }
        return $left;
    }

    private function parseAdditive()
    {
        $left = $this->parseMultiplicative();
        while ($this->isPunct('+') || $this->isPunct('-')) {
            $op = $this->next();
            $left = array('type' => 'Binary', 'op' => $op['value'], 'left' => $left, 'right' => $this->parseMultiplicative());
        }
        return $left;
    }

    private function parseMultiplicative()
    {
        $left = $this->parseUnary();
        while ($this->isPunct('*') || $this->isPunct('/') || $this->isPunct('%')) {
            $op = $this->next();
            $left = array('type' => 'Binary', 'op' => $op['value'], 'left' => $left, 'right' => $this->parseUnary());
        }
        return $left;
    }

    private function parseUnary()
    {
        if ($this->isPunct('-') || $this->isPunct('+') || $this->isPunct('!')) {
            $op = $this->next();
            return array('type' => 'Unary', 'op' => $op['value'], 'argument' => $this->parseUnary());
        }
        if ($this->isName('typeof')) {
            $this->next();
            return array('type' => 'Unary', 'op' => 'typeof', 'argument' => $this->parseUnary());
        }
        if ($this->isPunct('++') || $this->isPunct('--')) {
            $op = $this->next();
            $arg = $this->parseUnary();
            if ($arg['type'] !== 'Identifier' && $arg['type'] !== 'Member') {
                throw new JsError('Invalid target for ' . $op['value'], $op['line'], JsError::KIND_SYNTAX);
            }
            return array('type' => 'Update', 'op' => $op['value'], 'prefix' => true, 'argument' => $arg);
        }
        return $this->parsePostfix();
    }

    private function parsePostfix()
    {
        $expr = $this->parseCallMember();
        if ($this->isPunct('++') || $this->isPunct('--')) {
            if ($expr['type'] !== 'Identifier' && $expr['type'] !== 'Member') {
                throw new JsError('Invalid target for increment/decrement', $this->peek()['line'], JsError::KIND_SYNTAX);
            }
            $op = $this->next();
            return array('type' => 'Update', 'op' => $op['value'], 'prefix' => false, 'argument' => $expr);
        }
        return $expr;
    }

    /** Member access and calls, left to right: a.b[c](d).e */
    private function parseCallMember()
    {
        $expr = $this->parsePrimary();
        while (true) {
            if ($this->isPunct('.')) {
                $this->next();
                $name = $this->expectPropertyName();
                $expr = array(
                    'type' => 'Member',
                    'object' => $expr,
                    'property' => array('type' => 'Literal', 'value' => $name),
                    'computed' => false,
                );
                continue;
            }
            if ($this->isPunct('[')) {
                $this->next();
                $prop = $this->parseExpression();
                $this->expectPunct(']');
                $expr = array('type' => 'Member', 'object' => $expr, 'property' => $prop, 'computed' => true);
                continue;
            }
            if ($this->isPunct('(')) {
                $args = $this->parseArguments();
                $expr = array('type' => 'Call', 'callee' => $expr, 'arguments' => $args);
                continue;
            }
            break;
        }
        return $expr;
    }

    private function parseArguments()
    {
        $this->expectPunct('(');
        $args = array();
        while (!$this->isPunct(')')) {
            if ($this->isPunct('.')) {
                throw new JsError('Unsupported: spread arguments', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
            }
            $args[] = $this->parseAssignment();
            if ($this->isPunct(',')) {
                $this->next();
                continue;
            }
            // Neither a comma nor a close paren: the argument list is unterminated.
            // Reporting "expected )" points at the real mistake, where reporting the
            // next token ("unexpected ;") sends the author looking in the wrong place.
            if (!$this->isPunct(')')) {
                $this->expectPunct(')');
            }
        }
        $this->expectPunct(')');
        return $args;
    }

    private function parsePrimary()
    {
        $this->enter();
        try {
            return $this->parsePrimaryInner();
        } finally {
            $this->depth--;
        }
    }

    private function parsePrimaryInner()
    {
        $tok = $this->peek();

        if ($tok['type'] === 'num') {
            $this->next();
            return array('type' => 'Literal', 'value' => $tok['value']);
        }
        if ($tok['type'] === 'str') {
            $this->next();
            return array('type' => 'Literal', 'value' => $tok['value']);
        }

        if ($tok['type'] === 'name') {
            $v = $tok['value'];
            if (isset(self::$banned[$v])) {
                throw new JsError('Unsupported: ' . self::$banned[$v], $tok['line'], JsError::KIND_UNSUPPORTED);
            }
            if ($v === 'true' || $v === 'false') {
                $this->next();
                return array('type' => 'Literal', 'value' => $v === 'true');
            }
            if ($v === 'null') {
                $this->next();
                return array('type' => 'Literal', 'value' => null);
            }
            if ($v === 'undefined') {
                $this->next();
                return array('type' => 'Literal', 'value' => JsUndefined::instance());
            }
            if ($v === 'function') {
                $this->next();
                $name = null;
                if ($this->peek()['type'] === 'name' && !$this->isPunct('(')) {
                    $name = $this->expectIdentifier();
                }
                $params = $this->parseParams();
                $body = $this->parseBlock();
                return array('type' => 'FunctionExpression', 'name' => $name, 'params' => $params, 'body' => $body);
            }
            if (isset(self::$reserved[$v])) {
                throw new JsError(
                    'Unexpected reserved word "' . $v . '"',
                    $tok['line'],
                    JsError::KIND_SYNTAX
                );
            }
            $this->next();
            return array('type' => 'Identifier', 'name' => $v);
        }

        if ($this->isPunct('(')) {
            $this->next();
            $expr = $this->parseExpression();
            $this->expectPunct(')');
            // An arrow function is the one thing that looks like a parenthesised
            // expression until the => arrives; say so plainly rather than "unexpected =".
            if ($this->isPunct('=') && $this->peekAt(1)['value'] === '>') {
                throw new JsError('Unsupported: arrow functions', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
            }
            return $expr;
        }

        if ($this->isPunct('[')) {
            $this->next();
            $elements = array();
            while (!$this->isPunct(']')) {
                if ($this->isPunct('.')) {
                    throw new JsError('Unsupported: spread in array literals', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
                }
                $elements[] = $this->parseAssignment();
                if ($this->isPunct(',')) {
                    $this->next();
                }
            }
            $this->expectPunct(']');
            return array('type' => 'ArrayLiteral', 'elements' => $elements);
        }

        if ($this->isPunct('{')) {
            return $this->parseObjectLiteral();
        }

        throw new JsError(
            'Unexpected ' . ($tok['type'] === 'eof' ? 'end of input' : '"' . $tok['value'] . '"'),
            $tok['line'],
            JsError::KIND_SYNTAX
        );
    }

    private function parseObjectLiteral()
    {
        $this->expectPunct('{');
        $props = array();
        while (!$this->isPunct('}')) {
            $tok = $this->peek();
            if ($tok['type'] === 'str' || $tok['type'] === 'num') {
                $this->next();
                $key = is_float($tok['value']) ? JsRuntime::numberToString($tok['value']) : $tok['value'];
            } elseif ($tok['type'] === 'name') {
                $this->next();
                $key = $tok['value'];
            } else {
                throw new JsError('Invalid object key', $tok['line'], JsError::KIND_SYNTAX);
            }

            if ($this->isPunct('(')) {
                throw new JsError('Unsupported: shorthand object methods', $this->peek()['line'], JsError::KIND_UNSUPPORTED);
            }

            $this->expectPunct(':');
            $props[] = array('key' => $key, 'value' => $this->parseAssignment());

            if ($this->isPunct(',')) {
                $this->next();
            }
        }
        $this->expectPunct('}');
        return array('type' => 'ObjectLiteral', 'properties' => $props);
    }

    // -------------------------------------------------------------------- helpers

    private function enter()
    {
        $this->depth++;
        if ($this->depth > self::MAX_NESTING_DEPTH) {
            throw new JsError('Script nests too deeply', $this->peek()['line'], JsError::KIND_LIMIT);
        }
    }

    private function peek()
    {
        return $this->tokens[$this->pos];
    }

    private function peekAt($offset)
    {
        $i = $this->pos + $offset;
        return isset($this->tokens[$i]) ? $this->tokens[$i] : $this->tokens[count($this->tokens) - 1];
    }

    private function next()
    {
        $tok = $this->tokens[$this->pos];
        if ($tok['type'] !== 'eof') {
            $this->pos++;
        }
        return $tok;
    }

    private function atEof()
    {
        return $this->tokens[$this->pos]['type'] === 'eof';
    }

    private function isPunct($value)
    {
        $tok = $this->tokens[$this->pos];
        return $tok['type'] === 'punct' && $tok['value'] === $value;
    }

    private function isName($value)
    {
        $tok = $this->tokens[$this->pos];
        return $tok['type'] === 'name' && $tok['value'] === $value;
    }

    private function expectPunct($value)
    {
        if (!$this->isPunct($value)) {
            $tok = $this->peek();
            throw new JsError(
                'Expected "' . $value . '" but found '
                . ($tok['type'] === 'eof' ? 'end of input' : '"' . $tok['value'] . '"'),
                $tok['line'],
                JsError::KIND_SYNTAX
            );
        }
        return $this->next();
    }

    private function expectIdentifier()
    {
        $tok = $this->peek();
        if ($tok['type'] !== 'name') {
            throw new JsError('Expected a variable name', $tok['line'], JsError::KIND_SYNTAX);
        }
        if (isset(self::$reserved[$tok['value']])) {
            throw new JsError(
                '"' . $tok['value'] . '" is a reserved word and cannot be used as a name',
                $tok['line'],
                JsError::KIND_SYNTAX
            );
        }
        $this->next();
        return $tok['value'];
    }

    /** Property names after a dot may be reserved words (obj.default), as in JS. */
    private function expectPropertyName()
    {
        $tok = $this->peek();
        if ($tok['type'] !== 'name') {
            throw new JsError('Expected a property name', $tok['line'], JsError::KIND_SYNTAX);
        }
        $this->next();
        return $tok['value'];
    }

    /** Semicolons are optional, matching JS automatic semicolon insertion closely enough. */
    private function consumeSemicolon()
    {
        if ($this->isPunct(';')) {
            $this->next();
        }
    }
}
