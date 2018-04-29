<?php

namespace Gettext\Generators;

use Gettext\Translations;

class Po extends Generator implements GeneratorInterface
{
    /**
     * {@parentDoc}.
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $lines = ['msgid ""', 'msgstr ""'];

        foreach ($translations->getHeaders() as $name => $value) {
            $lines[] = sprintf('"%s: %s\\n"', $name, $value);
        }

        $lines[] = '';

        //Translations
        foreach ($translations as $translation) {
            if ($translation->hasComments()) {
                foreach ($translation->getComments() as $comment) {
                    $lines[] = '# '.$comment;
                }
            }

            if ($translation->hasExtractedComments()) {
                foreach ($translation->getExtractedComments() as $comment) {
                    $lines[] = '#. '.$comment;
                }
            }

            if ($translation->hasReferences()) {
                foreach ($translation->getReferences() as $reference) {
                    $lines[] = '#: '.$reference[0].(!is_null($reference[1]) ? ':'.$reference[1] : null);
                }
            }

            if ($translation->hasFlags()) {
                $lines[] = '#, '.implode(',', $translation->getFlags());
            }

            if ($translation->hasContext()) {
                $lines[] = 'msgctxt '.self::convertString($translation->getContext());
            }

            self::addLines($lines, 'msgid', $translation->getOriginal());

            if ($translation->hasPlural()) {
                self::addLines($lines, 'msgid_plural', $translation->getPlural());
                self::addLines($lines, 'msgstr[0]', $translation->getTranslation());

                foreach ($translation->getPluralTranslations() as $k => $v) {
                    self::addLines($lines, 'msgstr['.($k + 1).']', $v);
                }
            } else {
                self::addLines($lines, 'msgstr', $translation->getTranslation());
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Escapes and adds double quotes to a string.
     *
     * @param string $string
     *
     * @return string
     */
    private static function multilineQuote($string)
    {
        $lines = explode("\n", $string);
        $last = count($lines) - 1;

        foreach ($lines as $k => $line) {
            if ($k === $last) {
                $lines[$k] = self::convertString($line);
            } else {
                $lines[$k] = self::convertString($line."\n");
            }
        }

        return $lines;
    }

    /**
     * Add one or more lines depending whether the string is multiline or not.
     *
     * @param array  &$lines
     * @param string $name
     * @param string $value
     */
    private static function addLines(array &$lines, $name, $value)
    {
        $newLines = self::multilineQuote($value);

        if (count($newLines) === 1) {
            $lines[] = $name.' '.$newLines[0];
        } else {
            $lines[] = $name.' ""';

            foreach ($newLines as $line) {
                $lines[] = $line;
            }
        }
    }

    /**
     * Convert a string to its PO representation.
     *
     * @param string $value
     *
     * @return string
     */
    public static function convertString($value)
    {
        return '"'.strtr(
            $value,
            [
                "\x00" => '',
                '\\' => '\\\\',
                "\t" => '\t',
                "\n" => '\n',
                '"' => '\\"',
            ]
        ).'"';
    }
}
