<?php
/**
 * ezcConsoleArgumentTest class.
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
 * Test suite for ezcConsoleArgument class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleArgumentTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleArgumentTest" );
	}

    protected function setUp()
    {
    }

    public function testConstructorSuccess()
    {
        $arg = new ezcConsoleArgument(
            "testname",
            ezcConsoleInput::TYPE_INT,
            "Little short help",
            "Little long help",
            false,
            true,
            array( "test" )
        );
        $this->assertEquals( "testname", $arg->name );
        $this->assertEquals( ezcConsoleInput::TYPE_INT, $arg->type );
        $this->assertEquals( "Little short help", $arg->shorthelp );
        $this->assertEquals( "Little long help", $arg->longhelp );
        $this->assertEquals( false, $arg->mandatory );
        $this->assertEquals( array( "test" ), $arg->default );
        $this->assertEquals( true, $arg->multiple );
    }

    public function testConstructorFailure()
    {
        $exceptionThrown = false;
        try
        {
            $arg = new ezcConsoleArgument( 23 );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid name." );

        $exceptionThrown = false;
        try
        {
            $arg = new ezcConsoleArgument( "" );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid name." );
    }

    public function testGetAccessSuccess()
    {
        $arg = new ezcConsoleArgument( "testname" );
        $this->assertEquals( "testname", $arg->name );
        $this->assertEquals( ezcConsoleInput::TYPE_STRING, $arg->type );
        $this->assertEquals( "No help available.", $arg->shorthelp );
        $this->assertEquals( "There is no help for this argument available.", $arg->longhelp );
        $this->assertEquals( true, $arg->mandatory );
        $this->assertEquals( false, $arg->multiple );
        $this->assertEquals( null, $arg->default );
        $this->assertEquals( null, $arg->value );
    }

    public function testGetAccessFailure()
    {
        $arg = new ezcConsoleArgument( "testname" );
        try
        {
            echo $arg->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not found on access to invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $arg = new ezcConsoleArgument( "testname" );
        
        $this->assertSetProperty(
            $arg,
            "type",
            array( ezcConsoleInput::TYPE_STRING, ezcConsoleInput::TYPE_INT ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "shorthelp",
            array( "", "foo" ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "longhelp",
            array( "", "foo" ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "mandatory",
            array( true, false ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "multiple",
            array( true, false ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "default",
            array( "", "foo", array( "foo", "bar" ), null ),
            "ezcBaseValueException"
        );
        $this->assertSetProperty(
            $arg,
            "value",
            array( "", "foo", array( "foo", "bar" ), null ),
            "ezcBaseValueException"
        );
    }

    public function testSetAccessFailure()
    {
        $arg = new ezcConsoleArgument( "testname" );

        $this->assertSetPropertyFails(
            $arg,
            "name",
            array( "", "foo", 1, true, array(), new stdClass() ),
            "ezcBasePropertyPermissionException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "type",
            array( "", "foo", 23, true, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "shorthelp",
            array( 23, true, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "longhelp",
            array( 23, true, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "mandatory",
            array( "", "foo", 23, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "multiple",
            array( "", "foo", 23, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "default",
            array( new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $arg,
            "value",
            array( new stdClass() ),
            "ezcBaseValueException"
        );

        $this->assertSetPropertyFails(
            $arg,
            "foo",
            array( "foo" ),
            "ezcBasePropertyNotFoundException"
        );
    }
}
?>
