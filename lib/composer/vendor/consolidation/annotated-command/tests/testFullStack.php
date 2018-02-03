<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandProcessor;
use Consolidation\AnnotatedCommand\Hooks\AlterResultInterface;
use Consolidation\AnnotatedCommand\Hooks\ExtractOutputInterface;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Hooks\ProcessResultInterface;
use Consolidation\AnnotatedCommand\Hooks\StatusDeterminerInterface;
use Consolidation\AnnotatedCommand\Hooks\ValidatorInterface;
use Consolidation\AnnotatedCommand\Options\AlterOptionsCommandEvent;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\TestUtils\TestTerminal;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\TestUtils\ApplicationWithTerminalWidth;
use Consolidation\AnnotatedCommand\Options\PrepareTerminalWidthOption;
use Consolidation\AnnotatedCommand\Events\CustomEventAwareInterface;
use Consolidation\AnnotatedCommand\Events\CustomEventAwareTrait;

/**
 * Do a test of all of the classes in this project, top-to-bottom.
 */
class FullStackTests extends \PHPUnit_Framework_TestCase
{
    protected $application;
    protected $commandFactory;

    function setup() {
        $this->application = new ApplicationWithTerminalWidth('TestApplication', '0.0.0');
        $this->commandFactory = new AnnotatedCommandFactory();
        $alterOptionsEventManager = new AlterOptionsCommandEvent($this->application);
        $eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $eventDispatcher->addSubscriber($this->commandFactory->commandProcessor()->hookManager());
        $this->commandFactory->commandProcessor()->hookManager()->addCommandEvent($alterOptionsEventManager);
        $this->application->setDispatcher($eventDispatcher);
        $this->application->setAutoExit(false);
    }

    function testValidFormats()
    {
        $formatter = new FormatterManager();
        $formatter->addDefaultFormatters();
        $formatter->addDefaultSimplifiers();
        $commandInfo = CommandInfo::create('\Consolidation\TestUtils\alpha\AlphaCommandFile', 'exampleTable');
        $this->assertEquals('example:table', $commandInfo->getName());
        $this->assertEquals('\Consolidation\OutputFormatters\StructuredData\RowsOfFields', $commandInfo->getReturnType());
    }

    function testAutomaticOptions()
    {
        $commandFileInstance = new \Consolidation\TestUtils\alpha\AlphaCommandFile;
        $formatter = new FormatterManager();
        $formatter->addDefaultFormatters();
        $formatter->addDefaultSimplifiers();

        $this->commandFactory->commandProcessor()->setFormatterManager($formatter);
        $this->assertAutomaticOptionsForCommand($commandFileInstance, 'exampleTable', 'example:table');
        $this->assertAutomaticOptionsForCommand($commandFileInstance, 'exampleTableTwo', 'example:table2');
    }

    function assertAutomaticOptionsForCommand($commandFileInstance, $functionName, $commandName)
    {
        $commandInfo = $this->commandFactory->createCommandInfo($commandFileInstance, $functionName);

        $command = $this->commandFactory->createCommand($commandInfo, $commandFileInstance);
        $this->application->add($command);

        $containsList =
        [
            '--format[=FORMAT]  Format the result data. Available formats: csv,json,list,php,print-r,sections,string,table,tsv,var_export,xml,yaml [default: "table"]',
            '--fields[=FIELDS]  Available fields: I (first), II (second), III (third) [default: ""]',
        ];
        $this->assertRunCommandViaApplicationContains('help ' . $commandName, $containsList);
    }

