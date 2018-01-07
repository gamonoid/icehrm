<?php
namespace Consolidation\AnnotatedCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Application;

class AnnotatedCommandTests extends \PHPUnit_Framework_TestCase
{
    function testMyCatCommand()
    {
        $command = new \Consolidation\TestUtils\ExampleAnnotatedCommand();

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('my:cat', $command->getName());
        $this->assertEquals('This is the my:cat command implemented as an AnnotatedCommand subclass.', $command->getDescription());
        $this->assertEquals("This command will concatenate two parameters. If the --flip flag\nis provided, then the result is the concatenation of two and one.", $command->getHelp());
        $this->assertEquals('c', implode(',', $command->getAliases()));
        // Symfony Console composes the synopsis; perhaps we should not test it. Remove if this gives false failures.
        $this->assertEquals('my:cat [--multiple MULTIPLE] [--flip] [--] <one> [<two>]', $command->getSynopsis());
        $this->assertEquals('my:cat bet alpha --flip', implode(',', $command->getUsages()));

        $input = new StringInput('my:cat b alpha --multiple=t --multiple=e --flip');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'alphabet');
    }

    // TODO: Make a base test class to hold this.
    function assertRunCommandViaApplicationEquals($command, $input, $expectedOutput, $expectedStatusCode = 0)
    {
        $output = new BufferedOutput();

        $application = new Application('TestApplication', '0.0.0');
        $application->setAutoExit(false);
        $application->add($command);

        $statusCode = $application->run($input, $output);
        $commandOutput = trim($output->fetch());

        $this->assertEquals($expectedOutput, $commandOutput);
        $this->assertEquals($expectedStatusCode, $statusCode);
    }
}
