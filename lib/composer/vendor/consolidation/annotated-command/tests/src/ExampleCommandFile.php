<?php
namespace Consolidation\TestUtils;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
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
class ExampleCommandFile
{
    protected $state;
    protected $output;

    public function __construct($state = '')
    {
        $this->state = $state;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * This is the my:cat command
     *
     * This command will concatenate two parameters. If the --flip flag
     * is provided, then the result is the concatenation of two and one.
     *
     * @param string $one The first parameter.
     * @param string $two The other parameter.
     * @option boolean $flip Whether or not the second parameter should come first in the result.
     * @aliases c
     * @usage bet alpha --flip
     *   Concatenate "alpha" and "bet".
     * @arbitrary This annotation is here merely as a marker used in testing.
     */
    public function myCat($one, $two = '', $options = ['flip' => false])
    {
        if ($options['flip']) {
            return "{$two}{$one}";
        }
        return "{$one}{$two}";
    }

    /**
     * @command my:repeat
     */
    public function myRepeat($one, $two = '', $options = ['repeat' => 1])
    {
        return str_repeat("{$one}{$two}", $options['repeat']);
    }

    /**
     * @command my:join
     */
    public function myJoin(array $args, $options = ['flip' => false, 'repeat' => 1])
    {
        if ($options['flip']) {
            $args = array_reverse($args);
        }
        $result = implode('', $args);
        return str_repeat($result, $options['repeat']);
    }

    /**
     * This is a command with no options
     *
     * This command will concatenate two parameters.
     *
     * @param $one The first parameter.
     * @param $two The other parameter.
     * @aliases nope
     * @usage alpha bet
     *   Concatenate "alpha" and "bet".
     */
    public function commandWithNoOptions($one, $two = 'default')
    {
        return "{$one}{$two}";
    }

    /**
     * This command has no arguments--only options
     *
     * Return a result only if not silent.
     *
     * @option silent Supress output.
     */
    public function commandWithNoArguments($opts = ['silent|s' => false])
    {
        if (!$opts['silent']) {
            return "Hello, world";
        }
    }

    /**
     * Shortcut on annotation
     *
     * This command defines the option shortcut on the annotation instead of in the options array.
     *
     * @param $opts The options
     * @option silent|s Supress output.
     */
    public function shortcutOnAnnotation($opts = ['silent' => false])
    {
        if (!$opts['silent']) {
            return "Hello, world";
        }
    }

    /**
     * This is the test:arithmatic command
     *
     * This command will add one and two. If the --negate flag
     * is provided, then the result is negated.
     *
     * @command test:arithmatic
     * @param integer $one The first number to add.
     * @param integer $two The other number to add.
     * @option negate Whether or not the result should be negated.
     * @aliases arithmatic
     * @usage 2 2 --negate
     *   Add two plus two and then negate.
     * @custom
     */
    public function testArithmatic($one, $two, $options = ['negate' => false])
    {
        $result = $one + $two;
        if ($options['negate']) {
            $result = -$result;
        }

        // Integer return codes are exit codes (errors), so
        // return a the result as a string so that it will be printed.
        return "$result";
    }

    /**
     * This is the test:state command
     *
     * This command tests to see if the state of the Commandfile instance
     */
    public function testState()
    {
        return $this->state;
    }

    /**
     * This is the test:passthrough command
     *
     * This command takes a variable number of parameters as
     * an array and returns them as a csv.
     */
    public function testPassthrough(array $params)
    {
        return implode(',', $params);
    }

    /**
     * This command wraps its parameter in []; its alter hook
     * then wraps the result in <>.
     */
    public function testHook($parameter)
    {
        return "[$parameter]";
    }

    /**
     * Wrap the results of test:hook in <>.
     *
     * @hook alter test:hook
     */
    public function hookTestHook($result)
    {
        return "<$result>";
    }

    /**
     * This test is very similar to the preceding test, except
     * it uses an annotation hook instead of a named-function hook.
     *
     * @hookme
     * @before >
     * @after <
     */
    public function testAnnotationHook($parameter)
    {
        return "($parameter)";
    }

    /**
     * Wrap the results of test:hook in whatever the @before and @after
     * annotations contain.
     *
     * @hook alter @hookme
     */
    public function hookTestAnnotatedHook($result, CommandData $commandData)
    {
        $before = $commandData->annotationData()->get('before', '-');
        $after = $commandData->annotationData()->get('after', '-');
        return "$before$result$after";
    }

    /**
     * Alter the results of the hook with its command name.
     *
     * @hook alter @addmycommandname
     */
    public function hookAddCommandName($result, CommandData $commandData)
    {
        $annotationData = $commandData->annotationData();
        return "$result from " . $annotationData['command'];
    }

    /**
     * Here is a hook with an explicit command annotation that we will alter
     * with the preceeding hook
     *
     * @command alter-me
     * @addmycommandname
     */
    public function alterMe()
    {
        return "splendiferous";
    }

    /**
     * Here is another hook that has no command annotation that should be
     * altered with the default value for the command name
     *
     * @addmycommandname
     */
    public function alterMeToo()
    {
        return "fantabulous";
    }

    /**
     * @hook pre-command test:post-command
     */
    public function hookTestPreCommandHook(CommandData $commandData)
    {
        // Use 'writeln' to detect order that hooks are called
        $this->output->writeln("foo");
    }

    /**
     * @command test:post-command
     */
    public function testPostCommand($value)
    {
        $this->output->writeln($value);
    }

    /**
     * @hook post-command test:post-command
     */
    public function hookTestPostCommandHook($result, CommandData $commandData)
    {
        // Use 'writeln' to detect order that hooks are called
        $this->output->writeln("baz");
    }

    public function testHello($who)
    {
        return "Hello, $who.";
    }

    public function testException($what)
    {
        throw new \Exception($what);
    }

    /**
     * @hook init test:hello
     */
    public function initializeTestHello($input, AnnotationData $annotationData)
    {
        $who = $input->getArgument('who');
        if (!$who) {
            $input->setArgument('who', 'Huey');
        }
    }

    /**
     * @hook command-event test:hello
     */
    public function commandEventTestHello(ConsoleCommandEvent $event)
    {
        // Note that Symfony Console will not allow us to alter the
        // input from this hook, so we'll just print something to
        // show that this hook was executed.
        $input = $event->getInput();
        $who = $input->getArgument('who');
        $this->output->writeln("Here comes $who!");
    }

    /**
     * @hook interact test:hello
     */
    public function interactTestHello($input, $output)
    {
        $who = $input->getArgument('who');
        if (!$who) {
            $input->setArgument('who', 'Goofey');
        }
    }

    /**
     * @hook validate test:hello
     */
    public function validateTestHello($commandData)
    {
        $args = $commandData->arguments();
        if ($args['who'] == 'Donald Duck') {
            return new CommandError("I won't say hello to Donald Duck.");
        }
        if ($args['who'] == 'Drumph') {
            throw new \Exception('Irrational value error.');
        }
    }

    /**
     * Test default values in arguments
     *
     * @param string|null $one
     * @param string|null $two
     * @return string
     */
    public function defaults($one = null, $two = null)
    {
        if ($one && $two) {
            return "$one and $two";
        }
        if ($one) {
            return "only $one";
        }
        return "nothing provided";
    }
}
