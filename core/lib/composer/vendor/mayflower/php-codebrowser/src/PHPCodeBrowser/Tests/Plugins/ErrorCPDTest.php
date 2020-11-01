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
use PHPCodeBrowser\Plugins\ErrorCPD;
use PHPCodeBrowser\Tests\AbstractTestCase;

/**
 * ErrorCPDTest
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
class ErrorCPDTest extends AbstractTestCase
{
    /**
     * The object to test.
     *
     * @var ErrorCPD
     */
    protected $errorCPD;

    /**
     * The xml string to test the plugin against.
     *
     * @var string
     */
    protected $testXml = <<<HERE
<?xml version="1.0" encoding="UTF-8"?>
<pmd-cpd version="phpcpd 1.3.1">
  <duplication lines="1" tokens="2">
    <file path="/original/file" line="23"/>
    <file path="/copied/file" line="42"/>
    <codefragment>
        echo 'Some code here';
    </codefragment>
  </duplication>
</pmd-cpd>
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
        $this->errorCPD = new ErrorCPD($issueXML);
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
                '/original/file',
                array(
                    new Issue(
                        '/original/file',
                        23,
                        24,
                        'Duplication',
                        "Copy paste from:\n/copied/file (42)\n (0)",
                        'notice'
                    )
                )
            ),
            new File(
                '/copied/file',
                array(
                    new Issue(
                        '/copied/file',
                        42,
                        43,
                        'Duplication',
                        "Copy paste from:\n/original/file (23)\n (0)",
                        'notice'
                    )
                )
            )
        );
        $actual = $this->errorCPD->getFileList();
        $this->assertEquals($expected, $actual);
    }
}
