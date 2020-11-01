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
use PHPCodeBrowser\Plugins\ErrorCoverage;
use PHPCodeBrowser\Tests\AbstractTestCase;

/**
 * ErrorCoverageTest
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
class ErrorCoverageTest extends AbstractTestCase
{
    /**
     * The object to test.
     *
     * @var ErrorCoverage
     */
    protected $errorCoverage;

    /**
     * The xml string to test the plugin against.
     *
     * @var string
     */
    protected $testXml = <<<HERE
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="1279365369">
  <project timestamp="1279365369">
    <file name="/partly/tested">
      <line num="1" type="stmt" count="1"/>
      <line num="2" type="stmt" count="1"/>
      <line num="3" type="stmt" count="2"/>
      <line num="4" type="stmt" count="2"/>
      <line num="5" type="stmt" count="1"/>
      <line num="10" type="stmt" count="0"/>
      <line num="11" type="stmt" count="0"/>
      <line num="12" type="stmt" count="0"/>
      <line num="13" type="stmt" count="0"/>
      <line num="14" type="stmt" count="0"/>
      <line num="21" type="stmt" count="2"/>
      <line num="22" type="stmt" count="2"/>
    </file>
    <file name="/totally/tested">
      <line num="212" type="stmt" count="2"/>
      <line num="213" type="stmt" count="2"/>
      <line num="215" type="stmt" count="2"/>
      <line num="216" type="stmt" count="2"/>
    </file>
    <file name="/not/tested">
      <line num="212" type="stmt" count="0"/>
      <line num="213" type="stmt" count="0"/>
      <line num="214" type="stmt" count="0"/>
      <line num="216" type="stmt" count="0"/>
      <line num="219" type="stmt" count="0"/>
      <line num="220" type="stmt" count="0"/>
      <line num="221" type="stmt" count="0"/>
      <line num="224" type="stmt" count="0"/>
      <line num="225" type="stmt" count="0"/>
    </file>
  </project>
</coverage>
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
        $this->errorCoverage = new ErrorCoverage($issueXML);
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
                '/partly/tested',
                array(
                    new Issue(
                        '/partly/tested',
                        10,
                        14,
                        'Coverage',
                        'Not covered',
                        'Notice'
                    )
                )
            ),
            new File(
                '/totally/tested',
                array()
            ),
            new File(
                '/not/tested',
                array(
                    new Issue(
                        '/not/tested',
                        212,
                        225,
                        'Coverage',
                        'Not covered',
                        'Notice'
                    )
                )
            )
        );
        $actual = $this->errorCoverage->getFileList();
        $this->assertEquals($expected, $actual);
    }
}
