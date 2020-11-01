<?php
/**
 * Example for the usage of ezcConsoleTable class.
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

// Initialize the console output handler
$out = new ezcConsoleOutput();

// Define format schemes for even and odd rows
$out->formats->evenRow->color = 'red';
$out->formats->evenRow->style = array( 'bold' );

$out->formats->oddRow->color = 'blue';
$out->formats->oddRow->style = array( 'bold' );

// Define format schemes for even and odd cells
$out->formats->evenCell->color = 'red';
$out->formats->evenCell->style = array( 'negative' );

$out->formats->oddCell->color = 'blue';
$out->formats->oddCell->style = array( 'negative' );

// Create a new table with a width of 60 chars
$table = new ezcConsoleTable( $out, 60 );
    
// Set global cell content align
$table->options->defaultAlign = ezcConsoleTable::ALIGN_CENTER;

for ( $i = 0; $i < 5; $i ++ )
{
    for ( $j = 0; $j < 5; $j++ )
    {
        // Fill each table cell with ##
        $table[$i][$j]->content = '##';
        if ( $i === $j )
        {
            // On diagonal line set explicit cell format for even/odd
            $table[$i][$j]->format = $j % 2 == 0 ? 'evenCell' : 'oddCell';
        }
    }
    // Set global format for even/odd rows
    $table[$i]->format = $i % 2 == 0 ? 'evenRow' : 'oddRow';
    // Set border format for even/odd rows
    $table[$i]->borderFormat = $i % 2 == 0 ? 'evenRow' : 'oddRow';
}

$table->outputTable();
echo "\n";
?>
