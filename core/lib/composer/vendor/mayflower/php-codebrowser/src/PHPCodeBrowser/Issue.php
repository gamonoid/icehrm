<?php
/**
 * Plugin Abstract
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
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/
 * @since     File available since  0.1.2
 */

namespace PHPCodeBrowser;

/**
 * Issue
 *
 * Object Model for issues.
 * This object is used for working with common issues types.
 *
 * @category  PHP_CodeBrowser
 * @package   PHP_CodeBrowser
 * @author    Elger Thiele <elger.thiele@mayflower.de>
 * @author    Michel Hartmann <michel.hartmann@mayflower.de>
 * @copyright 2007-2010 Mayflower GmbH
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/mayflowergmbh
 * @since     Class available since  0.1.2
 */
class Issue
{
    /**
     * Source file name.
     *
     * @var string
     */
    public $fileName;

    /**
     * Starting Line of the Issue.
     *
     * @var string
     */
    public $lineStart;

    /**
     * Ending Line of the Issue.
     *
     * @var string
     */
    public $lineEnd;

    /**
     * Name of the Plugin that found the Issue.
     * It is also used for CSS class definitions.
     *
     * @var string
     */
    public $foundBy;

    /**
     * Issue Description text.
     *
     * @var string
     */
    public $description;

    /**
     * Severity of the issue.
     *
     * @var string
     */
    public $severity;

    /**
     * Default constructor
     *
     * @param string  $fileName    The source file name the issue was found in.
     * @param Integer $lineStart   The starting line of the issue.
     * @param Integer $lineEnd     The ending line of registered issue.
     * @param string  $foundBy     The plugin name definition.
     * @param string  $description The description of the issue.
     * @param string  $severity
     */
    public function __construct(
        $fileName,
        $lineStart,
        $lineEnd,
        $foundBy,
        $description,
        $severity
    ) {
        $this->fileName    = $fileName;
        $this->lineStart   = $lineStart;
        $this->lineEnd     = $lineEnd;
        $this->foundBy     = $foundBy;
        $this->description = $description;
        $this->severity    = $severity;
    }
}
