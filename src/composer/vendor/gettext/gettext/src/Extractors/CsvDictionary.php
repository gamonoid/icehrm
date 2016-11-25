<?php

namespace Gettext\Extractors;

use Gettext\Translations;
use Gettext\Utils\HeadersExtractorTrait;

/**
 * Class to get gettext strings from csv.
 */
class CsvDictionary extends Extractor implements ExtractorInterface
{
    use HeadersExtractorTrait;

    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $handle = fopen('php://memory', 'w');

        fputs($handle, $string);
        rewind($handle);

        while ($message = fgetcsv($handle)) {
            list($original, $translation) = $message + ['', ''];

            if ($original === '') {
                self::extractHeaders($translation, $translations);
                continue;
            }

            $translations->insert(null, $original)->setTranslation($translation);
        }

        fclose($handle);
    }
}
