<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\Parser\CommandInfoSerializer;
use Consolidation\AnnotatedCommand\Parser\CommandInfoDeserializer;

class CommandInfoTests extends \PHPUnit_Framework_TestCase
{
    function flattenArray($actualValue)
    {
        $result = [];
        foreach ($actualValue as $key => $value) {
          if (!is_string($value)) {
              $value = var_export($value, true);
          }
          $result[] = "{$key}=>{$value}";
        }
        return implode("\n", $result);
    }

    /**
     * Test CommandInfo command annotation parsing.
     */
    function testParsing()
    {
        $commandInfo = CommandInfo::create('\Consolidation\TestUtils\ExampleCommandFile', 'testArithmatic');
        $this->assertCommandInfoIsAsExpected($commandInfo);

        $serializer = new CommandInfoSerializer();
        $serialized = $serializer->serialize($commandInfo);

        $deserializer = new CommandInfoDeserializer();

        $deserializedCommandInfo = $deserializer->deserialize($serialized);
        $this->assertCommandInfoIsAsExpected($deserializedCommandInfo);
    }

    function testWithConfigImport()
    {
        $commandInfo = CommandInfo::create('\Consolidation\TestUtils\ExampleCommandFile', 'import');
        $this->assertEquals('config:import', $commandInfo->getName());

        $this->assertEquals(
            'A config directory label (i.e. a key in \$config_directories array in settings.php).',
            $commandInfo->arguments()->getDescription('label')
        );
    }

    function assertCommandInfoIsAsExpected($commandInfo)
    {
        $this->assertEquals('test:arithmatic', $commandInfo->getName());
        $this->assertEquals(
            'This is the test:arithmatic command',
            $commandInfo->getDescription()
        );
        $this->assertEquals(
            "This command will add one and two. If the --negate flag\nis provided, then the result is negated.",
            $commandInfo->getHelp()
        );
        $this->assertEquals('arithmatic', implode(',', $commandInfo->getAliases()));
        $this->assertEquals(
            '2 2 --negate=>Add two plus two and then negate.',
            $this->flattenArray($commandInfo->getExampleUsages())
        );
        $this->assertEquals(
            'The first number to add.',
            $commandInfo->arguments()->getDescription('one')
        );
        $this->assertEquals(
            'The other number to add.',
            $commandInfo->arguments()->getDescription('two')
        );
        $this->assertEquals(
            '2',
            $commandInfo->arguments()->get('two')
        );
        $this->assertEquals(
            'Whether or not the result should be negated.',
            $commandInfo->options()->getDescription('negate')
        );
        $this->assertEquals(
            'bob',
            $commandInfo->options()->get('unused')
        );
        $this->assertEquals(
            'one,two',
            $commandInfo->getAnnotation('dup')
        );
        $this->assertEquals(
            ['one','two'],
            $commandInfo->getAnnotationList('dup')
        );
    }

    function testReturnValue()
    {
        $commandInfo = CommandInfo::create('\Consolidation\TestUtils\alpha\AlphaCommandFile', 'exampleTable');
        $this->assertEquals('example:table', $commandInfo->getName());
        $this->assertEquals('\Consolidation\OutputFormatters\StructuredData\RowsOfFields', $commandInfo->getReturnType());
    }
}
