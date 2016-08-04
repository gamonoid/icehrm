<?php

namespace Gettext\Generators;

use Gettext\Translations;
use Gettext\Utils\HeadersGeneratorTrait;

/**
 * Class to export translations to csv.
 */
class Csv extends Generator implements GeneratorInterface
{
    use HeadersGeneratorTrait;

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

        if ($options['includeHeaders']) {
            fputcsv($handle, ['', '', self::generateHeaders($translations)]);
        }

        foreach ($translations as $translation) {
            $line = [$translation->getContext(), $translation->getOriginal(), $translation->getTranslation()];

            if ($translation->hasPluralTranslations(true)) {
                $line = array_merge($line, $translation->getPluralTranslations());
            }

            fputcsv($handle, $line);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
