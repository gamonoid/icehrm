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
// Define a new format "headline"
$out->formats->headline->color = 'red';
$out->formats->headline->style = array( 'bold' );
// Define a new format "sum"
$out->formats->sum->color = 'blue';
$out->formats->sum->style = array( 'negative' );

// Create a new table
$table = new ezcConsoleTable( $out, 60 );

// Create first row and in it the first cell
$table[0][0]->content = 'Headline 1';

// Create 3 more cells in row 0
for ( $i = 2; $i < 5; $i++ )
{
     $table[0][]->content = "Headline $i";
}

$data = array( 1, 2, 3, 4 );

// Create some more data in the table...
foreach ( $data as $value )
{
     // Create a new row each time and set it's contents to the actual value
     $table[][0]->content = "$value";
}

// Set another border format for our headline row
$table[0]->borderFormat = 'headline';

// Set the content format for all cells of the 3rd row to "sum"
$table[2]->format = 'sum';

$table->outputTable();
echo "\n";
/*

RESULT (without color):

+------------+------------+------------+------------+       // 
| Headline 1 | Headline 2 | Headline 3 | Headline 4 |       // Red bordered line
+------------+------------+------------+------------+       // 
| 1          |            |            |            |
+------------+------------+------------+------------+
| 2          |            |            |            |       // Content printed in white on blue
+------------+------------+------------+------------+
| 3          |            |            |            |
+------------+------------+------------+------------+
| 4          |            |            |            |
+------------+------------+------------+------------+

*/
?>
