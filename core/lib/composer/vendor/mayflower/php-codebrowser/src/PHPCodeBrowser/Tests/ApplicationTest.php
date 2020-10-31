<?php

namespace PHPCodeBrowser\Tests;

use PHPCodeBrowser\Application;

/**
 * Class ApplicationTest
 * @package PHPCodeBrowser\Tests
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp()
    {
        $this->application = new Application();
    }

    public function testCommand()
    {
        $this->assertInstanceOf('PHPCodeBrowser\\Command\\RunCommand', $this->application->get('phpcb'));
    }
}