    function testCommandsAndHooks()
    {
        // First, search for commandfiles in the 'alpha'
        // directory. Note that this same functionality
        // is tested more thoroughly in isolation in
        // testCommandFileDiscovery.php
        $discovery = new CommandFileDiscovery();
        $discovery
          ->setSearchPattern('*CommandFile.php')
          ->setIncludeFilesAtBase(false)
          ->setSearchLocations(['alpha']);

        chdir(__DIR__);
        $commandFiles = $discovery->discover('.', '\Consolidation\TestUtils');

        $formatter = new FormatterManager();
        $formatter->addDefaultFormatters();
        $formatter->addDefaultSimplifiers();
        $hookManager = new HookManager();
        $terminalWidthOption = new PrepareTerminalWidthOption();
        $terminalWidthOption->enableWrap(true);
        $terminalWidthOption->setApplication($this->application);
        $testTerminal = new TestTerminal(0);
        $terminalWidthOption->setTerminal($testTerminal);
        $commandProcessor = new CommandProcessor($hookManager);
        $commandProcessor->setFormatterManager($formatter);
        $commandProcessor->addPrepareFormatter($terminalWidthOption);

        // Create a new factory, and load all of the files
        // discovered above.
        $factory = new AnnotatedCommandFactory();
        $factory->setCommandProcessor($commandProcessor);
        // Add a listener to configure our command handler object
        $factory->addListernerCallback(function($command) use($hookManager) {
            if ($command instanceof CustomEventAwareInterface) {
                $command->setHookManager($hookManager);
            }
        } );
        $factory->setIncludeAllPublicMethods(false);
        $this->addDiscoveredCommands($factory, $commandFiles);

        $this->assertRunCommandViaApplicationContains('list', ['example:table'], ['additional:option', 'without:annotations']);

        $this->assertTrue($this->application->has('example:table'));
        $this->assertFalse($this->application->has('without:annotations'));

        // Run the use:event command that defines a custom event, my-event.
        $this->assertRunCommandViaApplicationEquals('use:event', 'one,two');
        // Watch as we dynamically add a custom event to the hook manager to change the command results:
        $hookManager->add(function () { return 'three'; }, HookManager::ON_EVENT, 'my-event');
        $this->assertRunCommandViaApplicationEquals('use:event', 'one,three,two');

        // Fetch a reference to the 'example:table' command and test its valid format types
        $exampleTableCommand = $this->application->find('example:table');
        $returnType = $exampleTableCommand->getReturnType();
        $this->assertEquals('\Consolidation\OutputFormatters\StructuredData\RowsOfFields', $returnType);
        $validFormats = $formatter->validFormats($returnType);
        $this->assertEquals('csv,json,list,php,print-r,sections,string,table,tsv,var_export,xml,yaml', implode(',', $validFormats));

        // Control: run commands without hooks.
        $this->assertRunCommandViaApplicationEquals('always:fail', 'This command always fails.', 13);
        $this->assertRunCommandViaApplicationEquals('simulated:status', '42');
        $this->assertRunCommandViaApplicationEquals('example:output', 'Hello, World.');
        $this->assertRunCommandViaApplicationEquals('example:cat bet alpha --flip', 'alphabet');
        $this->assertRunCommandViaApplicationEquals('example:echo a b c', "a\tb\tc");
        $this->assertRunCommandViaApplicationEquals('example:message', 'Shipwrecked; send bananas.');
        $this->assertRunCommandViaApplicationEquals('command:with-one-optional-argument', 'Hello, world');
        $this->assertRunCommandViaApplicationEquals('command:with-one-optional-argument Joe', 'Hello, Joe');

        // Add some hooks.
        $factory->hookManager()->addValidator(new ExampleValidator());
        $factory->hookManager()->addResultProcessor(new ExampleResultProcessor());
        $factory->hookManager()->addAlterResult(new ExampleResultAlterer());
        $factory->hookManager()->addStatusDeterminer(new ExampleStatusDeterminer());
        $factory->hookManager()->addOutputExtractor(new ExampleOutputExtractor());

        // Run the same commands as before, and confirm that results
        // are different now that the hooks are in place.
        $this->assertRunCommandViaApplicationEquals('simulated:status', '', 42);
        $this->assertRunCommandViaApplicationEquals('example:output', 'Hello, World!');
        $this->assertRunCommandViaApplicationEquals('example:cat bet alpha --flip', 'alphareplaced');
        $this->assertRunCommandViaApplicationEquals('example:echo a b c', 'a,b,c');
        $this->assertRunCommandViaApplicationEquals('example:message', 'Shipwrecked; send bananas.');

        $expected = <<<EOT
 ------ ------ -------
  I      II     III
 ------ ------ -------
  One    Two    Three
  Eins   Zwei   Drei
  Ichi   Ni     San
  Uno    Dos    Tres
 ------ ------ -------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:table', $expected);

        $expected = <<<EOT
 ------- ------
  III     II
 ------- ------
  Three   Two
  Drei    Zwei
  San     Ni
  Tres    Dos
 ------- ------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:table --fields=III,II', $expected);

        $expectedSingleField = <<<EOT
Two
Zwei
Ni
Dos
EOT;

        // When --field is specified (instead of --fields), then the format
        // is forced to 'string'.
        $this->assertRunCommandViaApplicationEquals('example:table --field=II', $expectedSingleField);

        // Check the help for the example table command and see if the options
        // from the alter hook were added.  We expect that we should not see
        // any of the information from the alter hook in the 'beta' folder yet.
        $this->assertRunCommandViaApplicationContains('help example:table',
            [
                'Option added by @hook option example:table',
                'example:table --french',
                'Add a row with French numbers.'
            ],
            [
                'chinese',
                'kanji',
            ]
        );

        $expectedOutputWithFrench = <<<EOT
 ------ ------ -------
  I      II     III
 ------ ------ -------
  One    Two    Three
  Eins   Zwei   Drei
  Ichi   Ni     San
  Uno    Dos    Tres
  Un     Deux   Trois
 ------ ------ -------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:table --french', $expectedOutputWithFrench);

        $expectedAssociativeListTable = <<<EOT
 --------------- ----------------------------------------------------------------------------------------
  SFTP Command    sftp -o Port=2222 dev@appserver.dev.drush.in
  Git Command     git clone ssh://codeserver.dev@codeserver.dev.drush.in:2222/~/repository.git wp-update
  MySQL Command   mysql -u pantheon -p4b33cb -h dbserver.dev.drush.in -P 16191 pantheon
 --------------- ----------------------------------------------------------------------------------------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:list', $expectedAssociativeListTable);
        $this->assertRunCommandViaApplicationEquals('example:list --field=sftp_command', 'sftp -o Port=2222 dev@appserver.dev.drush.in');

        $this->assertRunCommandViaApplicationEquals('get:serious', 'very serious');
        $this->assertRunCommandViaApplicationContains('get:lost', 'Command "get:lost" is not defined.', [], 1);

        $this->assertRunCommandViaApplicationContains('help example:wrap',
            [
                'Test word wrapping',
                '[default: "table"]',
            ]
        );

        $expectedUnwrappedOutput = <<<EOT
-------------------------------------------------------------------------------------------------------------------------- ---------------------------------------------------------------------------------------------------------------------------------------------
  First                                                                                                                      Second
 -------------------------------------------------------------------------------------------------------------------------- ---------------------------------------------------------------------------------------------------------------------------------------------
  This is a really long cell that contains a lot of data. When it is rendered, it should be wrapped across multiple lines.   This is the second column of the same table. It is also very long, and should be wrapped across multiple lines, just like the first column.
 -------------------------------------------------------------------------------------------------------------------------- ---------------------------------------------------------------------------------------------------------------------------------------------
EOT;
        $this->application->setWidthAndHeight(0, 0);
        $this->assertRunCommandViaApplicationEquals('example:wrap', $expectedUnwrappedOutput);

        $expectedWrappedOutput = <<<EOT
 ------------------ --------------------
  First              Second
 ------------------ --------------------
  This is a really   This is the second
  long cell that     column of the same
  contains a lot     table. It is also
  of data. When it   very long, and
  is rendered, it    should be wrapped
  should be          across multiple
  wrapped across     lines, just like
  multiple lines.    the first column.
 ------------------ --------------------
EOT;
        $this->application->setWidthAndHeight(42, 24);
        $testTerminal->setWidth(42);
        $this->assertRunCommandViaApplicationEquals('example:wrap', $expectedWrappedOutput);
    }

