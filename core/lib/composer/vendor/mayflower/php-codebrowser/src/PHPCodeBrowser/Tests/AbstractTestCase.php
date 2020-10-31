<?php
/**
 * Test case
 *
 * Copyright (c) 2007-2009, Mayflower GmbH
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
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 * @copyright  2007-2009 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since  0.1.0
 */

namespace PHPCodeBrowser\Tests;


/**
 * AbstractTests
 *
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage PHPUnit
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 * @copyright  2007-2009 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since  0.1.0
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Merged cruisecontrol XML error file
     *
     * @var string
     */
    protected static $ccXMLFile;

    /**
     * PHP_CodeBrowser error file
     *
     * @var string
     */
    protected static $xmlFile;

    /**
     * Basic XML file with valid headers
     *
     * @var string
     */
    protected static $xmlBasic;

    /**
     * Path information for a dummy TXT file
     *
     * @var string
     */
    protected static $testFile;

    /**
     * Path information for a dummy XML file
     *
     * @var string
     */
    protected static $testXML;

    /**
     * File of serialized error list
     *
     * @var string
     */
    protected static $serializedErrors;

    /**
     * Path information for generated XML test file
     *
     * @var string
     */
    protected static $generatedXMLTest;

    /**
     * Global setup method for all test cases. Basic variables are initialized.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if (!defined('PHPCB_SOURCE_DIR')) {
            define('PHPCB_SOURCE_DIR', realpath(dirname(__FILE__) . '/../'));
        }

        if (!defined('PHPCB_TEST_DIR')) {
            define(
                'PHPCB_TEST_DIR',
                realpath(PHPCB_SOURCE_DIR) . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'testData'
            );
        }
        if (!defined('PHPCB_TEST_LOGS')) {
            define('PHPCB_TEST_LOGS', PHPCB_TEST_DIR . '/logs');
        }
        if (!defined('PHPCB_TEST_OUTPUT')) {
            define('PHPCB_TEST_OUTPUT', PHPCB_TEST_DIR . DIRECTORY_SEPARATOR . 'output');
        }

        self::$xmlBasic = PHPCB_TEST_LOGS . '/basic.xml';

        if (is_dir(PHPCB_TEST_OUTPUT)) {
            $this->cleanUp(PHPCB_TEST_OUTPUT);
            rmdir(PHPCB_TEST_OUTPUT);
        }

        mkdir(PHPCB_TEST_OUTPUT);
    }

    /**
     * Global tear down method for all test cases.
     * Cleaning up generated data and output.
     *
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->cleanUp(PHPCB_TEST_OUTPUT);
        rmdir(PHPCB_TEST_OUTPUT);
    }

    /**
     * Load the cb error list
     *
     * @return array List of cb errors
     */
    protected function getSerializedErrors()
    {
        return unserialize(file_get_contents(self::$serializedErrors));
    }

    /**
     * Cleanup the test directory output folder
     *
     * @param string $dir The directory to clean up
     *
     * @return void
     */
    protected function cleanUp($dir)
    {
        $iterator = new \DirectoryIterator($dir);
        while ($iterator->valid()) {

            // delete file
            if ($iterator->isFile()) {
                unlink($dir . '/' . $iterator->current());
            }

            // delete folder recursive
            if (! $iterator->isDot() && $iterator->isDir()) {
                $this->cleanUp($dir . '/' . $iterator->current());
                rmdir($dir . '/' . $iterator->current());
            }
            $iterator->next();
        }
        unset($iterator);
    }
}
