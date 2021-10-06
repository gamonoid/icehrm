<?php

namespace Classes\Pdf;

use Invoices\InvoicePDFBuilder;

class PDFRegister
{
    protected static $register = [];

    public static function init()
    {
        self::put('invoice', function ($data) {
            return new InvoicePDFBuilder($data);
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