    function testCommandsAndHooksIncludeAllPublicMethods()
    {
        // First, search for commandfiles in the 'alpha'
        // directory. Note that this same functionality
        // is tested more thoroughly in isolation in
        // testCommandFileDiscovery.php
        $discovery = new CommandFileDiscovery();
        $discovery
          ->setSearchPattern('*CommandFile.php')
          ->setIncludeFilesAtBase(false)
          ->setSearchLocations(['alpha']);

        chdir(__DIR__);
        $commandFiles = $discovery->discover('.', '\Consolidation\TestUtils');

        $formatter = new FormatterManager();
        $formatter->addDefaultFormatters();
        $formatter->addDefaultSimplifiers();
        $hookManager = new HookManager();
        $commandProcessor = new CommandProcessor($hookManager);
        $commandProcessor->setFormatterManager($formatter);

        // Create a new factory, and load all of the files
        // discovered above.  The command factory class is
        // tested in isolation in testAnnotatedCommandFactory.php,
        // but this is the only place where
        $factory = new AnnotatedCommandFactory();
        $factory->setCommandProcessor($commandProcessor);
        // $factory->addListener(...);

        // Now we will once again add all commands, this time including all
        // public methods.  The command 'withoutAnnotations' should now be found.
        $factory->setIncludeAllPublicMethods(true);
        $this->addDiscoveredCommands($factory, $commandFiles);
        $this->assertTrue($this->application->has('without:annotations'));

        $this->assertRunCommandViaApplicationContains('list', ['example:table', 'without:annotations'], ['alter:formatters']);

        $this->assertRunCommandViaApplicationEquals('get:serious', 'very serious');
        $this->assertRunCommandViaApplicationContains('get:lost', 'Command "get:lost" is not defined.', [], 1);
    }

