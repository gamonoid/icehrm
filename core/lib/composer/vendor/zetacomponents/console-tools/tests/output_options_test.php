<?php
/**
 * ezcConsoleOutputOptionsTest 
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
 * Test suite for ezcConsoleOutputOptions struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleOutputOptionsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleOutputOptionsTest" );
	}

    /**
     * testConstructor
     * 
     * @access public
     */
    public function testConstructor()
    {
        $fake = new ezcConsoleOutputOptions( 1, 0, true, 'UTF-8' );
        $this->assertEquals( 
            $fake,
            new ezcConsoleOutputOptions(),
            'Default values incorrect for ezcConsoleOutputOptions.'
        );
    }
    
    /**
     * testConstructorNew
     * 
     * @access public
     */
    public function testConstructorNew()
    {
        $fake = new ezcConsoleOutputOptions(
            array( 
                "verbosityLevel" => 1,
                "autobreak" => 0,
                "useFormats" => true,
            )
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleOutputOptions(),
            'Default values incorrect for ezcConsoleOutputOptions.'
        );
    }

    public function testCompatibility()
    {
        $old = new ezcConsoleOutputOptions( 5, 80 );
        $new = new ezcConsoleOutputOptions(
            array( 
                "verbosityLevel"    => 5,
                "autobreak"         => 80,
            )
        );
        $this->assertEquals( $old, $new, "Old construction method did not produce same result as old one." );
    }

    public function testNewAccess()
    {
        $opt = new ezcConsoleOutputOptions();
        $this->assertEquals( $opt->verbosityLevel, 1 );
        $this->assertEquals( $opt->autobreak, 0 );
        $this->assertEquals( $opt->useFormats, true );
        $this->assertEquals( $opt["verbosityLevel"], 1 );
        $this->assertEquals( $opt["autobreak"], 0 );
        $this->assertEquals( $opt["useFormats"], true );
    }

    public function testGetAccessSuccess()
    {
        $opt = new ezcConsoleOutputOptions();
        $this->assertEquals( 1, $opt->verbosityLevel );
        $this->assertEquals( 0, $opt->autobreak );
        $this->assertEquals( true, $opt->useFormats );
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcConsoleOutputOptions();

        $exceptionThrown = false;
        try
        {
            echo $opt->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on get access of invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $opt = new ezcConsoleOutputOptions();

        $this->assertSetProperty(
            $opt,
            'verbosityLevel',
            array( 0, 1, 10, 42 )
        );
        $this->assertSetProperty(
            $opt,
            'autobreak',
            array( 0, 1, 10, 42 )
        );
        $this->assertSetProperty(
            $opt,
            'useFormats',
            array( false, true )
        );
    }

    public function testSetAccessFailure()
    {
        $opt = new ezcConsoleOutputOptions();

        $this->assertSetPropertyFails(
            $opt,
            'verbosityLevel',
            array( -1, 23.42, 'foo', '', true, false, array(), new stdClass() )
        );
        $this->assertSetPropertyFails(
            $opt,
            'autobreak',
            array( -1, 23.42, 'foo', '', true, false, array(), new stdClass() )
        );
        $this->assertSetPropertyFails(
            $opt,
            'useFormats',
            array( 'foo', '', 23, -42, 23.42, array(), new stdClass() )
        );

        $this->assertSetPropertyFails(
            $opt,
            'foo',
            array( '', 'bar', 23, -42, 23.42, true, false, array(), new stdClass() )
        );

        $this->assertSetPropertyFails(
            $opt,
            'characterEncoding',
            array( '', 'bar', 23, -42, 23.42, true, false, array(), new stdClass() )
        );
    }

    public function testIsset()
    {
        $opt = new ezcConsoleOutputOptions();
        $this->assertTrue( isset( $opt->verbosityLevel ) );
        $this->assertTrue( isset( $opt->autobreak ) );
        $this->assertTrue( isset( $opt->useFormats ) );
        $this->assertFalse( isset( $opt->characterEncoding ) );
        $this->assertFalse( isset( $opt->foo ) );
    }

}

?>
