<?php
/**
 * File containing the ezcConsoleOptionMandatoryViolationException.
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
 * An option was marked to be mandatory but was not submitted.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleOptionMandatoryViolationException extends ezcConsoleOptionException
{
    /**
     * Creates a new exception object. 
     * 
     * @param ezcConsoleOption $option The violating option.
     * @return void
     */
    public function __construct( ezcConsoleOption $option )
    {
        parent::__construct( "Option with long name '{$option->long}' is mandatory but was not submitted." );
    }
}

?>
