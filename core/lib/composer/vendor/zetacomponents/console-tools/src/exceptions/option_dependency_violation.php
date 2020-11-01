<?php
/**
 * File containing the ezcConsoleOptionDependencyViolationException.
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

/**
 * A dependency rule for a parameter was violated.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleOptionDependencyViolationException extends ezcConsoleOptionException
{
    /**
     * Creates a new exception object. 
     * 
     * @param ezcConsoleOption $dependingOption The depending option.
     * @param ezcConsoleOption $dependantOption The option depended on.
     * @param mixed $valueRange                 The dependend value range.
     * @return void
     */
    public function __construct( ezcConsoleOption $dependingOption, ezcConsoleOption $dependantOption, $valueRange = null )
    {
        $message  = "The option '{$dependingOption->long}' depends on the option '{$dependantOption->long}' ";
        if ( $valueRange !== null )
        {
            $message .= "to have a value in '{$valueRange}' ";
        }
        $message .= "but this one was not submitted.";
        parent::__construct( $message );
    }
}
?>
