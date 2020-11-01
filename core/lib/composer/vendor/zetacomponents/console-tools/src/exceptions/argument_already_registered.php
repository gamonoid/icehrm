<?php
/**
 * File containing the ezcConsoleArgumentAlreadyRegisteredException class.
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
 * There is already an argument registered with the given name or at the given place.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleArgumentAlreadyRegisteredException extends ezcConsoleException
{
    /**
     * The name of the argument is already in use.
     */
    const NAMED = 1;
    
    /**
     * The position of the argument is already in use. Unset the position first and the re-register.
     */
    const ORDERED = 2;

    /**
     * Creates a new exception object.
     * The $type parameter can either be
     * {@link ezcConsoleArgumentAlreadyRegisteredException::NAMED} or
     * {@link ezcConsoleArgumentAlreadyRegisteredException::ORDERED}, indicating
     * if the name of the parameter or its place are already taken.
     * 
     * @param int $offset Offset of the already reagistered argument.
     * @param int $type   Type of the offset.
     * @return void
     */
    public function __construct( $offset, $type )
    {
        switch ( $type )
        {
            case self::NAMED:
                $message = "Argument with name '$offset' already registered.";
                break;
            case self::ORDERED:
                $message = "Argument at position '$offset' already registered.";
                break;
        }
        parent::__construct( $message );
    }
}
?>
