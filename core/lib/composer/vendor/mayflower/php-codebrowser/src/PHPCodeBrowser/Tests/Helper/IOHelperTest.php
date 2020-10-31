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


namespace PHPCodeBrowser\Tests\Helper;

use PHPCodeBrowser\Helper\IOHelper;
use PHPCodeBrowser\Tests\AbstractTestCase;

/**
 * IOHelperTest
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
class IOHelperTest extends AbstractTestCase
{
    /**
     * The IOHelper object under test.
     *
     * @var IOHelper
     */
    protected $ioHelper;

    /**
     * (non-PHPDoc)
     * @see AbstractTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $this->ioHelper = new IOHelper();
    }

    /**
     * Test createFile function without creating a path
     *
     * @return void
     */
    public function testFileCreation()
    {
        $filename = PHPCB_TEST_OUTPUT . '/tmpfile';
        $content  = 'Lorem ipsum';

        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->ioHelper->createFile($filename, $content);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($content, file_get_contents($filename));

        unlink($filename);
    }

    /**
     * Test createFile function with creating a path
     *
     * @return void
     */
    public function testCreationOfFileWithPath()
    {
        $dirName = PHPCB_TEST_OUTPUT . '/tmpdir';
        $filename = $dirName . '/tmpfile';
        $content  = 'Lorem ipsum';

        if (file_exists($filename)) {
            unlink($filename);
            rmdir($dirName);
        } elseif (file_exists($dirName)) {
            rmdir($dirName);
        }

        $this->ioHelper->createFile($filename, $content);
        $this->assertTrue(file_exists($dirName));
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($content, file_get_contents($filename));

        unlink($filename);
        rmdir($dirName);
    }

    /**
     * Test deleteFile function
     *
     * @return void
     */
    public function testFileDeletion()
    {
        $filename = PHPCB_TEST_OUTPUT . '/tmpfile';

        if (!file_exists($filename)) {
            file_put_contents($filename, 'Lorem ipsum');
        }

        $this->ioHelper->deleteFile($filename);
        $this->assertFalse(file_exists($filename));
    }

    /**
     * Test deleteDirectory function
     *
     * @return void
     */
    public function testDirectoryDeletion()
    {
        $dir = PHPCB_TEST_OUTPUT . '/dir';
        $file = $dir . '/file';
        $subDir = $dir . '/subDir';

        mkdir($dir);
        mkdir($subDir);
        touch($file);

        $this->ioHelper->deleteDirectory($dir);
        $this->assertFileNotExists($dir);
    }

    /**
     * Test copyFile function
     *
     * @return void
     */
    public function testCopyFile()
    {
        $srcFile = PHPCB_TEST_OUTPUT . '/tmpfile';
        $dstDir = PHPCB_TEST_OUTPUT . '/tmpdir';
        $dstFile = $dstDir . '/tmpfile';
        $content = 'Lorem ipsum';

        if (file_exists($srcFile)) {
            unlink($srcFile);
        }
        if (file_exists($dstFile)) {
            rmdir($dstFile);
        }

        file_put_contents($srcFile, $content);

        $this->ioHelper->copyFile($srcFile, $dstDir);
        $this->assertTrue(file_exists($srcFile));
        $this->assertTrue(file_exists($dstDir));
        $this->assertTrue(file_exists($dstFile));
        $this->assertEquals($content, file_get_contents($dstFile));
        $this->assertEquals($content, file_get_contents($srcFile));

        unlink($dstFile);
        rmdir($dstDir);
        unlink($srcFile);
    }

    /**
     * Test loadFile function for non-existent file.
     *
     * @return void
     */
    public function testLoadFileWithNonexistentFile()
    {
        $sourceFile = PHPCB_TEST_OUTPUT . '/doesNotExist';
        if (file_exists($sourceFile)) {
            unlink(PHPCB_TEST_OUTPUT . '/doesNotExist');
        }
        try {
            $this->ioHelper->loadFile($sourceFile);
            $this->fail();
        } catch (\Exception $e) {
            // expected
        }
    }


    /**
     * Test copyFile function for non-existent source file
     *
     * @return void
     */
    public function testCopyFileNonExisting()
    {
        $file = PHPCB_TEST_OUTPUT . '/tmpfile';
        $dstDir = PHPCB_TEST_OUTPUT . '/tmpdir';

        if (file_exists($file)) {
            unlink($file);
        }

        try {
            $this->ioHelper->copyFile($file, $dstDir);
        } catch (\Exception $e) {
            return;
        }
        $this->fail('Expected exception was not thrown.');
    }

    /**
     * Test getCommonPathPrefix with empty file list.
     *
     * @return void
     */
    public function testGetCommonPathPrefixForNoFiles()
    {
        $this->assertEquals(
            '/',
            $this->ioHelper->getCommonPathPrefix(array())
        );
    }
}
