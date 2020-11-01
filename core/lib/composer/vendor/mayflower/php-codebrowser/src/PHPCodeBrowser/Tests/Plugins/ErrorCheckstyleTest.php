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
use PHPCodeBrowser\Plugins\ErrorCheckstyle;
use PHPCodeBrowser\Tests\AbstractTestCase;

/**
 * ErrorCheckstyleTest
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
class ErrorCheckstyleTest extends AbstractTestCase
{
    /**
     * The object to test.
     *
     * @var ErrorCheckstyle
     */
    protected $errorCheckstyle;

    /**
     * The xml string to test the plugin against.
     *
     * @var string
     */
    protected $testXml = <<<HERE
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle version="1.2.2">
 <file name="/some/file">
  <error line="117"
         column="32"
         severity="error"
         message="Message 1"
         source="Source3"/>
  <error line="121"
         column="88"
         severity="warning"
         message="Message 2"
         source="Source2"/>
 </file>
 <file name="/other/file">
  <error line="48"
         column="67"
         severity="error"
         message="Message 3"
         source="Source1"/>
 </file>
 <file name="/no/violations">
 </file>
</checkstyle>
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
        $xml->validateOnParse = true;
        $xml->loadXML($this->testXml);
        $issueXML->addXMLFile($xml);
        $this->errorCheckstyle = new ErrorCheckstyle($issueXML);
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
                        117,
                        117,
                        'Checkstyle',
                        'Message 1',
                        'error'
                    ),
                    new Issue(
                        '/some/file',
                        121,
                        121,
                        'Checkstyle',
                        'Message 2',
                        'warning'
                    )
                )
            ),
            new File(
                '/other/file',
                array(
                    new Issue(
                        '/other/file',
                        48,
                        48,
                        'Checkstyle',
                        'Message 3',
                        'error'
                    )
                )
            ),
            new File(
                '/no/violations',
                array()
            )
        );
        $actual = $this->errorCheckstyle->getFileList();
        $this->assertEquals($expected, $actual);
    }
}
