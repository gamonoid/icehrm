<?php
/**
 * View Abstract
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
 * @author    Jan Mergler <jan.mergler@mayflower.de>
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since  0.1.0
 */

namespace PHPCodeBrowser\View;

use Exception;
use PHPCodeBrowser\File;
use PHPCodeBrowser\Helper\IOHelper;

/**
 * ViewAbstract
 *
 * This class is generating the highlighted and formatted html view for file.
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Jan Mergler <jan.mergler@mayflower.de>
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/
 * @since     Class available since  0.1.0
 */
class ViewAbstract
{
    /**
     * Template directory
     *
     * @var string
     */
    protected $templateDir;

    /**
     * Output directory
     *
     * @var string
     */
    protected $outputDir;

    /**
     * Available resource folders
     *
     * @var array
     */
    protected $resourceFolders = array('css', 'js', 'img');

    /**
     * IOHelper for filesystem interaction.
     *
     * @var IOHelper
     */
    protected $ioHelper;

    /**
     * Default Constructor
     *
     * @param string $templateDir The directory containing the templates.
     * @param string $outputDir The directory where the reviews should be.
     * @param IOHelper $ioHelper The IOHelper object to use for I/O.
     * @throws Exception
     */
    public function __construct($templateDir, $outputDir, IOHelper $ioHelper)
    {
        $this->templateDir = $templateDir;
        if (!$this->templateDir) {
            throw new Exception(
                "Specified template directory '$templateDir' does not exist"
            );
        }

        $this->outputDir = $outputDir;
        if (!$this->outputDir) {
            throw new Exception(
                "Specified output directory '$outputDir' does not exist"
            );
        }
        $this->outputDir .= DIRECTORY_SEPARATOR;

        $this->ioHelper = $ioHelper;
    }

    /**
     * Copy needed resources to output directory
     *
     * @return void
     * @throws Exception
     * @see IOHelper->copyFile
     */
    public function copyResourceFolders()
    {
        foreach ($this->resourceFolders as $folder) {
            $this->ioHelper->copyDirectory(
                $this->templateDir . DIRECTORY_SEPARATOR . $folder,
                $this->outputDir . DIRECTORY_SEPARATOR . $folder
            );
        }
    }

    /**
     * Copy the noErrors file as index.html to indicate that no
     * source files were found
     *
     * @return void
     */
    public function copyNoErrorsIndex()
    {
        $this->ioHelper->createFile(
            $this->outputDir . '/index.html',
            $this->render('noErrors', array())
        );
    }

    /**
     * Creates a javascript-filled index.html
     *
     * @param array $fileList
     * @param bool $excludeOK
     *
     * @return void
     */
    public function generateIndex(array $fileList, $excludeOK = false)
    {
        //we want to exclude files without issues
        if ($excludeOK) {
            $fileList = array_filter($fileList, array('PHPCodeBrowser\\View\\ViewAbstract', 'hasFileAnyIssues'));
        }

        $data['treeList'] = $this->getTreeListHtml($fileList);
        $data['fileList'] = $fileList;

        $this->ioHelper->createFile(
            $this->outputDir . '/index.html',
            $this->render('index', $data)
        );
    }

    /**
     * Has the file any issues?
     *
     * @param File $file
     * @return boolean
     */
    public static function hasFileAnyIssues(File $file)
    {
        $issues = $file->getIssues();
        return !empty($issues);
    }

