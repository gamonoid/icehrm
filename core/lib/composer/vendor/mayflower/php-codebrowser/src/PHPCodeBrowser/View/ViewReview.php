<?php
/**
 * View Review
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

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPCodeBrowser\Helper\IOHelper;

/**
 * ViewReview
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
class ViewReview extends ViewAbstract
{
    /**
     * Highlight mapping.
     *
     * @var array
     */
    protected $phpHighlightColorMap;

    /**
     * Suffixes for php files.
     *
     * @var array
     */
    protected $phpSuffixes;

    /**
     * Default constructor
     *
     * Highlighting strings are set.
     *
     * @param string $templateDir The directory containing the templates.
     * @param string $outputDir   The directory where the reviews should be.
     * @param IOHelper $ioHelper  The IOHelper object to use for I/O.
     * @param array $phpSuffixes  The array with extensions of php files.
     */
    public function __construct($templateDir, $outputDir, $ioHelper, $phpSuffixes = array('php'))
    {
        parent::__construct($templateDir, $outputDir, $ioHelper);

        $this->phpHighlightColorMap = array(
            ini_get('highlight.string')  => 'string',
            ini_get('highlight.comment') => 'comment',
            ini_get('highlight.keyword') => 'keyword',
            ini_get('highlight.default') => 'default',
            ini_get('highlight.html')    => 'html',
        );

        $this->phpSuffixes = $phpSuffixes;
    }

    /**
     * Generating the Html code browser view for a given file.
     *
     * Issue list for each file will be marked in source code.
     * Source code is highlighted.
     * Generated Html source code is be saved as Html.
     *
     * @param array $issueList The issue list for given file
     * @param $fileName
     * @param string $commonPathPrefix The prefix path all given files have
     *                                 in common
     * @param bool $excludeOK
     *
     * @return void
     *
     * @see self::_formatIssues
     * @see self::_formatSourceCode
     * @see self::_generateJSCode
     */
    public function generate(array $issueList, $fileName, $commonPathPrefix, $excludeOK = false)
    {
        $issues           = $this->formatIssues($issueList);
        $shortFilename    = substr($fileName, strlen($commonPathPrefix));
        $data['issues']   = $issueList;
        $data['filepath'] = $shortFilename;
        $data['source']   = $this->formatSourceCode($fileName, $issues);

        $depth            = substr_count($shortFilename, DIRECTORY_SEPARATOR);
        $data['csspath']  = str_repeat('../', $depth - 1 >= 0 ? $depth - 1 : 0);

        //we want to exclude files without issues and there are no issues in this one
        if ($excludeOK && !$data['issues']) {
            return;
        }
        $this->ioHelper->createFile(
            $this->outputDir . $shortFilename . '.html',
            $this->render('review', $data)
        );
    }

    /**
     * Source code is highlighted an formatted.
     *
     * Besides highlighting, whole lines will be marked with different colors
     * and JQuery functions (like tooltips) are integrated.
     *
     * @param string $filename     The file to format
     * @param array  $outputIssues Sorted issueList by line number
     *
     * @return string Html formatted string
     */
    private function formatSourceCode($filename, $outputIssues)
    {
        $sourceDom  = $this->highlightCode($filename);
        $xpath      = new DOMXPath($sourceDom);
        $lines      = $xpath->query('//ol/li');

        // A shortcut to prevent possible trouble with log(0)
        // Note that this is exactly what will happen anyways.
        if ($lines->length === 0) {
            return $sourceDom->saveHTML();
        }

        $lineNumber = 0;
        $linePlaces = floor(log($lines->length, 10)) + 1;

        /** @var $line DOMElement:: */
        foreach ($lines as $line) {
            ++$lineNumber;
            $line->setAttribute('id', 'line_' . $lineNumber);

            $lineClasses = array(
                ($lineNumber % 2) ? 'odd' : 'even'
            );

            if (isset($outputIssues[$lineNumber])) {
                $lineClasses[] = 'hasIssues';
                $message = '|';
                foreach ($outputIssues[$lineNumber] as $issue) {
                    $message .= sprintf(
                        '
                        <div class="tooltip">
                            <div class="title %s">%s</div>
                            <div class="text">%s</div>
                        </div>
                        ',
                        $issue->foundBy,
                        $issue->foundBy,
                        $issue->description
                    );
                }
                $line->setAttribute('title', utf8_encode($message));
            }

            // Add line number
            $nuSpan = $sourceDom->createElement('span');
            $nuSpan->setAttribute('class', 'lineNumber');
            for ($i = 0; $i < $linePlaces - strlen($lineNumber); $i++) {
                $nuSpan->appendChild($sourceDom->createEntityReference('nbsp'));
            }
            $nuSpan->appendChild($sourceDom->createTextNode($lineNumber));
            $nuSpan->appendChild($sourceDom->createEntityReference('nbsp'));
            $line->insertBefore($nuSpan, $line->firstChild);

            //create anchor for the new line
            $anchor = $sourceDom->createElement('a');
            $anchor->setAttribute('name', 'line_' . $lineNumber);
            $line->appendChild($anchor);

            // set li css class depending on line errors
            switch ($tmp = (isset($outputIssues[$lineNumber])
                    ? count($outputIssues[$lineNumber])
                    : 0)) {
                case 0:
                    break;
                case 1:
                    $lineClasses[] = $outputIssues[$lineNumber][0]->foundBy;
                    break;
                case 1 < $tmp:
                    $lineClasses[] = 'moreErrors';
                    break;
                // This can't happen, count always returns >= 0
                // @codeCoverageIgnoreStart
                default:
                    break;
                // @codeCoverageIgnoreEnd
            }
            $line->setAttribute('class', implode(' ', $lineClasses));
        }
        return $sourceDom->saveHTML();
    }

    /**
     * Highlighter method for PHP source code
     *
     * The source code is highlighted by PHP native method.
     * Afterwords a DOMDocument will be generated with each
     * line in a separate node.
     *
     * @param string $sourceCode The PHP source code
     *
     * @return DOMDocument
     */
    protected function highlightPhpCode($sourceCode)
    {
        $code = highlight_string($sourceCode, true);
        if (extension_loaded('mbstring') && !mb_check_encoding($code, 'UTF-8')) {
            $detectOrder = mb_detect_order();
            $detectOrder[] = 'iso-8859-1';

            $encoding = mb_detect_encoding($code, $detectOrder, true);
            if ($encoding === false) {
                error_log('Error detecting file encoding');
            }
            $code = mb_convert_encoding(
                $code,
                'UTF-8',
                $encoding
            );
        }

        $sourceDom = new DOMDocument();
        $sourceDom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $code);

        //fetch <code>-><span>->children from php generated html
        $sourceElements = $sourceDom->getElementsByTagname('code')->item(0)
                                    ->childNodes->item(0)->childNodes;

        //create target dom
        $targetDom  = new DOMDocument();
        $targetNode = $targetDom->createElement('ol');
        $targetNode->setAttribute('class', 'code');
        $targetDom->appendChild($targetNode);

        $li = $targetDom->createElement('li');
        $targetNode->appendChild($li);

        // iterate through all <span> elements
        foreach ($sourceElements as $sourceElement) {
            if (!$sourceElement instanceof DOMElement) {
                $span = $targetDom->createElement('span');
                $span->nodeValue = htmlspecialchars($sourceElement->wholeText);
                $li->appendChild($span);
                continue;
            }

            if ('br' === $sourceElement->tagName) {
                // create new li and new line
                $li = $targetDom->createElement('li');
                $targetNode->appendChild($li);
                continue;
            }

            $elementClass = $this->mapPhpColors(
                $sourceElement->getAttribute('style')
            );

            foreach ($sourceElement->childNodes as $sourceChildElement) {
                if ($sourceChildElement instanceof DOMElement
                && 'br' === $sourceChildElement->tagName) {
                    // create new li and new line
                    $li = $targetDom->createElement('li');
                    $targetNode->appendChild($li);
                } else {
                    // append content to current li element
                    $span = $targetDom->createElement('span');
                    $span->nodeValue = htmlspecialchars(
                        $sourceChildElement->textContent
                    );
                    $span->setAttribute('class', $elementClass);
                    $li->appendChild($span);
                }
            }
        }
        return $targetDom;
    }

    /**
     * Return colors defined in ini files.
     *
     * @param string $style The given style name, e.g. "comment"
     *
     * @return string
     */
    protected function mapPhpColors($style)
    {
        $color = substr($style, 7);
        return $this->phpHighlightColorMap[$color];
    }

    /**
     * Highlighting source code of given file.
     *
     * Php code is using native php highlighter.
     * If PEAR Text_Highlighter is installed all defined files in $highlightMap
     * will be highlighted as well.
     *
     * @param string $file The filename / real path to file
     *
     * @return DOMDocument Html representation of parsed source code
     */
    protected function highlightCode($file)
    {
        $sourceCode = $this->ioHelper->loadFile($file);
        $extension  = pathinfo($file, PATHINFO_EXTENSION);

        if (in_array($extension, $this->phpSuffixes)) {
            return $this->highlightPhpCode($sourceCode);
        } else {
            $sourceCode = preg_replace(
                '/^.*$/m',
                '<li>$0</li>',
                htmlentities($sourceCode)
            );
            $sourceCode = preg_replace('/ /', '&nbsp;', $sourceCode);
            $sourceCode = '<div class="code"><ol class="code">'
                        . $sourceCode.'</ol></div>';
            $sourceCode = $this->stripInvalidXml($sourceCode);

            $doc = new DOMDocument();
            $doc->loadHTML($sourceCode);

            return $doc;
        }
    }

    /**
     * Sorting a list of issues combining issues matching same line number
     * for each file.
     *
     * @param array $issueList List of issues
     *
     * @return array
     */
    private function formatIssues($issueList)
    {
        $outputIssues = array();
        foreach ($issueList as $issue) {
            for ($i = $issue->lineStart; $i <= $issue->lineEnd; $i++) {
                $outputIssues[$i][] = $issue;
            }
        }
        return $outputIssues;
    }

    /**
     * Removes invalid XML
     *
     * @access private
     * @param string $value
     * @return string
     */
    private function stripInvalidXml($value)
    {
        $ret = "";
        $current = null;
        if (empty($value)) {
            return $ret;
        }

        $length = strlen($value);
        for ($i=0; $i < $length; $i++) {
            $current = ord($value{$i});
            if (($current == 0x9)
                || ($current == 0xA)
                || ($current == 0xD)
                || (($current >= 0x20) && ($current <= 0xD7FF))
                || (($current >= 0xE000) && ($current <= 0xFFFD))
                || (($current >= 0x10000) && ($current <= 0x10FFFF))
            ) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }
        }
        return $ret;
    }
}
