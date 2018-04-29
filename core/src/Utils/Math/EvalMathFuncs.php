<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:55 AM
 */

namespace Utils\Math;

// spreadsheet functions emulation
class EvalMathFuncs
{

    protected static $randomseed = null;
    
    public static function average()
    {
        $args = func_get_args();
        return (call_user_func_array(array('self', 'sum'), $args) / count($args));
    }

    public static function max()
    {
        $args = func_get_args();
        $res = array_pop($args);
        foreach ($args as $a) {
            if ($res < $a) {
                $res = $a;
            }
        }
        return $res;
    }

    public static function min()
    {
        $args = func_get_args();
        $res = array_pop($args);
        foreach ($args as $a) {
            if ($res > $a) {
                $res = $a;
            }
        }
        return $res;
    }

    public static function mod($op1, $op2)
    {
        return $op1 % $op2;
    }

    public static function pi()
    {
        return pi();
    }

    public static function power($op1, $op2)
    {
        return pow($op1, $op2);
    }

    public static function round($val, $precision = 0)
    {
        return round($val, $precision);
    }

    public static function sum()
    {
        $args = func_get_args();
        $res = 0;
        foreach ($args as $a) {
            $res += $a;
        }
        return $res;
    }

    protected static function setRandomSeed($randomseed)
    {
        self::$randomseed = $randomseed;
    }

    protected static function getRandomSeed()
    {
        if (is_null(self::$randomseed)) {
            return microtime();
        } else {
            return self::$randomseed;
        }
    }

    protected static function randInt($min, $max)
    {
        if ($min >= $max) {
            return false; //error
        }
        $noofchars = ceil(log($max + 1 - $min, '16'));
        $md5string = md5(self::getRandomSeed());
        $stringoffset = 0;
        do {
            while (($stringoffset + $noofchars) > strlen($md5string)) {
                $md5string .= md5($md5string);
            }
            $randomno = hexdec(substr($md5string, $stringoffset, $noofchars));
            $stringoffset += $noofchars;
        } while (($min + $randomno) > $max);
        return $min + $randomno;
    }

    protected static function randFloat()
    {
        $randomvalues = unpack('v', md5(self::getRandomSeed(), true));
        return array_shift($randomvalues) / 65536;
    }
}
