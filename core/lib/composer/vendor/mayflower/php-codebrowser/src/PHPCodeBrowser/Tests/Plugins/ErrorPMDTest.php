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
 * @since      File available since  0.9.0
 */

namespace PHPCodeBrowser\Tests\Plugins;


use DOMDocument;
use PHPCodeBrowser\File;
use PHPCodeBrowser\Issue;
use PHPCodeBrowser\IssueXml;
use PHPCodeBrowser\Plugins\ErrorPMD;
use PHPCodeBrowser\Tests\AbstractTestCase;

/**
 * ErrorPMDTest
 *
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage PHPUnit
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright  2007-2010 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since  0.9.0
 */
class ErrorPMDTest extends AbstractTestCase
{
    /**
     * The object to test.
     *
     * @var ErrorPMD
     */
    protected $errorPmd;

    /**
     * The xml string to test the plugin against.
     *
     * @var string
     */
    protected $testXml = <<<HERE
<?xml version="1.0" encoding="UTF-8" ?>
<pmd version="0.2.6" timestamp="2010-07-17T02:38:00-07:00">
  <file name="/some/file">
    <violation beginline="3"
               endline="4"
               rule="Rule1"
               ruleset="Ruleset 1"
               priority="1">Description 1</violation>
    <violation beginline="5"
               endline="5"
               rule="Rule2"
               ruleset="Ruleset 1"
               class="SomeClass"
               method="someMethod"
               priority="3">Description 2</violation>
  </file>
  <file name="/other/file">
    <violation beginline="15"
               endline="15"
               rule="The third rule"
               ruleset="Ruleset two"
               priority="3">Description 3</violation>
  </file>
  <file name="/has/no/violation">
  </file>
</pmd>
HERE;

    /**
     * (non-PHPDoc)
     * @see tests/cbAbstractTests#setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $issueXML = new IssueXML();
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->loadXML($this->testXml);
        $issueXML->addXMLFile($xml);
        $this->errorPmd = new ErrorPMD($issueXML);
    }

    /**
     * Test getFileList
     *
     * @return  void
     */
    public function testGettingFileList()
    {
        $expected = array(
            new File(
                '/some/file',
                array(
                    new Issue(
                        '/some/file',
                        3,
                        4,
                        'PMD',
                        'Description 1',
                        'error'
                    ),
                    new Issue(
                        '/some/file',
                        5,
                        5,
                        'PMD',
                        'Description 2',
                        'error'
                    )
                )
            ),
            new File(
                '/other/file',
                array(
                    new Issue(
                        '/other/file',
                        15,
                        15,
                        'PMD',
                        'Description 3',
                        'error'
                    )
                )
            ),
            new File(
                '/has/no/violation',
                array()
            )
        );
        $actual = $this->errorPmd->getFileList();
        $this->assertEquals($expected, $actual);
    }
}
