<?php
namespace Consolidation\Config\Util;

use Consolidation\Config\Config;

class ConfigGroupTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    protected function setUp()
    {
        $data = [
            // Define some configuration settings for the options for
            // the commands my:foo and my:bar.
            'command' => [
                'my' => [
                    // commands.my.options.* apply to all my:* commands.
                    'options' => [
                        'path' => '/etc/common',
                        'priority' => 'normal',
                    ],
                    'foo' => [
                        // commands.my.foo.options.* apply only to the my:foo command.
                        'options' => [
                            'name' => 'baz',
                        ],
                    ],
                    'bar' => [
                        // Similarly, commands.my.bar.options is for the my:bar command.
                        'options' => [
                            'priority' => 'high',
                        ],
                    ],
                ],
            ],
            // Define some configuration settings for the configuration
            // of some task \My\Tasks\Operations\Frobulate.
            'task' => [
                'Operations' => [
                    // task.Operations.settings apply to all tasks in
                    // any *.Tass.Operations namespace.
                    'settings' => [
                        'dir' => '/base/dir',
                    ],
                    'Frobulate' => [
                        // task.Operations.Frobulate.settings applies only
                        // the Frobulate task.
                        'settings' => [
                            'object' => 'fire truck',
                        ],
                    ],
                ],
            ],
        ];

        $this->config = new Config($data);
    }

    public function testDotNotation()
    {
        // Test the test
        $this->assertEquals('baz', $this->config->get('command.my.foo.options.name'));
    }

    public function testFallback()
    {
        $fooFallback = new ConfigFallback($this->config, 'my.foo', 'command.', '.options.');
        $barFallback = new ConfigFallback($this->config, 'my.bar', 'command.', '.options.');

        $this->assertEquals(null, $barFallback->get('name'));
        $this->assertEquals('baz', $fooFallback->get('name'));
        $this->assertEquals('high', $barFallback->get('priority'));

        $this->assertEquals('normal', $fooFallback->get('priority'));
        $this->assertEquals('/etc/common', $barFallback->get('path'));
        $this->assertEquals('/etc/common', $fooFallback->get('path'));
    }

    public function testMerge()
    {
        $frobulateMerge = new ConfigMerge($this->config, 'Operations.Frobulate', 'task.');

        $settings = $frobulateMerge->get('settings');
        $this->assertEquals('fire truck', $settings['object']);
        $this->assertEquals('/base/dir', $settings['dir']);
        $keys = array_keys($settings);
        sort($keys);
        $this->assertEquals('dir,object', implode(',', $keys));
    }
}

