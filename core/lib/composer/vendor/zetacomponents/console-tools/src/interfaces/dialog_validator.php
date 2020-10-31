<?php
/**
 * File containing the ezcConsoleDialogValidator interface.
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
 * Interface that every console dialog validator class must implement.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
interface ezcConsoleDialogValidator
{
    /**
     * Perform no conversion on the result. 
     */
    const CONVERT_NONE  = 0;

    /**
     * Convert result to lower-case. 
     */
    const CONVERT_LOWER = 1;

    /**
     * Convert result to upper-case. 
     */
    const CONVERT_UPPER = 2;

    /**
     * Returns if the given result is valid. 
     * 
     * @param mixed $result The received result.
     * @return bool If the result is valid.
     */
    public function validate( $result );

    /**
     * Returns a fixed version of the result, if possible.
     * This method tries to repair the submitted result, if it is not valid,
     * yet. Fixing can be done in different ways, like casting into a certain
     * datatype, string manipulation, creating an object. A result returned
     * by fixup must not necessarily be valid, so a dialog should call validate
     * after trying to fix the result.
     * 
     * @param mixed $result The received result.
     * @return mixed The manipulated result.
     */
    public function fixup( $result );

}

?>
