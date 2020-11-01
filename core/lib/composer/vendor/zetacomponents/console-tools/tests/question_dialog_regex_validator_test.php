<?php
/**
 * ezcConsoleQuestionDialogRegexValidatorTest class.
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
 * Test suite for ezcConsoleQuestionDialogRegexValidator class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleQuestionDialogRegexValidatorTest extends ezcTestCase
{
	public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( "ezcConsoleQuestionDialogRegexValidatorTest" );
    }

    public function testGetAccessDefaultSuccess()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );
        $this->assertEquals( "//", $validator->pattern );
        $this->assertNull( $validator->default );
    }

    public function testGetAccessCustomSuccess()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator(
            "/[0-9]+/",
            23
        );
        $this->assertEquals( "/[0-9]+/", $validator->pattern );
        $this->assertEquals( 23, $validator->default );
    }

    public function testGetAccessFailure()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );
        
        try
        {
            echo $validator->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );

        $validator->pattern = "@^\s+$@";
        $validator->default = 23.42;

        $this->assertEquals( "@^\s+$@", $validator->pattern );
        $this->assertEquals( 23.42, $validator->default );
    }

    public function testSetAccessFailure()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );

        $exceptionCaught = false;
        try
        {
            $validator->pattern = "";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property type." );
        
        $exceptionCaught = false;
        try
        {
            $validator->default = array();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property default." );
        
        $exceptionCaught = false;
        try
        {
            $validator->foo = "Foo";
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on nonexistent property foo." );
    }

    public function testIssetAccess()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );
        $this->assertTrue( isset( $validator->pattern ), "Property pattern not set." );
        $this->assertTrue( isset( $validator->default ), "Property default not set." );

        $this->assertFalse( isset( $validator->foo ), "Property foo set." );
    }

    public function testValidate()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );

        $this->assertTrue( $validator->validate( "foo" ), "Value foo is invalid." );
        $this->assertTrue( $validator->validate( true ) );
        $this->assertTrue( $validator->validate( 23 ) );

        $validator->pattern = "/^[0-9]+\.[a-z]+$/";

        $this->assertTrue( $validator->validate( "123.abc" ), "Value 123.abc is invalid." );
        $this->assertFalse( $validator->validate( "abc" ) );
        $this->assertFalse( $validator->validate( 23 ) );
        
        $validator->default = "foo";

        $this->assertTrue( $validator->validate( "" ), "Empty value is invalid." );
    }

    public function testFixup()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );

        $this->assertEquals( "23", $validator->fixup( "23" ) );
        $this->assertEquals( "-23", $validator->fixup( "-23" ) );
        $this->assertEquals( "foo", $validator->fixup( "foo" ) );
        $this->assertEquals( "23.42", $validator->fixup( "23.42" ) );
        $this->assertEquals( "-23.42", $validator->fixup( "-23.42" ) );
        $this->assertEquals( "true", $validator->fixup( "true" ) );
        $this->assertEquals( "false", $validator->fixup( "false" ) );
        $this->assertEquals( "1", $validator->fixup( "1" ) );
        $this->assertEquals( "0", $validator->fixup( "0" ) );
        $this->assertEquals( "", $validator->fixup( "" ) );
        
        $validator->default = "foo";

        $this->assertEquals( "foo", $validator->fixup( "" ) );
    }

    public function testGetResultString()
    {
        $validator = new ezcConsoleQuestionDialogRegexValidator( "//" );

        $this->assertEquals( "(match //)", $validator->getResultString() );

        $validator->default = "foo";

        $this->assertEquals( "(match //) [foo]", $validator->getResultString() );

        $validator->pattern = "/^[0-9]+\.[a-z]+$/";
        $validator->default = null;

        $this->assertEquals( "(match /^[0-9]+\.[a-z]+$/)", $validator->getResultString() );

        $validator->default = "foo";
        
        $this->assertEquals( "(match /^[0-9]+\.[a-z]+$/) [foo]", $validator->getResultString() );
    }
}

?>
