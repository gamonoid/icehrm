<?php
/**
 * Source handler
 *
 * PHP Version 5.3.0
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
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Michel Hartmann <michel.hartmann@mayflower.de>
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since  0.2.0
 */

namespace PHPCodeBrowser;

use Exception;
use Monolog\Logger;
use PHPCodeBrowser\Helper\IOHelper;
use SplFileInfo;

/**
 * SourceHandler
 *
 * This class manages lists of source files and their issues.
 * For providing these lists the prior generated IssueXml is parsed.
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Christopher Weckerle <christopher.weckerle@mayflower.de>
 * @author    Michel Hartmann <michel.hartmann@mayflower.de>
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/
 * @since     Class available since  0.2.0
 */
class SourceHandler
{
    /**
     * Files to be included in the report
     *
     * @var File[]
     */
    protected $files = array();

    /**
     * Pear Log object where debug output should go to.
     *
     * @var Logger
     */
    protected $debugLog;

    /**
     * Default constructor
     *
     * @param Logger $debugLog
     * @param array  $plugins The plugins to get issues from.
     */
    public function __construct (Logger $debugLog, array $plugins = array())
    {
        $this->debugLog = $debugLog;
        array_walk($plugins, array($this, 'addPlugin'));
    }

    /**
     * Add a new plugin to the handler.
     *
     * @param PluginsAbstract $plugin The plugin to add.
     */
    public function addPlugin(PluginsAbstract $plugin)
    {
        foreach ($plugin->getFileList() as $file) {
            if (array_key_exists($file->name(), $this->files)) {
                $this->files[$file->name()]->mergeWith($file);
            } else {
                $this->files[$file->name()] = $file;
            }
        }
    }

    /**
     * Add source files to the list.
     *
     * @param SplFileInfo[]|string[]|\AppendIterator $files The files to add
     */
    public function addSourceFiles($files)
    {
        foreach ($files as $f) {
            $this->addSourceFile($f);
        }
    }

    /**
     * Add a source file.
     *
     * @param string|SplFileInfo $file The file to add
     * @throws \Exception
     */
    public function addSourceFile($file)
    {
        if (is_string($file)) {
            $filename = $file;
            $file     = realpath($file);
        } else {
            $filename = $file->getPathName();
            $file     = $file->getRealPath();
        }

        if (!$file) {
            throw new Exception("$filename is no regular file");
        }

        if (!array_key_exists($file, $this->files)) {
            $this->files[$file] = new File($file);
        }
    }

    /**
     * Retrieves the parent directory all files have in common.
     *
     * @return string
     */
    public function getCommonPathPrefix()
    {
        return IOHelper::getCommonPathPrefix(array_keys($this->files));
    }

    /**
     * Returns a sorted array of the files that should be in the report.
     *
     * @return File[] of File
     */
    public function getFiles()
    {
        File::sort($this->files);
        return $this->files;
    }

    /**
     * Get a unique list of all file names with issues.
     *
     * @return array
     */
    public function getFilesWithIssues()
    {
        return array_keys($this->files);
    }

    /**
     * Remove all files that match the given PCRE.
     *
     * @param string $expr The PCRE specifying which files to remove.
     * @return void.
     */
    public function excludeMatchingPCRE($expr)
    {
        foreach (array_keys($this->files) as $filename) {
            if (preg_match($expr, $filename)) {
                $this->debugLog->debug(
                    "Excluding $filename, it matches PCRE $expr"
                );
                unset($this->files[$filename]);
            }
        }
    }

    /**
     * Remove all files that match the given shell wildcard pattern
     * as accepted by fnmatch().
     *
     * @param string $pattern The pattern.
     * @return void.
     */
    public function excludeMatchingPattern($pattern)
    {
        foreach (array_keys($this->files) as $filename) {
            if (fnmatch($pattern, $filename)) {
                $this->debugLog->debug(
                    "Excluding $filename, it matches pattern $pattern"
                );
                unset($this->files[$filename]);
            }
        }
    }
}