    /**
     * Convert a list of files to a html fragment for jstree.
     *
     * @param File[] $fileList       The files, format: array('name' => File).
     * @param string $hrefPrefix    The prefix to put before all href= tags.
     *
     * @return string  The html fragment.
     */
    protected function getTreeListHtml(array $fileList, $hrefPrefix = '')
    {
        /*
         * In this method, all directories have a trailing DIRECTORY_SEPARATOR.
         * This is important so that $curDir doesn't become empty if we go
         * up to the root directory ('/' on linux)
         */
        $curDir = IOHelper::getCommonPathPrefix(array_keys($fileList));
        $preLen = strlen($curDir);

        $indentStep = 4;
        $indent     = $indentStep;
        $ret        = '<ul>' . PHP_EOL;
        foreach ($fileList as $name => $file) {
            $dir = dirname($name) . DIRECTORY_SEPARATOR;

            // Go back until the file is somewhere below curDir
            while (strpos($dir, $curDir) !== 0) {
                // chop off one subDir from $curDir
                $curDir = substr(
                    $curDir,
                    0,
                    strrpos($curDir, DIRECTORY_SEPARATOR, -2) + 1
                );
                $ret    .= str_pad(' ', $indent);
                $ret    .= '</ul>' . PHP_EOL;
                $indent -= $indentStep;
                $ret    .= str_pad(' ', $indent);
                $ret    .= '</li>' . PHP_EOL;
            }

            if ($dir !== $curDir) {
                // File is in a subDir of current directory
                // relDir has no leading or trailing slash.
                $relDir  = substr($dir, strlen($curDir), -1);
                $relDirs = explode(DIRECTORY_SEPARATOR, $relDir);

                foreach ($relDirs as $dirName) {
                    $curDir .= $dirName . DIRECTORY_SEPARATOR;
                    // Check how many errors/warnings are in this dir.
                    //TODO: Optimize this. Counts get recalculated for subDirs.
                    $errors   = 0;
                    $warnings = 0;
                    foreach (array_keys($fileList) as $fName) {
                        if (strncmp($fName, $curDir, strlen($curDir)) === 0) {
                            $errors   += $fileList[$fName]->getErrorCount();
                            $warnings += $fileList[$fName]->getWarningCount();
                        }
                    }
                    $count = '';
                    if ($errors != 0 || $warnings != 0) {
                        $count .= '(<span class="errorCount">';
                        $count .= $errors;
                        $count .= '</span>|<span class="warningCount">';
                        $count .= $warnings . '</span>)';
                    }
                    $ret    .= str_pad(' ', $indent);
                    $ret    .= "<li><a class='treeDir'>$dirName $count</a>"
                        . PHP_EOL;
                    $indent += $indentStep;
                    $ret    .= str_pad(' ', $indent);
                    $ret    .= '<ul>' . PHP_EOL;
                }
            }

            $name = str_replace('\\', '/', $name);
            $shortName = substr($name, $preLen);
            $fileName  = basename($name);
            $count = '';
            if ($file->getErrorCount() != 0 || $file->getWarningCount() != 0) {
                $count .= '(<span class="errorCount">';
                $count .= $file->getErrorCount();
                $count .= '</span>|<span class="warningCount">';
                $count .= $file->getWarningCount();
                $count .= '</span>)';
            }

            $ret .= str_pad(' ', $indent);
            $ret .= '<li class="php"><a class="fileLink" href="';
            $ret .= $hrefPrefix . $shortName . '.html">';
            $ret .= "$fileName $count</a></li>" . PHP_EOL;
        }

        while ($indent > $indentStep) {
            $indent -= $indentStep;
            $ret    .= str_pad(' ', $indent);
            $ret    .= '</ul>' . PHP_EOL;
            $indent -= $indentStep;
            $ret    .= str_pad(' ', $indent);
            $ret    .= '</li>' . PHP_EOL;
        }

        $ret .= '</ul>' . PHP_EOL;
        return $ret;
    }

    /**
     * Render a template.
     *
     * Defined template is parsed and filled with data.
     * Rendered content is read from output buffer.
     *
     * @param string $templateName Template file to use for rendering
     * @param array  $data         Given data set to use for rendering
     *
     * @return string              HTML files as string from output buffer
     */
    protected function render($templateName, $data)
    {
        $filePath = $this->templateDir . DIRECTORY_SEPARATOR
                  . $templateName . '.tpl';

        extract($data, EXTR_SKIP);

        ob_start();
        include($filePath);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}
