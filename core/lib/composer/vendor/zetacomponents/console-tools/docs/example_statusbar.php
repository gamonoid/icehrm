<?php
/**
 * Example for the usage of ezcConsoleStatusbar class.
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

// Create status bar itself
$status = new ezcConsoleStatusbar( $out );

// Perform actions
$i = 0;
while( $i++ < 20 ) 
{
    // Do whatever you want to indicate progress for
    usleep( mt_rand( 20000, 2000000 ) );
    // Indicate success or failure
    $status->add( (bool)mt_rand( 0, 1 ) );
}

$out->outputLine();
// Print statistics
$out->outputLine( $status->getSuccessCount() . ' operations succeeded, ' . $status->getFailureCount() . ' failed.' );

/*
OUTPUT:

+-++++-++++-++-+--+-
13 operations succeeded, 7 failed.

*/
?>
