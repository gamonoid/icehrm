<?php

namespace Gettext\Generators;

use Gettext\Translations;
use Gettext\Utils\DictionaryTrait;

class CsvDictionary extends Generator implements GeneratorInterface
{
    use DictionaryTrait;

    public static $options = [
        'includeHeaders' => false,
    ];

    /**
     * {@parentDoc}.
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        $handle = fopen('php://memory', 'w');

        foreach (self::toArray($translations, $options['includeHeaders']) as $original => $translation) {
            fputcsv($handle, [$original, $translation]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
