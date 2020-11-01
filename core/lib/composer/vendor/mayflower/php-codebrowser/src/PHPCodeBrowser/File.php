<?php
/**
 * File
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
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since 0.2.0
 */

namespace PHPCodeBrowser;

use PHPCodeBrowser\Helper\IOHelper;

/**
 * File
 *
 * An object of this class represents a single source file
 * with it's issues, if any.
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/mayflowergmbh
 * @since     Class available since 0.2.0
 */
class File
{
    /**
     * Filename.
     *
     * @var string
     */
    private $name;

    /**
     * Issues associated with this file.
     *
     * @var Issue[]
     */
    private $issues;

    /**
     * Default constructor.
     *
     * @param string  $name The name of the file.
     * @param Issue[] $issues
     */
    public function __construct($name, array $issues = array())
    {
        if (DIRECTORY_SEPARATOR !== '/')
        {
            $name = str_replace('/', DIRECTORY_SEPARATOR, $name);
        }

        $this->name = $name;
        $this->issues = $issues;
    }

    /**
     * Add an issue for this file.
     *
     * @param Issue $issue The issue to add.
     * @throws \InvalidArgumentException
     */
    public function addIssue(Issue $issue)
    {
        if ($issue->fileName !== $this->name) {
            throw new \InvalidArgumentException(
                'Tried to add issue to wrong file.'
            );
        }
        $this->issues[] = $issue;
    }

    /**
     * Gets an array containing the issues for this file.
     *
     * @return Issue[] The issues.
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Returns the absolute name of this file.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Returns the basename of this file.
     *
     * @return string
     */
    public function basename()
    {
        return basename($this->name);
    }

    /**
     * Returns the dirName of this file.
     *
     * @return string
     */
    public function dirName()
    {
        return dirname($this->name);
    }

    /**
     * Returns the number of issues this file has.
     *
     * @return Integer
     */
    public function getIssueCount()
    {
        return count($this->issues);
    }

    /**
     * Returns the number of errors this file has.
     *
     * @return Integer
     */
    public function getErrorCount()
    {
        $count = 0;
        foreach ($this->issues as $issue) {
            if (strcasecmp($issue->severity, 'error') === 0) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * Returns the number of issues this file has that are not errors.
     *
     * @return Integer
     */
    public function getWarningCount()
    {
        return $this->getIssueCount() - $this->getErrorCount();
    }

    /**
     * Merges the issues from two file objects representing the same file.
     *
     * @param File $file The file to merge with.
     * @throws \InvalidArgumentException
     */
    public function mergeWith(File $file)
    {
        if ($this->name !== $file->name) {
            throw new \InvalidArgumentException(
                'Tried to merge different files'
            );
        }
        $this->issues = array_merge($this->issues, $file->issues);
    }

    /**
     * Sorts an array of Files. Key value association will be preserved.
     *
     * @param File[] $files The files to sort.
     */
    public static function sort(array &$files)
    {
        uasort($files, 'PHPCodeBrowser\File::internalSort');
    }

    /**
     * Sorting function used in File::sort()
     */
    protected static function internalSort(File $first, File $second)
    {
        $first = $first->name();
        $second = $second->name();

        $prefix = IOHelper::getCommonPathPrefix(array($first, $second));
        $prefixLength = strlen($prefix);

        $first = substr($first, $prefixLength);
        $second = substr($second, $prefixLength);

        $firstIsInSubDir = (substr_count($first, DIRECTORY_SEPARATOR) !== 0);
        $secondIsInSubDir = (substr_count($second, DIRECTORY_SEPARATOR) !== 0);

        if ($firstIsInSubDir) {
            if ($secondIsInSubDir) {
                // both are subdirectories
                return strcmp($first, $second);
            } else {
                // a lies in a subDir of the dir in which b lies,
                // so b comes later.
                return -1;
            }
        } else {
            if ($secondIsInSubDir) {
                // b lies in a subDir of the dir in which a lies,
                // so a comes later.
                return 1;
            } else {
                // both are files
                return strcmp($first, $second);
            }
        }
    }
}
