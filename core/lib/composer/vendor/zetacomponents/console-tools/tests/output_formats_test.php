<?php
/**
 * ezcConsoleOutputFormatsTest 
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
 * @subpackage Tests
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Test suite for ezcConsoleOutputFormats struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleOutputFormatsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleOutputFormatsTest" );
	}

    public function testConstructor()
    {
        $formats = new ezcConsoleOutputFormats();
        $formats->default = new ezcConsoleOutputFormat();
        $this->assertEquals( 
            $formats,
            new ezcConsoleOutputFormats(),
            'Default values incorrect for ezcConsoleOutputFormats.'
        );
    }

    public function testGetAccessNonExistent()
    {
        $formats = new ezcConsoleOutputFormats();
        $format = new ezcConsoleOutputFormat();
        $this->assertEquals( 
            $format,
            $formats->foobar
        );
    }

    public function testGetAccessExistent()
    {
        $formats = new ezcConsoleOutputFormats();
        $format = new ezcConsoleOutputFormat();
        $formats->foobar = $format;
        $this->assertEquals( 
            $format,
            $formats->foobar
        );
    }

    public function testGetAccessManipulate()
    {
        $formats = new ezcConsoleOutputFormats();
        $formats->foobar->color = 'blue';
        
        $format = new ezcConsoleOutputFormat();
        $format->color = 'blue';
        
        $this->assertEquals( 
            $format,
            $formats->foobar
        );
    }

    public function testSetAccessExistent()
    {
        $formats = new ezcConsoleOutputFormats();
        $format = new ezcConsoleOutputFormat();
        $formats->foobar = $format;
        $this->assertEquals( 
            $format,
            $formats->foobar
        );
    }

    public function testIssetAccessSuccess()
    {
        $formats = new ezcConsoleOutputFormats();
        $formats->foobar = new ezcConsoleOutputFormat();
        $this->assertTrue( isset( $formats->foobar ) );
    }

    public function testIssetAccessFailure()
    {
        $formats = new ezcConsoleOutputFormats();
        $this->assertFalse( isset( $formats->foobar ) );
    }

    public function testIterator()
    {
        $formatsObj = new ezcConsoleOutputFormats();
        $formatsArr = $this->readAttribute(
            $formatsObj, 'formats'
        );
        reset( $formatsArr );

        // First run
        foreach ( $formatsObj as $name => $format )
        {
            $this->assertEquals(
                current( $formatsArr ),
                $format
            );
            $this->assertEquals(
                key( $formatsArr ),
                $name
            );

            next( $formatsArr );
        }
        $this->assertFalse(
            current( $formatsArr )
        );
        $this->assertFalse(
            $formatsObj->valid()
        );
        
        reset( $formatsArr );
        // Second run
        foreach ( $formatsObj as $name => $format )
        {
            $this->assertEquals(
                current( $formatsArr ),
                $format
            );
            $this->assertEquals(
                key( $formatsArr ),
                $name
            );

            next( $formatsArr );
        }
        $this->assertFalse(
            current( $formatsArr )
        );
        $this->assertFalse(
            $formatsObj->valid()
        );
    }

    public function testCountable()
    {
        $formatsObj = new ezcConsoleOutputFormats();

        $this->assertEquals(
            3,
            count( $formatsObj )
        );

        $formatsObj->foobar->color = 'blue';

        $this->assertEquals(
            4,
            count( $formatsObj )
        );
    }
}

?>
