<?php
namespace Test\Unit;

use Utils\Math\EvalMath;

class EvalMathUnit extends \TestTemplate
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testTran()
    {
        $m = new EvalMath();
        // basic evaluation:
        $result = $m->evaluate('2+2');
        self::assertEquals(4, $result);
        // supports: order of operation; parentheses; negation; built-in functions
        $result = $m->evaluate('-8(5/2)^2*(1-sqrt(4))-8');
        self::assertEquals(42, $result);
        // create your own variables
        $m->evaluate('a = e^(ln(pi))');
        // or functions
        $m->evaluate('f(x,y) = x^2 + y^2 - 2x*y + 1');
        // and then use them
        $result = $m->evaluate('3*f(42,a)');
        self::assertEquals(4533, round($result));
    }
}
