<?php
namespace Consolidation\TestUtils;

use Consolidation\AnnotatedCommand\AnnotatedCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test file used in the Annotation Factory tests.  It is also
 * discovered in the testCommandDiscovery() test.
 *
 * The testCommandDiscovery test search base is the 'src' directory;
 * any command files located immediately inside the search base are
 * eligible for discovery, and will be included in the search results.
 */
class ExampleAnnotatedCommand extends AnnotatedCommand
{
    /**
     * Do the main function of the my:cat command.
     */
    public function myCat($one, $two = '', $multiple = [], $flip = false)
    {
        if ($flip) {
            return "{$two}{$one}" . implode('', array_reverse($multiple));
        }
        return "{$one}{$two}" . implode('', $multiple);
    }

    /**
     * This is the my:cat command implemented as an AnnotatedCommand subclass.
     *
     * This command will concatenate two parameters. If the --flip flag
     * is provided, then the result is the concatenation of two and one.
     *
     * @command my:cat
     * @arg string $one The first parameter.
     * @arg string $two The other parameter.
     * @default $two ''
     * @option array $multiple An array of values
     * @default $multiple []
     * @option boolean $flip Whether or not the second parameter should come first in the result.
     * @aliases c
     * @usage bet alpha --flip
     *   Concatenate "alpha" and "bet".
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $one = $input->getArgument('one');
        $two = $input->getArgument('two');
        $multiple = $input->getOption('multiple');
        $flip = $input->getOption('flip');

        $result = $this->myCat($one, $two, $multiple, $flip);

        // We could also just use $output->writeln($result) here,
        // but calling processResults enables the use of output
        // formatters. Note also that if you use processResults, you
        // should correctly inject the command processor into your
        // annotated command via AnnotatedCommand::setCommandProcessor().
        return $this->processResults($input, $output, $result);
    }
}
