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
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use \Consolidation\AnnotatedCommand\Parser\Internal\FullyQualifiedClassCache;

class FullyQualifiedClassCacheTests extends \PHPUnit_Framework_TestCase
{
    function testFqcn()
    {
        $reflectionMethod = new \ReflectionMethod('\Consolidation\TestUtils\alpha\AlphaCommandFile', 'exampleTableTwo');
        $filename = $reflectionMethod->getFileName();

        $fqcnCache = new FullyQualifiedClassCache();

        $handle = fopen($filename, "r");
        $this->assertTrue($handle !== false);

        $namespaceName = $this->callProtected($fqcnCache, 'readNamespace', [$handle]);

        $this->assertEquals('Consolidation\TestUtils\alpha', $namespaceName);

        $usedClasses = $this->callProtected($fqcnCache, 'readUseStatements', [$handle]);

        $this->assertTrue(isset($usedClasses['RowsOfFields']));
        $this->assertEquals('Consolidation\OutputFormatters\StructuredData\RowsOfFields', $usedClasses['RowsOfFields']);

        fclose($handle);

        $fqcn = $fqcnCache->qualify($filename, 'RowsOfFields');
        $this->assertEquals('Consolidation\OutputFormatters\StructuredData\RowsOfFields', $fqcn);

        $fqcn = $fqcnCache->qualify($filename, 'ClassWithoutUse');
        $this->assertEquals('Consolidation\TestUtils\alpha\ClassWithoutUse', $fqcn);

        $fqcn = $fqcnCache->qualify($filename, 'ExampleAliasedClass');
        $this->assertEquals('Consolidation\TestUtils\ExampleCommandFile', $fqcn);
    }

    function callProtected($object, $method, $args = [])
    {
        $r = new \ReflectionMethod($object, $method);
        $r->setAccessible(true);
        return $r->invokeArgs($object, $args);
    }
}
