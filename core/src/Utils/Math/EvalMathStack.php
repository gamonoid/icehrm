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

    protected $stack = array();
    public $count = 0;

    public function push($val)
    {
        $this->stack[$this->count] = $val;
        $this->count++;
    }

    public function pop()
    {
        if ($this->count > 0) {
            $this->count--;
            return $this->stack[$this->count];
        }
        return null;
    }

    public function last($n = 1)
    {
        if ($this->count - $n >= 0) {
            return $this->stack[$this->count - $n];
        }
        return null;
    }
}
