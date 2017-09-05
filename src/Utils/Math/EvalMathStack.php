<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:55 AM
 */

namespace Utils\Math;

// for internal use
class EvalMathStack
{

    var $stack = array();
    var $count = 0;

    function push($val)
    {
        $this->stack[$this->count] = $val;
        $this->count++;
    }

    function pop()
    {
        if ($this->count > 0) {
            $this->count--;
            return $this->stack[$this->count];
        }
        return null;
    }

    function last($n = 1)
    {
        if ($this->count - $n >= 0) {
            return $this->stack[$this->count - $n];
        }
        return null;
    }
}
