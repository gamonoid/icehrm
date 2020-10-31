<?php
/**
 * Example for the usage of ezcConsoleProgressMonitor class.
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

$out = new ezcConsoleOutput();

// Create a progress monitor
$status = new ezcConsoleProgressMonitor( $out, 7 );

// Perform actions
$i = 0;
while( $i++ < 7 ) 
{
    // Do whatever you want to indicate progress for
    usleep( mt_rand( 20000, 2000000 ) );
    // Advance the statusbar by one step
    $status->addEntry( 'ACTION', "Performed action #{$i}." );
}

$out->outputLine();

/*
OUTPUT:

    14.3% ACTION Performed action #1.
    28.6% ACTION Performed action #2.
    42.9% ACTION Performed action #3.
    57.1% ACTION Performed action #4.
    71.4% ACTION Performed action #5.
    85.7% ACTION Performed action #6.
   100.0% ACTION Performed action #7.

*/
?>