    function testCommandsAndHooksWithBetaFolder()
    {
        // First, search for commandfiles in the 'alpha'
        // directory. Note that this same functionality
        // is tested more thoroughly in isolation in
        // testCommandFileDiscovery.php
        $discovery = new CommandFileDiscovery();
        $discovery
          ->setSearchPattern('*CommandFile.php')
          ->setIncludeFilesAtBase(false)
          ->setSearchLocations(['alpha', 'beta']);

        chdir(__DIR__);
        $commandFiles = $discovery->discover('.', '\Consolidation\TestUtils');

        $formatter = new FormatterManager();
        $formatter->addDefaultFormatters();
        $formatter->addDefaultSimplifiers();
        $hookManager = new HookManager();
        $commandProcessor = new CommandProcessor($hookManager);
        $commandProcessor->setFormatterManager($formatter);

        // Create a new factory, and load all of the files
        // discovered above.  The command factory class is
        // tested in isolation in testAnnotatedCommandFactory.php,
        // but this is the only place where
        $factory = new AnnotatedCommandFactory();
        $factory->setCommandProcessor($commandProcessor);
        // $factory->addListener(...);
        $factory->setIncludeAllPublicMethods(true);
        $this->addDiscoveredCommands($factory, $commandFiles);

        // A few asserts, to make sure that our hooks all get registered.
        $allRegisteredHooks = $hookManager->getAllHooks();
        $registeredHookNames = array_keys($allRegisteredHooks);
        sort($registeredHookNames);
        $this->assertEquals('*,example:table,my-event', implode(',', $registeredHookNames));
        $allHooksForExampleTable = $allRegisteredHooks['example:table'];
        $allHookPhasesForExampleTable = array_keys($allHooksForExampleTable);
        sort($allHookPhasesForExampleTable);
        $this->assertEquals('alter,option', implode(',', $allHookPhasesForExampleTable));

        $this->assertContains('alterFormattersChinese', var_export($allHooksForExampleTable, true));

        $alterHooksForExampleTable = $this->callProtected($hookManager, 'getHooks', [['example:table'], 'alter']);
        $this->assertContains('alterFormattersKanji', var_export($alterHooksForExampleTable, true));

        $allHooksForAnyCommand = $allRegisteredHooks['*'];
        $allHookPhasesForAnyCommand = array_keys($allHooksForAnyCommand);
        sort($allHookPhasesForAnyCommand);
        $this->assertEquals('alter', implode(',', $allHookPhasesForAnyCommand));

        $this->assertContains('alterFormattersKanji', var_export($allHooksForAnyCommand, true));

        // Help should have the information from the hooks in the 'beta' folder
        $this->assertRunCommandViaApplicationContains('help example:table',
            [
                'Option added by @hook option example:table',
                'example:table --french',
                'Add a row with French numbers.',
                'chinese',
                'kanji',
            ]
        );

        // Confirm that the "unavailable" command is now available
        $this->assertTrue($this->application->has('unavailable:command'));

        $expectedOutputWithChinese = <<<EOT
 ------ ------ -------
  I      II     III
 ------ ------ -------
  One    Two    Three
  Eins   Zwei   Drei
  Ichi   Ni     San
  Uno    Dos    Tres
  壹     貳     叁
 ------ ------ -------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:table --chinese', $expectedOutputWithChinese);

        $expectedOutputWithKanji = <<<EOT
 ------ ------ -------
  I      II     III
 ------ ------ -------
  One    Two    Three
  Eins   Zwei   Drei
  Ichi   Ni     San
  Uno    Dos    Tres
  一     二     三
 ------ ------ -------
EOT;
        $this->assertRunCommandViaApplicationEquals('example:table --kanji', $expectedOutputWithKanji);
    }

