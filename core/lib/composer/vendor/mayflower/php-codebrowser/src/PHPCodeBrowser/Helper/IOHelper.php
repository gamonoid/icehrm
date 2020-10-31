<?php
/**
 * Input and output helper
 *
 * PHP Version 5.2.6
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
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @copyright 2007-2009 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since  0.1.0
 */


namespace PHPCodeBrowser\Helper;

use DirectoryIterator;

/**
 * IOHelper
 *
 * Input output helper class provides several methods for writing and
 * reading files or directories.
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Christopher Weckerle <christopher.weckerle@mayflower.de>
 * @copyright 2007-2009 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/
 * @since     Class available since  0.1.0
 */
class IOHelper
{

    /**
     * Creates a file with given name and content.
     * If directories to file do not exists they will be created.
     *
     * @param string $fileName    The filename
     * @param string $fileContent The content of the file
     *
     * @return void
     */
    public function createFile($fileName, $fileContent)
    {
        $realName = basename($fileName);
        $path     = substr($fileName, 0, - 1 * (strlen($realName)));

        if (!empty($path)) {
            self::createDirectory($path);
        }
        file_put_contents(realpath($path) . '/' . $realName, $fileContent);
    }

    /**
     * Delete a file. The filename could inherit a absolute or relative
     * path-to-file,
     * e.g. foo/bar/myFile.php
     *
     * @param string $fileName The (path-to) filename
     *
     * @return void
     */
    public function deleteFile($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * Copy a file from a source to target dir. The source could inherit an
     * absolute or relative path-to-file.
     *
     * @param string $fileSource   The source file
     * @param string $sourceFolder The target folder
     *
     * @return void
     * @throws \Exception
     */
    public function copyFile($fileSource, $sourceFolder)
    {
        if (!file_exists($fileSource)) {
            throw new \Exception('File ' . $fileSource . ' does not exists!');
        }

        $fileName = basename($fileSource);
        self::createFile(
            $sourceFolder . '/' . $fileName,
            self::loadFile($fileSource)
        );
    }

    /**
     * Return the content of a given file.
     *
     * @param string $fileName The file the content should be read in
     *
     * @return string
     * @throws \Exception
     */
    public function loadFile($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \Exception('File ' . $fileName . ' does not exist!');
        }
        return trim(file_get_contents($fileName));
    }

    /**
     * Create a directory and its inherit path to directory if not present,
     * e.g. path/that/does/not/exist/myFolder/
     *
     * @param string $target The target folder to create
     *
     * @return void
     */
    public function createDirectory($target)
    {
        $target = rtrim($target, DIRECTORY_SEPARATOR);

        if (!is_dir($target))
        {
            mkdir($target, 0777, true);
        }
    }

    /**
     * Delete a directory within all its items.
     * Note that the given directory $source will be deleted as well.
     *
     * @param string $source The directory to delete.
     *
     * @throws \Exception
     * @return void
     */
    public function deleteDirectory($source)
    {
        $iterator = new DirectoryIterator($source);
        while ($iterator->valid()) {

            $src = realpath($source . '/' . $iterator->current());

            // delete file
            if ($iterator->isFile()) {
                self::deleteFile($src);
            }

            // delete folder recursive
            if (! $iterator->isDot() && $iterator->isDir()) {
                self::deleteDirectory($src);
            }

            $iterator->next();
        }
        unset($iterator);

        // delete the source root folder as well
        if (!rmdir($source)) {
            throw new \Exception('Could not delete directory ' . $source);
        }
    }

    /**
     * Copy a directory within all its items.
     *
     * @param string $source  The source directory
     * @param string $target  The target to create
     * @param array  $exclude List of files / folders that should not be copyed
     *
     * @return void
     */
    public function copyDirectory($source, $target, $exclude = array())
    {
        // first check for target itself
        self::createDirectory($target);
        $iterator = new DirectoryIterator($source);
        while ($iterator->valid()) {

            $item = $iterator->current();

            // create new file
            if ($iterator->isFile()) {
                self::copyFile($source . '/' . $item, $target);
            }

            // create folder recursive
            if (!$iterator->isDot()
                && $iterator->isDir()
                && !in_array($item, $exclude)
            ) {
                self::copyDirectory(
                    $source . '/' . $item,
                    $target . '/' . $item
                );
            }
            $iterator->next();
        }
    }

    /**
     * Get the prefix all paths in an array of paths have in common.
     * @param array $fileNames
     * @return string
     */
    public static function getCommonPathPrefix(array $fileNames)
    {
        if (empty($fileNames)) {
            return '/';
        }
        $prefix = dirname(array_shift($fileNames));
        foreach ($fileNames as $filename) {
            $prefix = self::getCurrentCommonPathPrefix($prefix, $filename);
        }

        if (substr($prefix, -1, 1) !== DIRECTORY_SEPARATOR) {
            $prefix .= DIRECTORY_SEPARATOR;
        }
        return $prefix;
    }

    /**
     * Get the part of currentPrefix that currentPrefix and path have in common.
     * @param string $currentPrefix
     * @param string $path
     * @return string
     */
    protected static function getCurrentCommonPathPrefix($currentPrefix, $path)
    {
        if (strpos($path, $currentPrefix . DIRECTORY_SEPARATOR) === 0
                || $currentPrefix == DIRECTORY_SEPARATOR
                || $currentPrefix == ''
                || $currentPrefix == '.'
                || preg_match('/^[A-Z]\:\\\\$/', $currentPrefix) === 1) {
            return $currentPrefix;
        }
        return self::getCurrentCommonPathPrefix(dirname($currentPrefix), $path);
    }
}
