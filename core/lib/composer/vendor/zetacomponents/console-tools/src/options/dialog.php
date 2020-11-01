<?php
/**
 * This file contains the ezcConsoleDialogOptions class.
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
 * @access public
 */

/**
 * Basic options class for ezcConsoleDialog implementations.
 * 
 * @property ezcConsoleDialogValidator $validator
 *           The validator to use with this dialog.
 * @property string $format
 *           The output format for the dialog.
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleDialogOptions extends ezcBaseOptions
{
    /**
     * Properties.
     * 
     * @var array(string=>mixed)
     */
    protected $properties = array(
        "format"    => "default",
        "validator" => null
    );

    /**
     * Property write access.
     * 
     * @param string $propertyName Name of the property.
     * @param mixed $propertyValue The value for the property.
     *
     * @throws ezcBasePropertyPermissionException
     *         If the property you try to access is read-only.
     * @throws ezcBasePropertyNotFoundException 
     *         If the the desired property is not found.
     * @ignore
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case "format":
                if ( is_string( $propertyValue ) === false || strlen( $propertyValue ) < 1 )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, "string, length > 0" );
                }
                break;
            case "validator":
                if ( ( $propertyValue instanceof ezcConsoleDialogValidator ) === false )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, "ezcConsoleDialogValidator" );
                }
                break;
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
        $this->properties[$propertyName] = $propertyValue;
    }
}

?>