    public function addDiscoveredCommands($factory, $commandFiles) {
        foreach ($commandFiles as $path => $commandClass) {
            $this->assertFileExists($path);
            if (!class_exists($commandClass)) {
                include $path;
            }
            $commandInstance = new $commandClass();
            $commandList = $factory->createCommandsFromClass($commandInstance);
            foreach ($commandList as $command) {
                $this->application->add($command);
            }
        }
    }

    function assertRunCommandViaApplicationEquals($cmd, $expectedOutput, $expectedStatusCode = 0)
    {
        $input = new StringInput($cmd);
        $output = new BufferedOutput();

        $statusCode = $this->application->run($input, $output);
        $commandOutput = trim($output->fetch());

        $expectedOutput = $this->simplifyWhitespace($expectedOutput);
        $commandOutput = $this->simplifyWhitespace($commandOutput);

        $this->assertEquals($expectedOutput, $commandOutput);
        $this->assertEquals($expectedStatusCode, $statusCode);
    }

    function assertRunCommandViaApplicationContains($cmd, $containsList, $doesNotContainList = [], $expectedStatusCode = 0)
    {
        $input = new StringInput($cmd);
        $output = new BufferedOutput();
        $containsList = (array) $containsList;

        $statusCode = $this->application->run($input, $output);
        $commandOutput = trim($output->fetch());

        $commandOutput = $this->simplifyWhitespace($commandOutput);

        foreach ($containsList as $expectedToContain) {
            $this->assertContains($this->simplifyWhitespace($expectedToContain), $commandOutput);
        }
        foreach ($doesNotContainList as $expectedToNotContain) {
            $this->assertNotContains($this->simplifyWhitespace($expectedToNotContain), $commandOutput);
        }
        $this->assertEquals($expectedStatusCode, $statusCode);
    }

    function simplifyWhitespace($data)
    {
        return trim(preg_replace('#\s+$#m', '', $data));
    }

    function callProtected($object, $method, $args = [])
    {
        $r = new \ReflectionMethod($object, $method);
        $r->setAccessible(true);
        return $r->invokeArgs($object, $args);
    }

}

class ExampleValidator implements ValidatorInterface
{
    public function validate(CommandData $commandData)
    {
        $args = $commandData->arguments();
        if (isset($args['one']) && ($args['one'] == 'bet')) {
            $commandData->input()->setArgument('one', 'replaced');
            return $args;
        }
    }
}

class ExampleResultProcessor implements ProcessResultInterface
{
    public function process($result, CommandData $commandData)
    {
        if (is_array($result) && array_key_exists('item-list', $result)) {
            return implode(',', $result['item-list']);
        }
    }
}

class ExampleResultAlterer implements AlterResultInterface
{
    public function process($result, CommandData $commandData)
    {
        if (is_string($result) && ($result == 'Hello, World.')) {
            return 'Hello, World!';
        }
    }
}

class ExampleStatusDeterminer implements StatusDeterminerInterface
{
    public function determineStatusCode($result)
    {
        if (is_array($result) && array_key_exists('status-code', $result)) {
            return $result['status-code'];
        }
    }
}

class ExampleOutputExtractor implements ExtractOutputInterface
{
    public function extractOutput($result)
    {
        if (is_array($result) && array_key_exists('message', $result)) {
            return $result['message'];
        }
    }
}
