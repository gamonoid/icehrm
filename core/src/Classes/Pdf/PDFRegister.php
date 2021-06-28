<?php

namespace Classes\Pdf;

use Forms\Common\EmployeeFormPDFBuilder;

class PDFRegister
{
    protected static $register = [];

    public static function init()
    {
        self::put('empf', function ($data) {
            return new EmployeeFormPDFBuilder($data);
        });
    }

    public static function put($key, $callback)
    {
        self::$register[$key] = $callback;
    }

    public static function get($key)
    {
        return self::$register[$key];
    }
}
