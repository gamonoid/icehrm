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


namespace PHPCodeBrowser\Tests\View;

use PHPCodeBrowser\File;
use PHPCodeBrowser\Issue;
use PHPCodeBrowser\Tests\AbstractTestCase;
use PHPCodeBrowser\View\ViewReview;

/**
 * ViewReviewTest
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
class ViewReviewTest extends AbstractTestCase
{
    /**
     * The ViewReview object to test.
     *
     * @var ViewReview
     */
    protected $viewReview;

    /**
     * IOHelper mock to simulate filesystem interaction.
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioMock;

    /**
     * (non-PHPDoc)
     * @see tests/cbAbstractTests#setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $this->ioMock = $this->createMock('PHPCodeBrowser\Helper\IOHelper');

        $this->viewReview = new ViewReview(
            PHPCB_ROOT_DIR . '/../templates/',
            PHPCB_TEST_OUTPUT,
            $this->ioMock
        );
    }

    /**
     * Test the generate method without any issues
     *
     * @return void
     */
    public function testGenerateNoIssues()
    {
        $expectedFile = PHPCB_TEST_OUTPUT . DIRECTORY_SEPARATOR . basename(__FILE__) . '.html';

        $this->ioMock->expects($this->once())
                      ->method('loadFile')
                      ->with($this->equalTo(__FILE__))
                      ->will($this->returnValue(file_get_contents(__FILE__)));
        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with($this->equalTo($expectedFile));

        $this->viewReview->generate(
            array(),
            __FILE__,
            dirname(__FILE__) . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Test the generate method with an issue
     *
     * @return void
     */
    public function testGenerate()
    {
        $issueList = array(
            new Issue(
                __FILE__,
                80,
                85,
                'finder',
                'description',
                'severe'
            )
        );

        $expectedFile = PHPCB_TEST_OUTPUT . DIRECTORY_SEPARATOR . basename(__FILE__) . '.html';
        $this->ioMock->expects($this->once())
                      ->method('loadFile')
                      ->with($this->equalTo(__FILE__))
                      ->will($this->returnValue(file_get_contents(__FILE__)));
        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with($this->equalTo($expectedFile));

        $this->viewReview->generate(
            $issueList,
            __FILE__,
            dirname(__FILE__) . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Test the generate method with multiple errors on one line.
     *
     * @return void
     */
    public function testGenerateMultiple()
    {
        $issueList = array(
            new Issue(__FILE__, 80, 80, 'finder', 'description', 'severe'),
            new Issue(__FILE__, 80, 80, 'other finder', 'other description', 'more severe')
        );

        $expectedFile = PHPCB_TEST_OUTPUT . DIRECTORY_SEPARATOR . basename(__FILE__) . '.html';
        $this->ioMock->expects($this->once())
                      ->method('loadFile')
                      ->with($this->equalTo(__FILE__))
                      ->will($this->returnValue(file_get_contents(__FILE__)));
        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with($this->equalTo($expectedFile));

        $this->viewReview->generate(
            $issueList,
            __FILE__,
            dirname(__FILE__) . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Try to test highlighting with Text_Highlighter
     *
     * @return void
     */
    public function testGenerateWithTextHighlighter()
    {
        if (!class_exists('Text_Highlighter')) {
            $this->markTestIncomplete();
        }

        $html = <<< EOT
<html>
    <head>
        <title>Title</title>
    </head>
    <body>
        <p>Body</p>
    </body>
</html>
EOT;
        $prefix = '/dir/';
        $fileName = $prefix . 'file.html';

        $expectedFile = PHPCB_TEST_OUTPUT . '/file.html.html';
        $this->ioMock->expects($this->once())
                      ->method('loadFile')
                      ->with($this->equalTo($fileName))
                      ->will($this->returnValue($html));
        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with($this->equalTo($expectedFile));

        $issues = array(
            new Issue($fileName, 5, 5, 'finder', 'description', 'severity')
        );

        $this->viewReview->generate($issues, $fileName, $prefix);
    }

    /**
     * Test highlighting of unknown code files.
     *
     * @return void
     */
    public function testGenerateUnknownType()
    {
        $expectedFile = PHPCB_TEST_OUTPUT . DIRECTORY_SEPARATOR . basename(self::$xmlBasic) . '.html';

        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with($this->equalTo($expectedFile));

        $issueList = array(
            new Issue(self::$xmlBasic, 5, 5, 'finder', 'description', 'severity')
        );

        $this->viewReview->generate(
            $issueList,
            self::$xmlBasic,
            dirname(self::$xmlBasic) . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Test if the resource folders are copied.
     *
     * @return void
     */
    public function testCopyResourceFolders()
    {
        $this->ioMock->expects($this->exactly(3))
                      ->method('copyDirectory')
                      ->with(
                          $this->matchesRegularExpression(
                              '|^' . realpath(dirname(__FILE__) . '/../../../templates/') . '|'
                          )
                      );
        $this->viewReview->copyResourceFolders();
    }

    /**
     * Test the generateIndex function
     *
     * @return void
     */
    public function testGenerateIndex()
    {
        $files = array(
            "s/A/somefile.php" => new File("s/A/somefile.php"),
            "s/file.php" => new File("s/file.php"),
            "s/B/anotherfile.php" => new File("s/B/anotherfile.php")
        );

        $this->ioMock->expects($this->once())
                      ->method('createFile')
                      ->with(
                          $this->logicalAnd(
                              $this->stringEndsWith('index.html')
                          )
                      );
        $this->viewReview->generateIndex($files);
    }
}
