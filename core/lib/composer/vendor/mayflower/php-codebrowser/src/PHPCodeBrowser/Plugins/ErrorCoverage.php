<?php
/**
 * Coverage
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
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage Plugins
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @copyright  2007-2010 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since  0.1.0
 */

namespace PHPCodeBrowser\Plugins;


use DOMElement;
use DOMNode;
use DOMNodeList;
use PHPCodeBrowser\Issue;
use PHPCodeBrowser\PluginsAbstract;

/**
 * ErrorCoverage
 *
 * @category   PHP_CodeBrowser
 * @package    PHP_CodeBrowser
 * @subpackage Plugins
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @copyright  2007-2010 Mayflower GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since  0.1.0
 */
class ErrorCoverage extends PluginsAbstract
{
    /**
     * Name of this plugin.
     * Used to read issues from XML.
     * @var string
     */
    public $pluginName = 'coverage';

    /**
     * Name of the attribute that holds the number of the first line
     * of the issue.
     * @var string
     */
    protected $lineStartAttr = 'num';

    /**
     * Name of the attribute that holds the number of the last line
     * of the issue.
     * @var string
     */
    protected $lineEndAttr = 'num';

    /**
     * Default string to use as source for issue.
     * @var string
     */
    protected $source = 'Coverage';

    /**
     * The detailed mapper method for each single plugin, returning an array
     * of Issue objects.
     * This method provides a default behaviour an can be overloaded to
     * implement special behavior for other plugins.
     *
     * @param DomNode $element  The XML plugin node with its errors
     * @param string  $filename Name of the file to return issues for.
     *
     * @return array            array of issue objects.
     */
    public function mapIssues(DomNode $element, $filename)
    {
        $errorList = array();

        $children = $element->childNodes;
        $childCount = $children->length;

        for ($next = 0; $next < $childCount; $next++) {
            $child = $children->item($next);

            if ($this->representsUncoveredLOC($child)) {
                $begin = $child->getAttribute('num');
                $end   = $begin;

                $next += 1;
                while ($next < $childCount) {
                    $child = $children->item($next);
                    if (!$child instanceof DOMElement) {
                        $next += 1;
                        continue;
                    }
                    if (!$this->representsUncoveredLOC($child)) {
                        break;
                    }

                    $end = $child->getAttribute('num');
                    $next += 1;
                }

                $errorList[] = new Issue(
                    $filename,
                    $begin,
                    $end,
                    'Coverage',
                    'Not covered',
                    'Notice'
                );
            }
        }

        return $errorList;
    }

    /**
     * Check if the given object is a DOMElement representing an
     * uncovered line of code.
     */
    private function representsUncoveredLOC($elem)
    {
        return ($elem instanceof DOMElement
             && 0      === (int) $elem->getAttribute('count')
             && 'line' ===       $elem->nodeName
             && 'stmt' ===       $elem->getAttribute('type'));
    }

    /**
     * Get an array with all files that have issues.
     *
     * @return array
     */
    public function getFilesWithIssues()
    {
        $fileNames  = array();
        $issueNodes = $this->issueXml->query(
            '/*/'.$this->pluginName.'/*//file[@name]'
        );

        foreach ($issueNodes as $node) {
            $fileNames[] = $node->getAttribute('name');
        }

        return array_unique($fileNames);
    }

    /**
     * Get all DOMNodes that represent issues for a specific file.
     *
     * @param string $filename Name of the file to get nodes for.
     * @return DOMNodeList
     */
    protected function getIssueNodes($filename)
    {
        return $this->issueXml->query(
            '/*/'.$this->pluginName.'/*//file[@name="'.$filename.'"]'
        );
    }
}
