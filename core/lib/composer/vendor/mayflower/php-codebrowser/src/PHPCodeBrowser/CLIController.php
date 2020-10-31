<?php
/**
 * Cli controller
 *
 * PHP Version 5.3.2
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
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since  0.1.0
 */

namespace PHPCodeBrowser;

use File_Iterator_Factory;
use Monolog\Logger;
use PHPCodeBrowser\Helper\IOHelper;
use PHPCodeBrowser\View\ViewReview;

if (!defined('PHPCB_ROOT_DIR')) {
    define('PHPCB_ROOT_DIR', dirname(__FILE__) . '/../');
}
if (!defined('PHPCB_TEMPLATE_DIR')) {
    define('PHPCB_TEMPLATE_DIR', dirname(__FILE__) . '/../../templates');
}

/**
 * CLIController
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Michel Hartmann <michel.hartmann@mayflower.de>
 * @author    Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/
 * @since     Class available since  0.1.0
 */
class CLIController
{
    /**
     * Path to the Cruise Control input xml file
     *
     * @var string
     */
    private $logDir;

    /**
     * Path to the code browser html output folder
     *
     * @var string
     */
    private $htmlOutputDir;

    /**
     * Path to the project source code files
     *
     * @var string
     */
    private $projectSource;

    /**
     * array of PCREs. Matching files will not appear in the output.
     *
     * @var array
     */
    private $excludeExpressions;

    /**
     * array of glob patterns. Matching files will not appear in the output.
     *
     * @var array
     */
    private $excludePatterns;

    /**
     * The error plugin classes
     *
     * @var array
     */
    private $registeredPlugins;

    /**
     * The IOHelper used for filesystem interaction.
     *
     * @var IOHelper
     */
    private $ioHelper;

    /**
     * Pear Log object where debug output should go to.
     *
     * @var Logger
     */
    private $debugLog;

    /**
     * Plugin-specific options. Formatted like
     *  array(
     *      'ErrorCRAP' => array(
     *          'threshold' => 2
     *      )
     *  )
     *
     * @var array
     */
    private $pluginOptions = array();

    /**
     * File extensions that we take as php files.
     *
     * @var array
     */
    private $phpSuffixes;

    /**
     * We want to exclude files with no issues
     *
     * @var boolean
     */
    private $excludeOK;

    /**
     * The constructor
     *
     * Standard setters are initialized
     *
     * @param string   $logPath            The (path-to) xml log files. Can be null.
     * @param array    $projectSource      The project sources. Can be null.
     * @param string   $htmlOutputDir      The html output dir, where new files will be created
     * @param array    $excludeExpressions A list of PCREs. Files matching will not appear in the output.
     * @param array    $excludePatterns    A list of glob patterns. Files matching will not appear in the output.
     * @param array    $pluginOptions      array of arrays with plugin-specific options
     * @param IOHelper $ioHelper           The IOHelper object to be used for filesystem interaction.
     * @param Logger   $debugLog
     * @param array    $phpSuffixes
     * @param bool     $excludeOK
     */
    public function __construct(
        $logPath,
        array $projectSource,
        $htmlOutputDir,
        array $excludeExpressions,
        array $excludePatterns,
        array $pluginOptions,
        $ioHelper,
        Logger $debugLog,
        array $phpSuffixes,
        $excludeOK = false
    ) {
        $this->logDir             = $logPath;
        $this->projectSource      = $projectSource;
        $this->htmlOutputDir      = $htmlOutputDir;
        $this->excludeExpressions = $excludeExpressions;
        $this->excludePatterns    = $excludePatterns;
        foreach ($pluginOptions as $plugin => $options) {
            $this->pluginOptions["Error$plugin"] = $options;
        }
        $this->ioHelper           = $ioHelper;
        $this->debugLog           = $debugLog;
        $this->registeredPlugins  = array();
        $this->phpSuffixes        = $phpSuffixes;
        $this->excludeOK          = $excludeOK;
    }

    /**
     * Setter/adder method for the used plugin classes.
     * For each plugin to use, add it to this array
     *
     * @param mixed $classNames Definition of plugin classes
     *
     * @return void
     */
    public function addErrorPlugins($classNames)
    {
        foreach ((array) $classNames as $className) {
            $this->registeredPlugins[] = $className;
        }
    }

    /**
     * Main execute function for PHP_CodeBrowser.
     *
     * Following steps are resolved:
     * 1. Clean-up output directory
     * 2. Merge xml log files
     * 3. Generate XML file via error list from plugins
     * 4. Save the ErrorList as XML file
     * 5. Generate HTML output from XML
     * 6. Copy resources (css, js, images) from template directory to output
     *
     * @return void
     */
    public function run()
    {
        // clear and create output directory
        if (is_dir($this->htmlOutputDir)) {
            $this->ioHelper->deleteDirectory($this->htmlOutputDir);
        } elseif (is_file($this->htmlOutputDir)) {
            $this->ioHelper->deleteFile($this->htmlOutputDir);
        }
        $this->ioHelper->createDirectory($this->htmlOutputDir);

        // init needed classes
        $viewReview  = new ViewReview(
            PHPCB_TEMPLATE_DIR,
            $this->htmlOutputDir,
            $this->ioHelper,
            $this->phpSuffixes
        );

        $sourceHandler = new SourceHandler($this->debugLog);

        if (isset($this->logDir)) {
            $issueXml    = new IssueXml();

            // merge xml files
            $issueXml->addDirectory($this->logDir);

            // conversion of XML file cc to cb format
            foreach ($this->registeredPlugins as $className) {
                if (array_key_exists($className, $this->pluginOptions)) {
                    $plugin = new $className(
                        $issueXml,
                        $this->pluginOptions[$className]
                    );
                } else {
                    $plugin = new $className($issueXml);
                }
                $sourceHandler->addPlugin($plugin);
            }
        }

        if (isset($this->projectSource)) {
            foreach ($this->projectSource as $source) {
                if (is_dir($source)) {
                    $factory = new File_Iterator_Factory;

                    $suffixes = array_merge(
                        $this->phpSuffixes,
                        array('php','js','css', 'html')
                    );

                    $sourceHandler->addSourceFiles(
                        $factory->getFileIterator(
                            $source,
                            $suffixes
                        )
                    );
                } else {
                    $sourceHandler->addSourceFile($source);
                }
            }
        }

        array_walk(
            $this->excludeExpressions,
            array($sourceHandler, 'excludeMatchingPCRE')
        );
        array_walk(
            $this->excludePatterns,
            array($sourceHandler, 'excludeMatchingPattern')
        );

        $files = $sourceHandler->getFiles();

        if (!$files) {
            $viewReview->copyNoErrorsIndex();
        } else {
            // Get the path prefix all files have in common
            $commonPathPrefix = $sourceHandler->getCommonPathPrefix();

            foreach ($files as $file) {
                $viewReview->generate(
                    $file->getIssues(),
                    $file->name(),
                    $commonPathPrefix,
                    $this->excludeOK
                );
            }

            // Copy needed resources (eg js libraries) to output directory
            $viewReview->copyResourceFolders();
            $viewReview->generateIndex($files, $this->excludeOK);
        }
    }
}
