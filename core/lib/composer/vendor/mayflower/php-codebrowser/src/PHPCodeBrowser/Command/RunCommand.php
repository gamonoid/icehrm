<?php
/**
 * RunCommand
 *
 * PHP Version 5.3.0
 *
 * Copyright (c) 2007-2014, Mayflower GmbH
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
 * @author    Robin Gloster <robin.gloster@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since 1.1
 */

namespace PHPCodeBrowser\Command;


use Exception;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPCodeBrowser\CLIController;
use PHPCodeBrowser\Helper\IOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunCommand
 * @package PHPCodeBrowser\Command
 */
class RunCommand extends Command
{
    protected function configure()
    {
        $plugins = array_map(
            function ($class) {
                return '"' . substr($class, strlen('Error')) . '"';
            },
            $this->getAvailablePlugins()
        );


        $this->setName('phpcb')
            ->setHelp(
                'A Code browser for PHP files with syntax highlighting and colored error-sections '
                . 'found by quality assurance tools like PHPUnit, PHPMD or PHP_CodeSniffer.'
            )->addOption(
                'log',
                'l',
                InputOption::VALUE_REQUIRED,
                'The path to the xml log files, e.g. generated from PHPUnit. Either this or --source must be given'
            )->addOption(
                'extensions',
                'S',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'PHP file extensions to include. Can be given multiple times'
            )->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Path to the output folder where generated files should be stored'
            )->addOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Path to the project source code. Can either be a directory or a single file. Parse '
                . 'complete source directory if set, else only files found in logs. Either this or'
                . ' --log must be given. Can be given multiple times'
            )->addOption(
                'ignore',
                'i',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Files or directories that will be ignored during the parsing process. Can be given multiple times'
            )->addOption(
                'exclude',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes all files matching the given glob pattern. This is done after pulling the '
                . 'files in the source dir in if one is given. Can be given multiple times. Note'
                . ' that the match is run against absolute file names'
            )->addOption(
                'excludePCRE',
                'E',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Works like -e but takes PCRE instead of glob patterns'
            )->addOption(
                'debugExcludes',
                null,
                InputOption::VALUE_NONE,
                'Print which files are excluded by which expressions and patterns'
            )->addOption(
                'excludeOK',
                null,
                InputOption::VALUE_NONE,
                'Exclude files with no issues from the report'
            )->addOption(
                'disablePlugin',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Disable single Plugins. Can be one of ' . implode(', ', $plugins)
            )->addOption(
                'crapThreshold',
                null,
                InputOption::VALUE_REQUIRED,
                'The minimum value for CRAP errors to be recognized. Defaults to 0. Regardless '
                . 'of this setting, values below 30 will be considered notices, those above warnings'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkErrors($input);

        $extensions = $this->handleBackwardCompatibility($input->getOption('extensions'));
        $ignore = $this->handleBackwardCompatibility($input->getOption('ignore'));

        $excludePCRE = $input->getOption('excludePCRE');
        $excludePCRE = $this->convertIgnores($ignore, $excludePCRE);

        $logger = new Logger('PHPCodeBrowser');

        if (!$input->getOption('debugExcludes')) {
            $logger->pushHandler(new NullHandler());
        }

        // init new CLIController
        $controller = new CLIController(
            $input->getOption('log'),
            $input->getOption('source'),
            $input->getOption('output'),
            $excludePCRE,
            $input->getOption('exclude'),
            array('CRAP' => array('threshold' => $input->getOption('crapThreshold'))),
            new IOHelper(),
            $logger,
            array_merge($extensions, array('php')),
            (bool) $input->getOption('excludeOK')
        );

        $plugins = $this->getAvailablePlugins();
        $plugins = $this->disablePlugins($input->getOption('disablePlugin'), $plugins);
        $controller->addErrorPlugins($plugins);

        try {
            $controller->run();
        } catch (Exception $e) {
            error_log(
                <<<HERE
                [Error] {$e->getMessage()}

{$e->getTraceAsString()}
HERE
            );
        }

        return 0;
    }


    /**
     * @param InputInterface $input
     * @throws \InvalidArgumentException if errors are found
     */
    protected function checkErrors(InputInterface $input)
    {
        if (!$input->getOption('log')) {
            if (!$input->getOption('source')) {
                throw new \InvalidArgumentException('Missing log or source argument.');
            }
        } elseif (!file_exists($input->getOption('log'))) {
            throw new \InvalidArgumentException('Log directory does not exist.');
        } elseif (!is_dir($input->getOption('log'))) {
            throw new \InvalidArgumentException('Log argument must be a directory, a file was given.');
        }

        if ($input->getOption('source')) {
            foreach ($input->getOption('source') as $s) {
                if (!file_exists($s)) {
                    throw new \InvalidArgumentException("Source '$s' does not exist");
                }
            }
        }

        if (!$input->getOption('output')) {
            throw new \InvalidArgumentException('Missing output argument.');
        } elseif (file_exists($input->getOption('output')) && !is_dir($input->getOption('output'))) {
            throw new \InvalidArgumentException('Output argument must be a directory, a file was given.');
        }
    }

    /**
     * Returns a list of available plugins.
     *
     * Currently hard-coded.
     *
     * @return string[] Class names of error plugins
     */
    protected function getAvailablePlugins()
    {
        return array(
            'PHPCodeBrowser\\Plugins\\ErrorCheckstyle',
            'PHPCodeBrowser\\Plugins\\ErrorPMD',
            'PHPCodeBrowser\\Plugins\\ErrorCPD',
            'PHPCodeBrowser\\Plugins\\ErrorPadawan',
            'PHPCodeBrowser\\Plugins\\ErrorCoverage',
            'PHPCodeBrowser\\Plugins\\ErrorCRAP'
        );
    }

    /**
     * @param array $disabledPlugins
     * @param array $plugins
     *
     * @return array
     */
    protected function disablePlugins(array $disabledPlugins, array $plugins)
    {
        $disabledPlugins = array_map(
            function ($param) {
                return strtolower($param);
            },
            $disabledPlugins
        );

        foreach ($plugins as $pluginKey => $plugin) {
            $name = substr($plugin, strlen('Error'));
            if (in_array(strtolower($name), $disabledPlugins)) {
                // Remove it from the plugins list
                unset($plugins[$pluginKey]);
            }
        }

        return $plugins;
    }

    /**
     * Convert the --ignore arguments to patterns
     *
     * @param array $ignored
     * @param $excludePCRE
     * @return array
     */
    protected function convertIgnores(array $ignored, $excludePCRE)
    {
        $dirSep = preg_quote(DIRECTORY_SEPARATOR, '/');
        foreach ($ignored as $ignore) {
            $ig = realpath($ignore);
            if (!$ig) {
                error_log("[Warning] $ignore does not exists");
            } else {
                $ig = preg_quote($ig, '/');
                $excludePCRE[] = "/^$ig($dirSep|$)/";
            }
        }
        return $excludePCRE;
    }

    /**
     * This converts comma-separated options into an array
     *
     * @param array $option
     * @return array
     */
    private function handleBackwardCompatibility(array $option)
    {
        if (count($option) == 1 && strpos($option[0], ',') !== false) {
            $option = explode(',', $option[0]);
            error_log('Usage of comma-separated options is deprecated, specify them one-by-one.', E_DEPRECATED);
        }

        return $option;
    }
}
