<?php
/**
 * ezcConsoleArgumentsTest class.
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
 * Test suite for ezcConsoleArguments class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleArgumentsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleArgumentsTest" );
	}

    protected function setUp()
    {
    }

    public function testOffsetSetSuccess()
    {
        $args = new ezcConsoleArguments();

        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );
        $args[5] = new ezcConsoleArgument( "arg 5" );
        $args[]  = new ezcConsoleArgument( "arg 6" );
        $args[4] = new ezcConsoleArgument( "arg 4" );
        $args[2] = new ezcConsoleArgument( "arg 2" );
        // This is basically unset!
        $args[1] = null;

        $refNames = array( 0 => "arg 0", 2 => "arg 2",  4 => "arg 4", 5 => "arg 5", 6 => "arg 6" );
        reset( $refNames );

        foreach ( $args as $name => $arg )
        {
            $this->assertEquals( key( $refNames ), $name, "Name not correctly returned as key." );
            $this->assertEquals( current( $refNames ), $arg->name, "Arg not correctly returned." );
            next( $refNames );
        }
    }

    public function testOffsetSetFailure()
    {
        $args = new ezcConsoleArguments();

        $exceptionThrown = false;
        try
        {
            $args[0] = new stdClass();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value object." );

        $exceptionThrown = false;
        try
        {
            $args[0] = array();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value array." );

        $exceptionThrown = false;
        try
        {
            $args[0] = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value scalar." );

        $exceptionThrown = false;
        try
        {
            $args[""] = new ezcConsoleArgument( "testname" );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid offset string." );

        $exceptionThrown = false;
        try
        {
            $args[0] = new ezcConsoleArgument( "testname" );
            $args[0] = new ezcConsoleArgument( "testname 2" );
        }
        catch ( ezcConsoleArgumentAlreadyRegisteredException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcConsoleArgumentAlreadyRegisteredException not thrown double registered offset." );

        $exceptionThrown = false;
        try
        {
            $args[2] = new ezcConsoleArgument( "testname" );
            $args[3] = new ezcConsoleArgument( "testname" );
        }
        catch ( ezcConsoleArgumentAlreadyRegisteredException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcConsoleArgumentAlreadyRegisteredException not thrown double registered name." );
    }

    public function testOffsetGetSuccess()
    {
        $args = new ezcConsoleArguments();

        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );
        $args[5] = new ezcConsoleArgument( "arg 5" );
        $args[]  = new ezcConsoleArgument( "arg 6" );
        $args[4] = new ezcConsoleArgument( "arg 4" );
        $args[2] = new ezcConsoleArgument( "arg 2" );
        // This is basically unset!
        $args[1] = null;

        $this->assertEquals( $args[0], new ezcConsoleArgument( "arg 0" ) );
        $this->assertEquals( $args[2], new ezcConsoleArgument( "arg 2" ) );
        $this->assertEquals( $args[4], new ezcConsoleArgument( "arg 4" ) );
        $this->assertEquals( $args[5], new ezcConsoleArgument( "arg 5" ) );
        $this->assertEquals( $args[6], new ezcConsoleArgument( "arg 6" ) );
        
        $this->assertEquals( $args["arg 0"], new ezcConsoleArgument( "arg 0" ) );
        $this->assertEquals( $args["arg 2"], new ezcConsoleArgument( "arg 2" ) );
        $this->assertEquals( $args["arg 4"], new ezcConsoleArgument( "arg 4" ) );
        $this->assertEquals( $args["arg 5"], new ezcConsoleArgument( "arg 5" ) );
        $this->assertEquals( $args["arg 6"], new ezcConsoleArgument( "arg 6" ) );
    }

    public function testOffsetGetFailure()
    {
        $args = new ezcConsoleArguments();
        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );
        
        $exceptionThrown = false;
        try
        {
            var_dump( $args[true] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on get access with invalid offset true." );
        
        $exceptionThrown = false;
        try
        {
            var_dump( $args[2] );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseProperytNotFoundException not thrown on get access with non-existent integer offset 2." );
        
        $exceptionThrown = false;
        try
        {
            var_dump( $args["arg 2"] );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseProperytNotFoundException not thrown on get access with non-existent string offset 'arg 2'." );
    }

    public function testOffsetUnsetSuccess()
    {
        $args = new ezcConsoleArguments();

        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );
        $args[5] = new ezcConsoleArgument( "arg 5" );
        $args[]  = new ezcConsoleArgument( "arg 6" );
        $args[4] = new ezcConsoleArgument( "arg 4" );
        $args[2] = new ezcConsoleArgument( "arg 2" );

        // This is basically unset!
        $args[1] = null;

        unset( $args[0], $args[2], $args[4], $args[5], $args[6] );

        $this->assertAttributeEquals(
            array(),
            "ordered",
            $args,
            "Unset on ordered array failed."
        );
        $this->assertAttributeEquals(
            array(),
            "named",
            $args,
            "Unset on named array failed."
        );
    }

    public function testOffsetUnsetFailure()
    {
        $args = new ezcConsoleArguments();

        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );

        $exceptionThrown = false;
        try
        {
            unset( $args["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on unset access to invald string offset." );

        $exceptionThrown = false;
        try
        {
            unset( $args[true] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on unset access to invald boolean offset." );
    }

    public function testOffsetExistsSuccess()
    {
        $args = new ezcConsoleArguments();

        $args[0] = new ezcConsoleArgument( "arg 0" );
        $args[1] = new ezcConsoleArgument( "arg 1" );
        $args[5] = new ezcConsoleArgument( "arg 5" );
        $args[]  = new ezcConsoleArgument( "arg 6" );
        
        $this->assertTrue( isset( $args[0] ) );
        $this->assertTrue( isset( $args["arg 0"] ) );

        $this->assertTrue( isset( $args[1] ) );
        $this->assertTrue( isset( $args["arg 1"] ) );

        $this->assertTrue( isset( $args[5] ) );
        $this->assertTrue( isset( $args["arg 5"] ) );

        $this->assertTrue( isset( $args[6] ) );
        $this->assertTrue( isset( $args["arg 6"] ) );

        $this->assertFalse( isset( $args[2] ) );
        $this->assertFalse( isset( $args["arg 2"] ) );
    }

    public function testOffsetExistsFailure()
    {
        $args = new ezcConsoleArguments();

        try
        {
            isset( $args[true] );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "ezcBaseValueException not thrown on isset access to invalid offset true." );
    }

    public function testIterator()
    {
        $refNames = array(
            0  => "arg 0",
            1  => "arg 1",
            2  => "arg 2",
            5  => "arg 5",
            10 => "arg 10"
        );

        $args = new ezcConsoleArguments();
        foreach ( $refNames as $key => $name )
        {
            $args[$key] = new ezcConsoleArgument( $name );
        }
        
        reset( $refNames );
        foreach ( $args as $key => $arg )
        {
            $this->assertEquals( key( $refNames ), $key );
            $this->assertEquals( current( $refNames ), $arg->name );
            next( $refNames );
        }
        reset( $refNames );
        foreach ( $args as $key => $arg )
        {
            $this->assertEquals( key( $refNames ), $key );
            $this->assertEquals( current( $refNames ), $arg->name );
            next( $refNames );
        }
    }

    public function testCount()
    {
        $refNames = array(
            0  => "arg 0",
            1  => "arg 1",
            2  => "arg 2",
            5  => "arg 5",
            10 => "arg 10"
        );

        $args = new ezcConsoleArguments();
        foreach ( $refNames as $key => $name )
        {
            $args[$key] = new ezcConsoleArgument( $name );
        }
        
        $this->assertEquals( 5, count( $args ) );
    }


}
?>
