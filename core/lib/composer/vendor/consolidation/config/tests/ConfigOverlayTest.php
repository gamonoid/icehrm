<?php
namespace Consolidation\Config\Util;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Loader\YamlConfigLoader;

class ConfigOverlayTest extends \PHPUnit_Framework_TestCase
{
    protected $overlay;

    protected function setUp()
    {
        $aliasContext = new Config();
        $aliasContext->import([
            'hidden-by-a' => 'alias hidden-by-a',
            'hidden-by-process' => 'alias hidden-by-process',
            'options' =>[
                'a-a' => 'alias-a',
            ],
            'command' => [
                'foo' => [
                    'bar' => [
                        'command' => [
                            'options' => [
                                'a-b' => 'alias-b',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $configFileContext = new Config();
        $configFileContext->import([
            'hidden-by-cf' => 'config-file hidden-by-cf',
            'hidden-by-a' => 'config-file hidden-by-a',
            'hidden-by-process' => 'config-file hidden-by-process',
            'options' =>[
                'cf-a' => 'config-file-a',
            ],
            'command' => [
                'foo' => [
                    'bar' => [
                        'command' => [
                            'options' => [
                                'cf-b' => 'config-file-b',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->overlay = new ConfigOverlay();
        $this->overlay->set('hidden-by-process', 'process-h');
        $this->overlay->addContext('cf', $configFileContext);
        $this->overlay->addContext('a', $aliasContext);
        $this->overlay->setDefault('df-a', 'default');
        $this->overlay->setDefault('hidden-by-a', 'default hidden-by-a');
        $this->overlay->setDefault('hidden-by-cf', 'default hidden-by-cf');
        $this->overlay->setDefault('hidden-by-process', 'default hidden-by-process');
    }

    public function testGetPriority()
    {
        $this->assertEquals('process-h', $this->overlay->get('hidden-by-process'));
        $this->assertEquals('config-file hidden-by-cf', $this->overlay->get('hidden-by-cf'));
        $this->assertEquals('alias hidden-by-a', $this->overlay->get('hidden-by-a'));
    }

    public function testDefault()
    {
        $this->assertEquals('alias-a', $this->overlay->get('options.a-a'));
        $this->assertEquals('alias-a', $this->overlay->get('options.a-a', 'ignored'));
        $this->assertEquals('default', $this->overlay->getDefault('df-a', 'ignored'));
        $this->assertEquals('nsv', $this->overlay->getDefault('a-a', 'nsv'));

        $this->overlay->setDefault('df-a', 'new value');
        $this->assertEquals('new value', $this->overlay->getDefault('df-a', 'ignored'));
    }

    public function testExport()
    {
        $data = $this->overlay->export();

        $this->assertEquals('config-file-a', $data['options']['cf-a']);
        $this->assertEquals('alias-a', $data['options']['a-a']);
    }

    /**
     * @expectedException Exception
     */
    public function testImport()
    {
        $data = $this->overlay->import(['a' => 'value']);
    }

    public function testMaintainPriority()
    {
        // Get and re-add the 'cf' context. Its priority should not change.
        $configFileContext = $this->overlay->getContext('cf');
        $this->overlay->addContext('cf', $configFileContext);

        // These asserts are the same as in testGetPriority
        $this->assertEquals('process-h', $this->overlay->get('hidden-by-process'));
        $this->assertEquals('config-file hidden-by-cf', $this->overlay->get('hidden-by-cf'));
        $this->assertEquals('alias hidden-by-a', $this->overlay->get('hidden-by-a'));
    }

    public function testChangePriority()
    {
        // Increase the priority of the 'cf' context. Now, it should have a higher
        // priority than the 'alias' context, but should still have a lower
        // priority than the 'process' context.
        $this->overlay->increasePriority('cf');

        // These asserts are the same as in testGetPriority
        $this->assertEquals('process-h', $this->overlay->get('hidden-by-process'));
        $this->assertEquals('config-file hidden-by-cf', $this->overlay->get('hidden-by-cf'));

        // This one has changed: the config-file value is now found instead
        // of the alias value.
        $this->assertEquals('config-file hidden-by-a', $this->overlay->get('hidden-by-a'));
    }

    public function testPlaceholder()
    {
        $this->overlay->addPlaceholder('lower');

        $higherContext = new Config();
        $higherContext->import(['priority-test' => 'higher']);

        $lowerContext = new Config();
        $lowerContext->import(['priority-test' => 'lower']);

        // Usually 'lower' would have the highest priority, since it is
        // added last. However, our earlier call to 'addPlaceholder' reserves
        // a spot for it, so the 'higher' context will end up with a higher
        // priority.
        $this->overlay->addContext('higher', $higherContext);
        $this->overlay->addContext('lower', $lowerContext);
        $this->assertEquals('higher', $this->overlay->get('priority-test', 'neither'));

        // Test to see that we can change the value of the 'higher' context,
        // and the change will be reflected in the overlay.
        $higherContext->set('priority-test', 'changed');
        $this->assertEquals('changed', $this->overlay->get('priority-test', 'neither'));

        // Test to see that the 'process' context still has the highest priority.
        $this->overlay->set('priority-test', 'process');
        $higherContext->set('priority-test', 'ignored');
        $this->assertEquals('process', $this->overlay->get('priority-test', 'neither'));
    }

    public function testDoesNotHave()
    {
        $context = $this->overlay->getContext('no-such-context');
        $data = $context->export();
        $this->assertEquals('[]', json_encode($data));

        $this->assertTrue(!$this->overlay->has('no-such-key'));
        $this->assertTrue(!$this->overlay->hasDefault('no-such-default'));

        $this->assertEquals('no-such-value', $this->overlay->get('no-such-key', 'no-such-value'));

    }
}
