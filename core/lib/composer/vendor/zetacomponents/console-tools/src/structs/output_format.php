<?php
/**
 * File containing the ezcConsoleOutputFormat class.
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
 * Struct class to store formating entities used by ezcConsoleOutput.
 *
 * Struct class to store formating entities used by ezcConsoleOutput.
 *
 * Possible values of {@link ezcConsoleOutputFormat::$color} are:
 * - gray
 * - red
 * - green
 * - yellow
 * - blue
 * - magenta
 * - cyan
 * - white
 * - default (representing the consoles default color)
 *
 * For {@link ezcConsoleOutputFormat::$bgcolor} the following values are valid:
 * - black
 * - red
 * - green
 * - yellow
 * - blue
 * - magenta
 * - cyan
 * - white
 * - default (representing the consoles default background color)
 *
 * The {@link ezcConsoleOutputFormat::$style} attribute takes an array of 
 * (possibly) multiple attributes. Choose from the lists below:
 *
 * - default (resets all attributes to default)
 *
 * - bold
 * - faint
 * - normal
 *
 * - italic
 * - notitalic
 *
 * - underlined
 * - doubleunderlined
 * - notunderlined
 *
 * - blink
 * - blinkfast
 * - noblink
 *
 * - negative
 * - positive
 *
 * @property string $color
 *           Contains the color for this format.
 * @property array(string) $style
 *           Contains the lists of styles that are associated with
 *           this format.
 * @property string $bgcolor
 *           Contains the background color for this format.
 * @property string $target
 *           Contains the output target to use. Pick one of
 *           ezcConsoleOutput::TARGET_OUTPUT, ezcConsoleOutput::TARGET_STDOUT
 *           or ezcConsoleOutput::TARGET_STDERR.
 *
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleOutputFormat
{
    /**
     * Container to hold the properties
     *
     * @var array(string=>mixed)
     */
    protected $properties = array( 
        'color'     => 'default',
        'style'     => array( 'default' ),
        'bgcolor'   => 'default',
        'target'    => ezcConsoleOutput::TARGET_OUTPUT,
    );

    /**
     * Create a new ezcConsoleOutputFormat object.
     * Creates a new object of this class.
     * 
     * @param string $color             Name of a color value.
     * @param array(string) $style Names of style values.
     * @param string $bgcolor           Name of a bgcolor value.
     * @param string $target            Target output stream.
     */
    public function __construct( $color = 'default', array $style = null, $bgcolor = 'default', $target = ezcConsoleOutput::TARGET_OUTPUT )
    {
        $this->__set( 'color', $color );
        $this->__set( 'style', isset( $style ) ? $style : array( 'default' ) );
        $this->__set( 'bgcolor', $bgcolor );
        $this->__set( 'target', $target );
    }
    
    /**
     * Overloaded __get() method to gain read-only access to some attributes.
     * 
     * @param string $propertyName Name of the property to read.
     * @return mixed Desired value if exists, otherwise null.
     * @ignore
     */
    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'style':
                return (array) $this->properties[$propertyName];
            case 'color':
            case 'bgcolor':
            case 'target':
                return $this->properties[$propertyName];
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
    }

    /**
     * Overloaded __set() method to gain read-only access to properties.
     * It also performs checks on setting others.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the setting you try to access does not exists
     * @throws ezcBaseValueException
     *         If trying to set an invalid value for a setting.
     * 
     * @param string $propertyName Name of the attrinbute to access.
     * @param string $val The value to set.
     * @ignore
     */
    public function __set( $propertyName, $val )
    {
        if ( !isset( $this->properties[$propertyName] ) )
        {
            throw new ezcBasePropertyNotFoundException( $propertyName );
        }
        // Extry handling of multi styles
        if ( $propertyName === 'style' )
        {
            if ( !is_array( $val ) ) $val = array( $val );
            foreach ( $val as $style )
            {
                if ( !ezcConsoleOutput::isValidFormatCode( $propertyName, $style ) )
                {
                    throw new ezcBaseValueException( $propertyName, $style, 'valid ezcConsoleOutput format code' );
                }
            }
            $this->properties['style'] = $val;
            return;
        }
        // Continue normal handling
        if ( ( $propertyName === "color" || $propertyName === "bgcolor" )
             && !ezcConsoleOutput::isValidFormatCode( $propertyName, $val ) )
        {
            throw new ezcBaseValueException( $propertyName, $val, 'valid ezcConsoleOutput format code' );
        }
        $this->properties[$propertyName] = $val;
    }
 
    /**
     * Property isset access.
     * 
     * @param string $propertyName Name of the property.
     * @return bool True is the property is set, otherwise false.
     * @ignore
     */
    public function __isset( $propertyName )
    {
        return isset( $this->properties[$propertyName] );
    }
    
}

?>
