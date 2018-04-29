<?php
namespace Consolidation\Config\Loader;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigLoader()
    {
        $loader = new YamlConfigLoader();

        // Assert that our test data exists (test the test)
        $path = __DIR__ . '/data/config-1.yml';
        $this->assertTrue(file_exists($path));

        $loader->load($path);

        $configFile = basename($loader->getSourceName());
        $this->assertEquals('config-1.yml', $configFile);

        // Make sure that the data we loaded contained the expected keys
        $keys = $loader->keys();
        sort($keys);
        $keysString = implode(',', $keys);
        $this->assertEquals('c,m', $keysString);

        $configData = $loader->export();
        $this->assertEquals('foo', $configData['c']);
        $this->assertEquals('1', $configData['m'][0]);
    }
}
