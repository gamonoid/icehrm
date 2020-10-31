<?php
/**
 * ezcConsoleQuestionDialogCollectionValidatorTest class. 
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
 * Test suite for ezcConsoleQuestionDialogCollectionValidator class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleQuestionDialogCollectionValidatorTest extends ezcTestCase
{
	public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( "ezcConsoleQuestionDialogCollectionValidatorTest" );
    }

    public function testGetAccessDefaultSuccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );
        $this->assertEquals( $collection, $validator->collection );
        $this->assertNull( $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogCollectionValidator::CONVERT_NONE, $validator->conversion );
    }

    public function testGetAccessCustomSuccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator(
            $collection,
            "foo",
            ezcConsoleQuestionDialogCollectionValidator::CONVERT_UPPER
        );
        $this->assertEquals( $collection, $validator->collection );
        $this->assertEquals( "foo", $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogCollectionValidator::CONVERT_UPPER, $validator->conversion );
    }

    public function testGetAccessFailure()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );
        
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
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );

        $collectionNew = array( 23, 42 );
        $validator->collection = $collectionNew;
        $validator->default = 23;
        $validator->conversion = ezcConsoleQuestionDialogCollectionValidator::CONVERT_LOWER;

        $this->assertEquals( $collectionNew, $validator->collection );
        $this->assertEquals( 23, $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogCollectionValidator::CONVERT_LOWER, $validator->conversion );
    }

    public function testSetAccessFailure()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );
        
        $exceptionCaught = false;
        try
        {
            $validator->collection = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property collection." );

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
            $validator->conversion = "Foo";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property conversion." );

        $exceptionCaught = false;
        try
        {
            $validator->foo = "Foo";
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on access of nonexistent property foo." );
    }

    public function testIssetAccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );
        $this->assertTrue( isset( $validator->collection ), "Property collection not set." );
        $this->assertTrue( isset( $validator->default ), "Property default not set." );
        $this->assertTrue( isset( $validator->conversion ), "Property conversion not set." );

        $this->assertFalse( isset( $validator->foo ), "Property foo set." );
    }

    public function testValidate()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection );
        $this->assertTrue( $validator->validate( "foo" ) );
        $this->assertFalse( $validator->validate( "test" ) );
    }

    public function testFixup()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection, null );

        $this->assertEquals( "foo", $validator->fixup( "foo" ) );
        $this->assertEquals( "FOO", $validator->fixup( "FOO" ) );

        $validator->conversion = ezcConsoleQuestionDialogCollectionValidator::CONVERT_UPPER;
        
        $this->assertEquals( "FOO", $validator->fixup( "foo" ) );
        $this->assertEquals( "FOO", $validator->fixup( "FOO" ) );

        $validator->conversion = ezcConsoleQuestionDialogCollectionValidator::CONVERT_LOWER;
        
        $this->assertEquals( "foo", $validator->fixup( "foo" ) );
        $this->assertEquals( "foo", $validator->fixup( "FOO" ) );

        $this->assertEquals( "", $validator->fixup( "" ) );

        $validator->default = "foo";

        $this->assertEquals( "foo", $validator->fixup( "" ) );
    }

    public function testGetResultString()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogCollectionValidator( $collection, null );

        $this->assertEquals( "(foo/bar/baz)", $validator->getResultString() );

        $validator->default = "foo";

        $this->assertEquals( "(foo/bar/baz) [foo]", $validator->getResultString() );
    }
}

?>
