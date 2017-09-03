<?php

namespace Gettext;

abstract class BaseTranslator implements TranslatorInterface
{
    /** @var TranslatorInterface */
    public static $current;

    /**
     * @see TranslatorInterface
     */
    public function register()
    {
        $previous = self::$current;

        self::$current = $this;

        include_once __DIR__.'/translator_functions.php';

        return $previous;
    }
}
