<?php
/**
 * File containing the ezcConsoleOutputFormats class.
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
 * Class to store the collection for formating classes.
 *
 * This class stores objects of {@link ezcConsoleOutputFormat}, which
 * represents a format option set for {@link ezcConsoleOutput}.
 *
 * <code>
 * // New ezcConsoleOutput 
 * // $output->format is instance of ezcConsoleOutputFormats.
 * $output = new ezcConsoleOutput();
 * 
 * // Default format - color = blue
 * $output->formats->default->color = 'blue';
 * // Default format - weight = bold
 * $output->formats->default->style = array( 'bold' );
 *
 * // New format "important" - color = red
 * $output->formats->important->color = 'red';
 * // Format "important" - background color = black
 * $output->formats->important->bgcolor = 'black';
 * </code>
 * 
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleOutputFormats implements Iterator, Countable
{
    /**
     * Array of ezcConsoleOutputFormat.
     * 
     * @var array(ezcConsoleOutputFormat)
     */
    protected $formats = array();

    /**
     * Create a new ezcConsoleOutputFormats object.
     *
     * Creates a new, empty object of this class. It also adds a default
     * format.
     */
    public function __construct()
    {
        $this->formats['default'] = new ezcConsoleOutputFormat();
        $this->formats['success'] = new ezcConsoleOutputFormat();
        $this->formats['success']->color = 'green';
        $this->formats['success']->style = array( 'bold' );
        $this->formats['failure'] = new ezcConsoleOutputFormat();
        $this->formats['failure']->color = 'red';
        $this->formats['failure']->style = array( 'bold' );
    }

    /**
     * Returns the current Iterator value.
     *
     * Implementation of {@link Iterator::current()}.
     * 
     * @return ezcConsoleOutputFormat
     */
    public function current()
    {
        return current( $this->formats );
    }

    /**
     * Advances the Iterator to the next element. 
     *
     * Implementation of {@link Iterator::next()}.
     * 
     * @return ezcConsoleOutputFormat|bool
     */
    public function next()
    {
        return next( $this->formats );
    }

    /**
     * Returns the current Iterator key. 
     *
     * Implementation of {@link Iterator::key()}.
     * 
     * @return string
     */
    public function key()
    {
        return key( $this->formats );
    }

    /**
     * Resets the Iterator to the first element.
     *
     * Implementation of {@link Iterator::rewind()}.
     * 
     * @return ezcConsoleOutputFormat
     */
    public function rewind()
    {
        return reset( $this->formats );
    }

    /**
     * Checks if the current Iterator position is still valid.
     *
     * Implementation of {@link Iterator::valid()}.
     * 
     * @return bool
     */
    public function valid()
    {
        return ( current( $this->formats ) !== false );
    }

    /**
     * Returns the number of registered formats.
     *
     * Implementation of {@link Countable::count()}.
     * 
     * @return void
     */
    public function count()
    {
        return count( $this->formats );
    }

    /**
     * Read access to the formats.
     *
     * Formats are accessed directly like properties of this object. If a
     * format does not exist, it is created on the fly (using default values),
     * 
     * @param string $formatName
     * @return ezcConsoleOutputFormat The format.
     */
    public function __get( $formatName )
    {
        if ( !isset( $this->formats[$formatName] ) )
        {
            $this->formats[$formatName] = new ezcConsoleOutputFormat();
        }
        return $this->formats[$formatName];
    }

    /**
     * Write access to the formats.
     *
     * Formats are accessed directly like properties of this object. If a
     * format does not exist, it is created on the fly (using default values),
     * 
     * @param string $formatName
     * @param ezcConsoleOutputFormat $val The format defintion.
     * @return void
     */
    public function __set( $formatName, ezcConsoleOutputFormat $val )
    {
        $this->formats[$formatName] = $val;
    }
 
    /**
     * Property isset access.
     * 
     * @param string $formatName Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $formatName )
    {
        return isset( $this->formats[$formatName] );
    }

}

?>
