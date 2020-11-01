<?php
/**
 * File containing the ezcConsoleInvalidOutputTargetException.
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
 * Thrown if a given target {@link ezcConsoleOutputFormat} could not be opened.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleInvalidOutputTargetException extends ezcConsoleException
{
    
    /**
     * Creates a new exception object.
     * 
     * @param string $target Affected target.
     * @return void
     */
    public function __construct( $target )
    {
        parent::__construct( "The target '{$target}' could not be opened for writing." );
    }

}
?>
