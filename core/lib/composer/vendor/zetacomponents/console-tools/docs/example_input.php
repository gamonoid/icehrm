<?php
/**
 * Example for the usage of ezcConsoleParameter class.
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

$optionHandler = new ezcConsoleInput();

// Register simple parameter -h/--help
$optionHandler->registerOption( new ezcConsoleOption( 'h', 'help' ) );

// Register complex parameter -f/--file
$file = new ezcConsoleOption(
 'f',
 'file',
 ezcConsoleInput::TYPE_STRING,
 null,
 false,
 'Process a file.',
 'Processes a single file.'
);
$optionHandler->registerOption( $file );

// Manipulate parameter -f/--file after registration
$file->multiple = true;

// Register another complex parameter that depends on -f and excludes -h
$dir = new ezcConsoleOption(
 'd',
 'dir',
 ezcConsoleInput::TYPE_STRING,
 null,
 true,
 'Process a directory.',
 'Processes a complete directory.',
 array( new ezcConsoleOptionRule( $optionHandler->getOption( 'f' ) ) ),
 array( new ezcConsoleOptionRule( $optionHandler->getOption( 'h' ) ) )
);
$optionHandler->registerOption( $dir );

// Register an alias for this parameter
$optionHandler->registerAlias( 'e', 'extended-dir', $dir );

// Process registered parameters and handle errors
try
{
     $optionHandler->process( array( 'example_input.php', '-h' ) );
}
catch ( ezcConsoleOptionException $e )
{
     echo $e->getMessage();
     exit( 1 );
}

// Process a single parameter
$file = $optionHandler->getOption( 'f' );
if ( $file->value === false )
{
     echo "Parameter -{$file->short}/--{$file->long} was not submitted.\n";
}
elseif ( $file->value === true )
{
     echo "Parameter -{$file->short}/--{$file->long} was submitted without value.\n";
}
else
{
     echo "Parameter -{$file->short}/--{$file->long} was submitted with value <".var_export($file->value, true).">.\n";
}

// Process all parameters at once:
foreach ( $optionHandler->getOptionValues() as $paramShort => $val )
{
     switch ( true )
     {
         case $val === false:
             echo "Parameter $paramShort was not submitted.\n";
             break;
         case $val === true:
             echo "Parameter $paramShort was submitted without a value.\n";
             break;
         case is_array( $val ):
             echo "Parameter $paramShort was submitted multiple times with value: <".implode(', ', $val).">.\n";
             break;
         default:
             echo "Parameter $paramShort was submitted with value: <$val>.\n";
             break;
     }
}
?>
