<?php
/**
 * Example for the usage of ezcConsoleOutput class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package ConsoleTools
 * @version //autogen//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

require_once 'Base/src/base.php';

/**
 * Autoload ezc classes 
 * 
 * @param string $className 
 */
function __autoload( $className )
{
    ezcBase::autoload( $className );
}

// Create the output handler
$out = new ezcConsoleOutput();

// Set the verbosity to level 10
$out->options->verbosityLevel = 10;
// Enable auto wrapping of lines after 40 characters
$out->options->autobreak    = 40;

// Set the color of the default output format to green
$out->formats->default->color   = 'green';

// Set the color of the output format named 'success' to white
$out->formats->success->color   = 'white';
// Set the style of the output format named 'success' to bold
$out->formats->success->style   = array( 'bold' );

// Set the color of the output format named 'failure' to red
$out->formats->failure->color   = 'red';
// Set the style of the output format named 'failure' to bold
$out->formats->failure->style   = array( 'bold' );
// Set the background color of the output format named 'failure' to blue
$out->formats->failure->bgcolor = 'blue';

// Output text with default format
$out->outputText( 'This is default text ' );
// Output text with format 'success'
$out->outputText( 'including success message', 'success' );
// Some more output with default output.
$out->outputText( "and a manual linebreak.\n" );

// Manipulate the later output
$out->formats->success->color = 'green';
$out->formats->default->color = 'blue';

// This is visible, since we set verboseLevel to 10, and printed in default format (now blue)
$out->outputText( "Some verbose output.\n", null, 10 );
// This is not visible, since we set verboseLevel to 10
$out->outputText( "Some more verbose output.\n", null, 20 );
// This is visible, since we set verboseLevel to 10, and printed in format 'failure'
$out->outputText( "And some not so verbose, failure output.\n", 'failure', 5 );

?>
