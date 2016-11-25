<?php
namespace Consolidation\AnnotatedCommand;

class CommandFileDiscoveryTests extends \PHPUnit_Framework_TestCase
{
    function testCommandDiscovery()
    {
        $discovery = new CommandFileDiscovery();
        $discovery
          ->setSearchPattern('*CommandFile.php')
          ->setSearchLocations(['alpha']);

        chdir(__DIR__);
        $commandFiles = $discovery->discover('.', '\Consolidation\TestUtils');

        $commandFilePaths = array_keys($commandFiles);
        $commandFileNamespaces = array_values($commandFiles);

        // Ensure that the command files that we expected to
        // find were all found.  We don't find anything in
        // 'beta' because only 'alpha' is in the search path.
        $this->assertContains('./src/ExampleCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/ExampleHookAllCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/alpha/AlphaCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/alpha/Inclusive/IncludedCommandFile.php', $commandFilePaths);

        // Make sure that there are no additional items found.
        $this->assertEquals(4, count($commandFilePaths));

        // Ensure that the command file namespaces that we expected
        // to be generated all match.
        $this->assertContains('\Consolidation\TestUtils\ExampleCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\ExampleHookAllCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\alpha\AlphaCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\alpha\Inclusive\IncludedCommandFile', $commandFileNamespaces);

        // We do not need to test for additional namespace items, because we
        // know that the length of the array_keys must be the same as the
        // length of the array_values.
    }

    function testDeepCommandDiscovery()
    {
        $discovery = new CommandFileDiscovery();
        $discovery
          ->setSearchPattern('*CommandFile.php')
          ->setSearchDepth(1)
          ->setSearchLocations([]);

        chdir(__DIR__);
        $commandFiles = $discovery->discover('.', '\Consolidation\TestUtils');

        $commandFilePaths = array_keys($commandFiles);
        $commandFileNamespaces = array_values($commandFiles);

        // Ensure that the command files that we expected to
        // find were all found. We find both 'alpha' and 'beta'
        // items because the search locations is empty, which
        // causes the search at the base directory to be deep.
        // We do not find alpha/Inclusive, though, as the search
        // depth is only 2, which excludes directories that are
        // three levels deep.
        $this->assertContains('./src/ExampleCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/ExampleHookAllCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/alpha/AlphaCommandFile.php', $commandFilePaths);
        $this->assertContains('./src/beta/BetaCommandFile.php', $commandFilePaths);

        // Make sure that there are no additional items found.
        $this->assertEquals(4, count($commandFilePaths));

        // Ensure that the command file namespaces that we expected
        // to be generated all match.
        $this->assertContains('\Consolidation\TestUtils\ExampleCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\ExampleHookAllCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\alpha\AlphaCommandFile', $commandFileNamespaces);
        $this->assertContains('\Consolidation\TestUtils\beta\BetaCommandFile', $commandFileNamespaces);

        // We do not need to test for additional namespace items, because we
        // know that the length of the array_keys must be the same as the
        // length of the array_values.
    }
}
