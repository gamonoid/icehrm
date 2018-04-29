<?php
namespace Consolidation\TestUtils;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * Import config from a config directory.
     *
     * @command config:import
     * @param $label A config directory label (i.e. a key in \$config_directories array in settings.php).
     * @interact-config-label
     * @option preview Format for displaying proposed changes. Recognized values: list, diff.
     * @option source An arbitrary directory that holds the configuration files. An alternative to label argument
     * @option partial Allows for partial config imports from the source directory. Only updates and new configs will be processed with this flag (missing configs will not be deleted).
     * @aliases cim,config-import
     */
    public function import($label = null, $options = ['preview' => 'list', 'source' => InputOption::VALUE_REQUIRED, 'partial' => false])
    {
    }

    /**
     * Calculate the fibonacci sequence between two numbers.
     *
     * Graphic output will look like
     *     +----+---+-------------+
     *     |    |   |             |
     *     |    |-+-|             |
     *     |----+-+-+             |
     *     |        |             |
     *     |        |             |
     *     |        |             |
     *     +--------+-------------+
     *
     * @param int $start Number to start from
     * @param int $steps Number of steps to perform
     * @param array $opts
     * @option $graphic Display the sequence graphically using cube
     *   representation
     */
    public function fibonacci($start, $steps, $opts = ['graphic' => false])
    {
    }

    /**
     * Code sniffer.
     *
     * Run the PHP Codesniffer on a file or directory.
     *
     * @param string $file
     *    A file or directory to analyze.
     * @option $autofix Whether to run the automatic fixer or not.
     * @option $strict Show warnings as well as errors.
     *    Default is to show only errors.
     */
    public function sniff(
        $file = 'src',
        array $options = [
            'autofix' => false,
            'strict' => false,
        ]
    ) {
        return var_export($options, true);
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
    public function myCat($one, $two = '', array $options = ['flip' => false])
    {
        if ($options['flip']) {
            return "{$two}{$one}";
        }
        return "{$one}{$two}";
    }

    /**
     * @command my:repeat
     */
    public function myRepeat($one, $two = '', array $options = ['repeat' => 1])
    {
        return str_repeat("{$one}{$two}", $options['repeat']);
    }

    /**
     * This is the my:join command
     *
     * This command will join its parameters together. It can also reverse and repeat its arguments.
     *
     * @command my:join
     * @usage a b
     *   Join a and b to produce "a,b"
     * @usage
     *   Example with no parameters or options
     */
    public function myJoin(array $args, array $options = ['flip' => false, 'repeat' => 1])
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
     * This command work with app's input and output
     *
     * @command command:with-io-parameters
     */
    public function commandWithIOParameters(InputInterface $input, OutputInterface $output)
    {
        return $input->getFirstArgument();
    }

    /**
     * This command has no arguments--only options
     *
     * Return a result only if not silent.
     *
     * @option silent Supress output.
     */
    public function commandWithNoArguments(array $opts = ['silent|s' => false])
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
    public function shortcutOnAnnotation(array $opts = ['silent' => false])
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
     * @dup one
     * @dup two
     */
    public function testArithmatic($one, $two = 2, array $options = ['negate' => false, 'unused' => 'bob'])
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
     * @command test:replace-command
     */
    public function testReplaceCommand($value)
    {
        $this->output->writeln($value);
    }

    /**
     * @hook replace-command test:replace-command
     */
    public function hookTestReplaceCommandHook($value)
    {
        $this->output->writeln("bar");
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

    /**
     * @return string
     */
    public function defaultOptionOne(array $options = ['foo' => '1'])
    {
        return "Foo is " . $options['foo'];
    }

    /**
     * @return string
     */
    public function defaultOptionTwo(array $options = ['foo' => '2'])
    {
        return "Foo is " . $options['foo'];
    }

    /**
     * @return string
     */
    public function defaultOptionNone(array $options = ['foo' => InputOption::VALUE_REQUIRED])
    {
        return "Foo is " . $options['foo'];
    }

    /**
     * @return string
     */
    public function defaultOptionalValue(array $options = ['foo' => InputOption::VALUE_OPTIONAL])
    {
        return "Foo is " . var_export($options['foo'], true);
    }

    /**
     * @return string
     */
    public function defaultOptionDefaultsToTrue(array $options = ['foo' => true])
    {
        return "Foo is " . var_export($options['foo'], true);
    }

    /**
     * This is the test:required-array-option command
     *
     * This command will print all the valused of passed option
     *
     * @param array $opts
     * @return string
     */
    public function testRequiredArrayOption(array $opts = ['arr|a' => []])
    {
        return implode(' ', $opts['arr']);
    }

    /**
     * This is the test:array-option command
     *
     * This command will print all the valused of passed option
     *
     * @param array $opts
     * @return string
     */
    public function testArrayOption(array $opts = ['arr|a' => ['1', '2', '3']])
    {
        return implode(' ', $opts['arr']);
    }

    /**
     * @command global-options-only
     */
    public function globalOptionsOnly($arg, array $options = [])
    {
        return "Arg is $arg, options[help] is " . var_export($options['help'], true) . "\n";
    }
}
