<?php
/**
 * File containing the ezcConsoleInputValidator interface.
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
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 */
/**
 * Interface for input validators used in ezcConsoleInput.
 *
 * An instance of this interface is used in {@link ezcConsoleInput} to validate 
 * options and arguments.
 * 
 * @package ConsoleTools
 * @version //autogen//
 *
 * @access private
 * @TODO Verify interface and make it public to replace the validation in 
 *       {@link ezcConsoleInput}.
 */
interface ezcConsoleInputValidator
{
    /**
     * Validates the given options.
     *
     * May throw an exception that derives from {@link ezcConsoleException}.  
     * Receives the array of $options defined for validation and $hasArguments 
     * to indicates if arguments have been submitted in addition.
     *
     * @param array(ezcConsoleOption) $options
     * @param bool $hasArguments
     */
    public function validateOptions( array $options, $hasArguments );

    // @TODO: validateArguments();
}

?>
