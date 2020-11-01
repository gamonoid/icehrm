<?php
/**
 * Test case
 *
 * Copyright (c) 2007-2010, Mayflower GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Mayflower GmbH nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage PHPUnit
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de
 * @copyright  2007-2010 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since  0.1.0
 */

namespace PHPCodeBrowser\Tests;

use Exception;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPCodeBrowser\File;
use PHPCodeBrowser\Issue;
use PHPCodeBrowser\IssueXml;
use PHPCodeBrowser\Plugins\ErrorCheckstyle;
use PHPCodeBrowser\Plugins\ErrorPMD;
use PHPCodeBrowser\SourceHandler;
use SplFileInfo;

/**
 * SourceHandlerTest
 *
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage PHPUnit
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright  2007-2010 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since  0.1.0
 */
class SourceHandlerTest extends AbstractTestCase
{
    /**
     * SourceHandler object to test
     *
     * @var SourceHandler
     */
    protected $sourceHandler;

    /**
     * Plugin array populated with example files.
     * TODO: Mock this
     *
     * @var array of PluginsAbstract
     */
    protected $plugins;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Initializes common values.
     */
    public function __construct()
    {
        $xmlStrings = array(
            <<<HERE
<?xml version="1.0" encoding="UTF-8"?>
<pmd version="0.2.6" timestamp="2010-08-12T00:00:00+02:000">
    <file name='/a/nother/dir/src.php'>
        <violation beginline="291" endline="291" rule="ExitExpression"
            ruleset="Design Rules"
            externalInfoUrl="http://example.com" priority="1">descr</violation>
    </file>
</pmd>
HERE
        ,
            <<<HERE
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle version="1.2.0RC3">
    <file name="/a/dir/source.php">
        <error line="37" column="1" severity="error"
            message="m1" source="PEAR.Commenting.FileCommentSniff"/>
    </file>
    <file name="/a/nother/dir/src.php">
        <error line="39" column="1" severity="error"
            message="m3" source="PEAR.Commenting.FileCommentSniff"/>
        <error line="40" column="1" severity="error"
            message="m4" source="PEAR.Commenting.FileCommentSniff"/>
    </file>
</checkstyle>
HERE
        );
        $issueXML = new IssueXml();
        foreach ($xmlStrings as $xmlString) {
            $xml      = new \DOMDocument('1.0', 'UTF-8');
            $xml->validateOnParse = true;
            $xml->loadXML($xmlString);
            $issueXML->addXMLFile($xml);
        }
        $this->plugins = array(
            new ErrorCheckstyle($issueXML),
            new ErrorPMD($issueXML)
        );
    }

    /**
     * (non-PHPDoc)
     * @see AbstractTests#setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $this->logger = new Logger('PHPCodeBrowser');
        $this->logger->pushHandler(new NullHandler());

        $this->sourceHandler = new SourceHandler($this->logger);
        array_walk(
            $this->plugins,
            array($this->sourceHandler, 'addPlugin')
        );
    }

    /**
     * Test the constructor.
     *
     * @return void.
     */
    public function testInstantiation()
    {
        $sourceHandler = new SourceHandler(
            $this->logger,
            $this->plugins
        );
        $this->assertEquals($this->sourceHandler, $sourceHandler);
    }

    /**
     * Test getFiles.
     *
     * @return void
     */
    public function testGetFiles()
    {
        $expected = array(
            '/a/nother/dir/src.php' => new File(
                '/a/nother/dir/src.php',
                array(
                    new Issue('/a/nother/dir/src.php', 39, 39, 'Checkstyle', 'm3', 'error'),
                    new Issue('/a/nother/dir/src.php', 40, 40, 'Checkstyle', 'm4', 'error'),
                    new Issue('/a/nother/dir/src.php', 291, 291, 'PMD', 'descr', 'error')
                )
            ),
            '/a/dir/source.php' => new File(
                '/a/dir/source.php',
                array(
                    new Issue('/a/dir/source.php', 37, 37, 'Checkstyle', 'm1', 'error')
                )
            )
        );
        File::sort($expected);

        $actual = $this->sourceHandler->getFiles();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test getFilesWithIssues
     *
     * @return void
     */
    public function testGetFilesWithIssues()
    {
        $expectedFiles = array (
            '/a/dir/source.php',
            '/a/nother/dir/src.php'
        );
        $actualFiles = $this->sourceHandler->getFilesWithIssues();
        $this->assertEquals($expectedFiles, $actualFiles);
    }

    /**
     * Test addSourceFiles
     *
     * @return void
     */
    public function testAddSourceFiles()
    {
        $this->sourceHandler->addSourceFiles(
            array(new SplFileInfo(__FILE__), __FILE__)
        );
        $this->assertContains(__FILE__, array_keys($this->sourceHandler->getFiles()));
    }

    /**
     * Test if addSourceFile chokes on non-existent files.
     *
     * @return void
     */
    public function testAddSourceFilesWithNonExisting()
    {
        try {
            $this->sourceHandler->addSourceFiles(
                array(new SplFileInfo('/i/do/not/exist'))
            );
        } catch (Exception $e) {
            //expected
            return;
        }
        $this->fail('Expected Exception was not thrown');
    }

    /**
     * Test getCommonPathPrefix
     *
     * @return void
     */
    public function testGetCommonPathPrefix()
    {
        $expected = '/a/';
        $actual   = $this->sourceHandler->getCommonPathPrefix();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test excludeMatchingPCRE
     *
     * @return void
     */
    public function testExcludeMatchingPCRE()
    {
        $expected = array(
            '/a/dir/source.php' => new File(
                '/a/dir/source.php',
                array(
                    new Issue('/a/dir/source.php', 37, 37, 'Checkstyle', 'm1', 'error')
                )
            )
        );
        $this->sourceHandler->excludeMatchingPCRE('/^\/a.*src\.php$/');
        $this->assertEquals($expected, $this->sourceHandler->getFiles());
    }

    /**
     * Test excludeMatchingPattern
     *
     * @return void
     */
    public function testExcludeMatchingPattern()
    {
        $expected = array(
            '/a/dir/source.php' => new File(
                '/a/dir/source.php',
                array(
                    new Issue('/a/dir/source.php', 37, 37, 'Checkstyle', 'm1', 'error')
                )
            )
        );
        $this->sourceHandler->excludeMatchingPattern('*src.php');
        $this->assertEquals($expected, $this->sourceHandler->getFiles());
    }
}
