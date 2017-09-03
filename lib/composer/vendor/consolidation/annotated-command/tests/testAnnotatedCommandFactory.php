<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Options\AlterOptionsCommandEvent;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\TestUtils\ExampleCommandInfoAlterer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class AnnotatedCommandFactoryTests extends \PHPUnit_Framework_TestCase
{
    protected $commandFileInstance;
    protected $commandFactory;

    /**
     * Test CommandInfo command annotation parsing.
     */
    function testAnnotatedCommandCreation()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testArithmatic');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:arithmatic', $command->getName());
        $this->assertEquals('This is the test:arithmatic command', $command->getDescription());
        $this->assertEquals("This command will add one and two. If the --negate flag\nis provided, then the result is negated.", $command->getHelp());
        $this->assertEquals('arithmatic', implode(',', $command->getAliases()));
        $this->assertEquals('test:arithmatic [--negate] [--] <one> <two>', $command->getSynopsis());
        $this->assertEquals('test:arithmatic 2 2 --negate', implode(',', $command->getUsages()));

        $input = new StringInput('arithmatic 2 3 --negate');
        $this->assertRunCommandViaApplicationEquals($command, $input, '-5');
    }

    /**
     * Test CommandInfo command annotation altering.
     */
    function testAnnotatedCommandInfoAlteration()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'myCat');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $annotationData = $command->getAnnotationData();
        $this->assertTrue($annotationData->has('arbitrary'));
        $this->assertFalse($annotationData->has('dynamic'));

        $this->commandFactory->addCommandInfoAlterer(new ExampleCommandInfoAlterer());

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $annotationData = $command->getAnnotationData();
        $this->assertTrue($annotationData->has('arbitrary'));
        $this->assertTrue($annotationData->has('dynamic'));
    }

    function testMyCatCommand()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'myCat');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('my:cat', $command->getName());
        $this->assertEquals('This is the my:cat command', $command->getDescription());
        $this->assertEquals("This command will concatenate two parameters. If the --flip flag\nis provided, then the result is the concatenation of two and one.", $command->getHelp());
        $this->assertEquals('c', implode(',', $command->getAliases()));
        $this->assertEquals('my:cat [--flip] [--] <one> [<two>]', $command->getSynopsis());
        $this->assertEquals('my:cat bet alpha --flip', implode(',', $command->getUsages()));

        $input = new StringInput('my:cat bet alpha --flip');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'alphabet');
    }

    function testDefaultsCommand()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'defaults');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('defaults', $command->getName());
        $this->assertEquals('Test default values in arguments', $command->getDescription());

        $input = new StringInput('defaults');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'nothing provided');

        $input = new StringInput('defaults ichi');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'only ichi');

        $input = new StringInput('defaults I II');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'I and II');
    }

    function testCommandWithNoOptions()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'commandWithNoOptions');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('command:with-no-options', $command->getName());
        $this->assertEquals('This is a command with no options', $command->getDescription());
        $this->assertEquals("This command will concatenate two parameters.", $command->getHelp());
        $this->assertEquals('nope', implode(',', $command->getAliases()));
        $this->assertEquals('command:with-no-options <one> [<two>]', $command->getSynopsis());
        $this->assertEquals('command:with-no-options alpha bet', implode(',', $command->getUsages()));

        $input = new StringInput('command:with-no-options something');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'somethingdefault');
    }

    function testCommandWithNoArguments()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'commandWithNoArguments');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('command:with-no-arguments', $command->getName());
        $this->assertEquals('This command has no arguments--only options', $command->getDescription());
        $this->assertEquals("Return a result only if not silent.", $command->getHelp());
        $this->assertEquals('command:with-no-arguments [-s|--silent]', $command->getSynopsis());

        $input = new StringInput('command:with-no-arguments');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'Hello, world');
        $input = new StringInput('command:with-no-arguments -s');
        $this->assertRunCommandViaApplicationEquals($command, $input, '');
        $input = new StringInput('command:with-no-arguments --silent');
        $this->assertRunCommandViaApplicationEquals($command, $input, '');
    }

    function testCommandWithShortcutOnAnnotation()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'shortcutOnAnnotation');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('shortcut:on-annotation', $command->getName());
        $this->assertEquals('Shortcut on annotation', $command->getDescription());
        $this->assertEquals("This command defines the option shortcut on the annotation instead of in the options array.", $command->getHelp());
        $this->assertEquals('shortcut:on-annotation [-s|--silent]', $command->getSynopsis());

        $input = new StringInput('shortcut:on-annotation');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'Hello, world');
        $input = new StringInput('shortcut:on-annotation -s');
        $this->assertRunCommandViaApplicationEquals($command, $input, '');
        $input = new StringInput('shortcut:on-annotation --silent');
        $this->assertRunCommandViaApplicationEquals($command, $input, '');
    }

    function testState()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile('secret secret');
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testState');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:state', $command->getName());

        $input = new StringInput('test:state');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'secret secret');
    }

    function testPassthroughArray()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testPassthrough');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:passthrough', $command->getName());

        $input = new StringInput('test:passthrough a b c -- x y z');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'a,b,c,x,y,z');
    }

    function testPassThroughNonArray()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'myJoin');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $input = new StringInput('my:join bet --flip -- x y z');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'zyxbet');
        // Can't look at 'hasOption' until after the command initializes the
        // option, because Symfony.
        $this->assertTrue($input->hasOption('flip'));
    }

    function testPassThroughWithInputManipulation()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile;
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'myJoin');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $input = new StringInput('my:join bet --repeat=2 -- x y z');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'betxyzbetxyz');
        // Symfony does not allow us to manipulate the options via setOption until
        // the definition from the command object has been set up.
        $input->setOption('repeat', 3);
        $this->assertEquals(3, $input->getOption('repeat'));
        $input->setArgument(0, 'q');
        // Manipulating $input does not work -- the changes are not effective.
        // The end result here should be 'qx y yqx y yqx y y'
        $this->assertRunCommandViaApplicationEquals($command, $input, 'betxyzbetxyz');
    }

    function testHookedCommand()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookTestHook');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('alter test:hook', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:hook', [HookManager::ALTER_RESULT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookTestHook', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testHook');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:hook', $command->getName());

        $input = new StringInput('test:hook bar');
        $this->assertRunCommandViaApplicationEquals($command, $input, '<[bar]>');
    }

    function testPostCommandCalledAfterCommand()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookTestPostCommandHook');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('post-command test:post-command', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:post-command', [HookManager::POST_COMMAND_HOOK]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookTestPostCommandHook', $hookCallback[0][1]);

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookTestPreCommandHook');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('pre-command test:post-command', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:post-command', [HookManager::PRE_COMMAND_HOOK]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookTestPreCommandHook', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testPostCommand');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:post-command', $command->getName());

        $input = new StringInput('test:post-command bar');
        $this->assertRunCommandViaApplicationEquals($command, $input, "foo\nbar\nbaz", 0, $this->commandFileInstance);
    }

    function testHookAllCommands()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleHookAllCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'alterAllCommands');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('alter', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('Consolidation\TestUtils\ExampleHookAllCommandFile', [HookManager::ALTER_RESULT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('alterAllCommands', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'doCat');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('do:cat', $command->getName());

        $input = new StringInput('do:cat bar');
        $this->assertRunCommandViaApplicationEquals($command, $input, '*** bar ***');
    }

    function testAnnotatedHookedCommand()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookTestAnnotatedHook');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('alter @hookme', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);
        $hookCallback = $this->commandFactory->hookManager()->get('@hookme', [HookManager::ALTER_RESULT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookTestAnnotatedHook', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testAnnotationHook');
        $annotationData = $commandInfo->getRawAnnotations();
        $this->assertEquals('hookme,before,after', implode(',', $annotationData->keys()));
        $this->assertEquals('@hookme,@before,@after', implode(',', array_map(function ($item) { return "@$item"; }, $annotationData->keys())));

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:annotation-hook', $command->getName());

        $input = new StringInput('test:annotation-hook baz');
        $this->assertRunCommandViaApplicationEquals($command, $input, '>(baz)<');
    }

    function testHookHasCommandAnnotation()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookAddCommandName');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('alter @addmycommandname', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);
        $hookCallback = $this->commandFactory->hookManager()->get('@addmycommandname', [HookManager::ALTER_RESULT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookAddCommandName', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'alterMe');
        $annotationData = $commandInfo->getRawAnnotations();
        $this->assertEquals('command,addmycommandname', implode(',', $annotationData->keys()));

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('alter-me', $command->getName());

        $input = new StringInput('alter-me');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'splendiferous from alter-me');

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'alterMeToo');
        $annotationData = $commandInfo->getRawAnnotations();
        $this->assertEquals('addmycommandname', implode(',', $annotationData->keys()));
        $annotationData = $commandInfo->getAnnotations();
        $this->assertEquals('addmycommandname,command', implode(',', $annotationData->keys()));

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('alter:me-too', $command->getName());

        $input = new StringInput('alter:me-too');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'fantabulous from alter:me-too');
    }


    function testHookedCommandWithHookAddedLater()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();
        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testHook');

        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:hook', $command->getName());

        // Run the command once without the hook
        $input = new StringInput('test:hook foo');
        $this->assertRunCommandViaApplicationEquals($command, $input, '[foo]');

        // Register the hook and run the command again
        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'hookTestHook');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals('alter test:hook', $hookInfo->getAnnotation('hook'));

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);
        $hookCallback = $this->commandFactory->hookManager()->get('test:hook', [HookManager::ALTER_RESULT]);;
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('hookTestHook', $hookCallback[0][1]);

        $input = new StringInput('test:hook bar');
        $this->assertRunCommandViaApplicationEquals($command, $input, '<[bar]>');
    }

    function testInitializeHook()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'initializeTestHello');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals($hookInfo->getAnnotation('hook'), 'init test:hello');

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:hello', [HookManager::INITIALIZE]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('initializeTestHello', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testHello');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:hello', $command->getName());
        $commandGetNames = $this->callProtected($command, 'getNames');
        $this->assertEquals('test:hello,Consolidation\TestUtils\ExampleCommandFile', implode(',', $commandGetNames));

        $hookCallback = $command->commandProcessor()->hookManager()->get('test:hello', 'init');
        $this->assertTrue($hookCallback != null);
        $this->assertEquals('initializeTestHello', $hookCallback[0][1]);

        $input = new StringInput('test:hello');
        $this->assertRunCommandViaApplicationEquals($command, $input, "Hello, Huey.");
    }

    function testCommandEventHook()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'commandEventTestHello');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals($hookInfo->getAnnotation('hook'), 'command-event test:hello');

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:hello', [HookManager::COMMAND_EVENT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('commandEventTestHello', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testHello');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:hello', $command->getName());
        $commandGetNames = $this->callProtected($command, 'getNames');
        $this->assertEquals('test:hello,Consolidation\TestUtils\ExampleCommandFile', implode(',', $commandGetNames));

        $hookCallback = $command->commandProcessor()->hookManager()->get('test:hello', 'command-event');
        $this->assertTrue($hookCallback != null);
        $this->assertEquals('commandEventTestHello', $hookCallback[0][1]);

        $input = new StringInput('test:hello Pluto');
        $this->assertRunCommandViaApplicationEquals($command, $input, "Here comes Pluto!\nHello, Pluto.");
    }


    function testInteractAndValidate()
    {
        $this->commandFileInstance = new \Consolidation\TestUtils\ExampleCommandFile();
        $this->commandFactory = new AnnotatedCommandFactory();

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'interactTestHello');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals($hookInfo->getAnnotation('hook'), 'interact test:hello');

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'validateTestHello');

        $this->assertTrue($hookInfo->hasAnnotation('hook'));
        $this->assertEquals($hookInfo->getAnnotation('hook'), 'validate test:hello');

        $this->commandFactory->registerCommandHook($hookInfo, $this->commandFileInstance);

        $hookCallback = $this->commandFactory->hookManager()->get('test:hello', [HookManager::ARGUMENT_VALIDATOR]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('validateTestHello', $hookCallback[0][1]);

        $hookCallback = $this->commandFactory->hookManager()->get('test:hello', [HookManager::INTERACT]);
        $this->assertTrue($hookCallback != null);
        $this->assertEquals(1, count($hookCallback));
        $this->assertEquals(2, count($hookCallback[0]));
        $this->assertTrue(is_callable($hookCallback[0]));
        $this->assertEquals('interactTestHello', $hookCallback[0][1]);

        $commandInfo = $this->commandFactory->createCommandInfo($this->commandFileInstance, 'testHello');
        $command = $this->commandFactory->createCommand($commandInfo, $this->commandFileInstance);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('test:hello', $command->getName());
        $commandGetNames = $this->callProtected($command, 'getNames');
        $this->assertEquals('test:hello,Consolidation\TestUtils\ExampleCommandFile', implode(',', $commandGetNames));

        $testInteractInput = new StringInput('test:hello');
        $definition = new \Symfony\Component\Console\Input\InputDefinition(
            [
                new \Symfony\Component\Console\Input\InputArgument('application', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
                new \Symfony\Component\Console\Input\InputArgument('who', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
            ]
        );
        $testInteractInput->bind($definition);
        $testInteractOutput = new BufferedOutput();
        $command->commandProcessor()->interact(
            $testInteractInput,
            $testInteractOutput,
            $commandGetNames,
            $command->getAnnotationData()
        );
        $this->assertEquals('Goofey', $testInteractInput->getArgument('who'));

        $hookCallback = $command->commandProcessor()->hookManager()->get('test:hello', 'interact');
        $this->assertTrue($hookCallback != null);
        $this->assertEquals('interactTestHello', $hookCallback[0][1]);

        $input = new StringInput('test:hello "Mickey Mouse"');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'Hello, Mickey Mouse.');

        $input = new StringInput('test:hello');
        $this->assertRunCommandViaApplicationEquals($command, $input, 'Hello, Goofey.');

        $input = new StringInput('test:hello "Donald Duck"');
        $this->assertRunCommandViaApplicationEquals($command, $input, "I won't say hello to Donald Duck.", 1);

        $input = new StringInput('test:hello "Drumph"');
        $this->assertRunCommandViaApplicationEquals($command, $input, "Irrational value error.", 1);

        // Try the last test again with a display error function installed.
        $this->commandFactory->commandProcessor()->setDisplayErrorFunction(
            function ($output, $message) {
                $output->writeln("*** $message ****");
            }
        );

        $input = new StringInput('test:hello "Drumph"');
        $this->assertRunCommandViaApplicationEquals($command, $input, "*** Irrational value error. ****", 1);
    }

    function callProtected($object, $method, $args = [])
    {
        $r = new \ReflectionMethod($object, $method);
        $r->setAccessible(true);
        return $r->invokeArgs($object, $args);
    }

    function assertRunCommandViaApplicationEquals($command, $input, $expectedOutput, $expectedStatusCode = 0)
    {
        $output = new BufferedOutput();
        if ($this->commandFileInstance && method_exists($this->commandFileInstance, 'setOutput')) {
            $this->commandFileInstance->setOutput($output);
        }

        $application = new Application('TestApplication', '0.0.0');
        $alterOptionsEventManager = new AlterOptionsCommandEvent($application);

        $eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $eventDispatcher->addSubscriber($this->commandFactory->commandProcessor()->hookManager());
        $eventDispatcher->addSubscriber($alterOptionsEventManager);
        $application->setDispatcher($eventDispatcher);

        $application->setAutoExit(false);
        $application->add($command);

        $statusCode = $application->run($input, $output);
        $commandOutput = trim($output->fetch());

        $this->assertEquals($expectedOutput, $commandOutput);
        $this->assertEquals($expectedStatusCode, $statusCode);
    }
}
