<?php
namespace Consolidation\TestUtils\beta;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Test file used in the testCommandDiscovery() test.
 *
 * This commandfile is not found by the test.  The test search base is the
 * 'src' directory, but 'beta' is NOT one of the search directories available
 * for searching, so nothing in this folder will be examined.
 */
class BetaCommandFile
{
    public function unavailableCommand()
    {
        return 'This command is not available, because this commandfile is not in a location that is searched by the tests.';
    }

    /**
     * Demonstrate an alter hook with an option
     *
     * @hook alter example:table
     * @option chinese Add a row with Chinese numbers.
     * @usage example:table --chinese
     */
    public function alterFormattersChinese($result, CommandData $commandData)
    {
        if ($commandData->input()->getOption('chinese')) {
            $result[] = [ 'first' => '壹',  'second' => '貳',  'third' => '叁'  ];
        }

        return $result;
    }

    /**
     * Demonstrate an alter hook with an option
     *
     * @hook alter *
     * @option kanji Add a row with Kanji numbers.
     * @usage example:table --kanji
     */
    public function alterFormattersKanji($result, CommandData $commandData)
    {
        if ($commandData->input()->getOption('kanji')) {
            $result[] = [ 'first' => '一',  'second' => '二',  'third' => '三'  ];
        }

        return $result;
    }
}
