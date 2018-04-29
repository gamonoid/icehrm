<?php
namespace Consolidation\Config\Loader;

use Consolidation\TestUtils\TestLoader;

class ConfigProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigProcessorAdd()
    {
        $config1 = [
            'c' => 'foo',
            'm' => [1],
        ];
        $config2 = [
            'b' => '${c}bar',
            'm' => [2],
        ];
        $config3 = [
            'a' => '${b}baz',
            'm' => [3],
        ];

        $processor = new ConfigProcessor();
        $processor->add($config1);
        $processor->add($config2);
        $processor->add($config3);

        $data = $processor->export();
        $this->assertEquals('foo', $data['c']);
        $this->assertEquals('foobar', $data['b']);
        $this->assertEquals('foobarbaz', $data['a']);
    }

    public function processorForConfigMergeTest($provideSourceNames)
    {
        $config1 = [
            'm' => [
                'x' => 'x-1',
                'y' => [
                    'r' => 'r-1',
                    's' => 's-1',
                    't' => 't-1',
                ],
                'z' => 'z-1',
            ],
        ];
        $config2 = [
            'm' => [
                'w' => 'w-2',
                'y' => [
                    'q' => 'q-2',
                    's' => 's-2',
                ],
                'z' => 'z-2',
            ],
        ];
        $config3 = [
            'm' => [
                'v' => 'v-3',
                'y' => [
                    't' => 't-3',
                    'u' => 'u-3',
                ],
                'z' => 'z-3',
            ],
        ];

        $processor = new ConfigProcessor();
        $testLoader = new TestLoader();

        $testLoader->set($config1);
        $testLoader->setSourceName($provideSourceNames ? 'c-1' : '');
        $processor->extend($testLoader);

        $testLoader->set($config2);
        $testLoader->setSourceName($provideSourceNames ? 'c-2' : '');
        $processor->extend($testLoader);

        $testLoader->set($config3);
        $testLoader->setSourceName($provideSourceNames ? 'c-3' : '');
        $processor->extend($testLoader);

        return $processor;
    }

    public function testConfigProcessorMergeAssociative()
    {
        $processor = $this->processorForConfigMergeTest(false);
        $data = $processor->export();
        $this->assertEquals('{"m":{"x":"x-1","y":{"r":"r-1","s":"s-2","t":"t-3","q":"q-2","u":"u-3"},"z":"z-3","w":"w-2","v":"v-3"}}', json_encode($data));
    }

    public function testConfigProcessorMergeAssociativeWithSourceNames()
    {
        $processor = $this->processorForConfigMergeTest(true);
        $sources = $processor->sources();
        $data = $processor->export();
        $this->assertEquals('{"m":{"x":"x-1","y":{"r":"r-1","s":"s-2","t":"t-3","q":"q-2","u":"u-3"},"z":"z-3","w":"w-2","v":"v-3"}}', json_encode($data));
        $this->assertEquals('c-1', $sources['m']['x']);
        $this->assertEquals('c-1', $sources['m']['y']['r']);
        $this->assertEquals('c-2', $sources['m']['w']);
        $this->assertEquals('c-2', $sources['m']['y']['s']);
        $this->assertEquals('c-3', $sources['m']['z']);
        $this->assertEquals('c-3', $sources['m']['y']['u']);
    }

    public function testConfiProcessorSources()
    {
        $processor = new ConfigProcessor();
        $loader = new YamlConfigLoader();
        $processor->extend($loader->load(__DIR__ . '/data/config-1.yml'));
        $processor->extend($loader->load(__DIR__ . '/data/config-2.yml'));
        $processor->extend($loader->load(__DIR__ . '/data/config-3.yml'));

        $sources = $processor->sources();

        $data = $processor->export();
        $this->assertEquals('foo', $data['c']);
        $this->assertEquals('foobar', $data['b']);
        $this->assertEquals('foobarbaz', $data['a']);

        $this->assertEquals('3', $data['m'][0]);

        $this->assertEquals( __DIR__ . '/data/config-3.yml', $sources['a']);
        $this->assertEquals( __DIR__ . '/data/config-2.yml', $sources['b']);
        $this->assertEquals( __DIR__ . '/data/config-1.yml', $sources['c']);
        $this->assertEquals( __DIR__ . '/data/config-3.yml', $sources['m']);
    }

    public function testConfiProcessorSourcesLoadInReverseOrder()
    {
        $processor = new ConfigProcessor();
        $loader = new YamlConfigLoader();
        $processor->extend($loader->load(__DIR__ . '/data/config-3.yml'));
        $processor->extend($loader->load(__DIR__ . '/data/config-2.yml'));
        $processor->extend($loader->load(__DIR__ . '/data/config-1.yml'));

        $sources = $processor->sources();

        $data = $processor->export();
        $this->assertEquals('foo', $data['c']);
        $this->assertEquals('foobar', $data['b']);
        $this->assertEquals('foobarbaz', $data['a']);

        $this->assertEquals('1', $data['m'][0]);

        $this->assertEquals( __DIR__ . '/data/config-3.yml', $sources['a']);
        $this->assertEquals( __DIR__ . '/data/config-2.yml', $sources['b']);
        $this->assertEquals( __DIR__ . '/data/config-1.yml', $sources['c']);
        $this->assertEquals( __DIR__ . '/data/config-1.yml', $sources['m']);
    }
}
