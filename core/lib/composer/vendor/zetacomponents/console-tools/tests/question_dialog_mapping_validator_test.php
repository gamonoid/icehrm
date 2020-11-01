<?php
/**
 * ezcConsoleQuestionDialogMappingValidatorTest class. 
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
 * Test suite for ezcConsoleQuestionDialogMappingValidator class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleQuestionDialogMappingValidatorTest extends ezcTestCase
{
	public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( "ezcConsoleQuestionDialogMappingValidatorTest" );
    }

    public function testGetAccessDefaultSuccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );
        $this->assertEquals( $collection, $validator->collection );
        $this->assertNull( $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogMappingValidator::CONVERT_NONE, $validator->conversion );
        $this->assertEquals( array(), $validator->map );
    }

    public function testGetAccessCustomSuccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator(
            $collection,
            "foo",
            ezcConsoleQuestionDialogMappingValidator::CONVERT_UPPER,
            array( 'f' => 'foo' )
        );
        $this->assertEquals( $collection, $validator->collection );
        $this->assertEquals( "foo", $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogMappingValidator::CONVERT_UPPER, $validator->conversion );
        $this->assertEquals( array( 'f' => 'foo' ), $validator->map );
    }

    public function testGetAccessFailure()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );
        
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
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );

        $collectionNew         = array( 23, 42 );
        $validator->collection = $collectionNew;
        $validator->default    = 23;
        $validator->conversion = ezcConsoleQuestionDialogMappingValidator::CONVERT_LOWER;
        $newMap                = array( 'f' => 23, 'g' => 42 );
        $validator->map        = $newMap;

        $this->assertEquals( $collectionNew, $validator->collection );
        $this->assertEquals( 23, $validator->default );
        $this->assertEquals( ezcConsoleQuestionDialogMappingValidator::CONVERT_LOWER, $validator->conversion );
        $this->assertEquals( $newMap, $validator->map );
    }

    public function testSetAccessFailure()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );
        
        $exceptionCaught = false;
        try
        {
            $validator->collection = true;
            $this->fail( "Exception not thrown on invalid value for property collection." );
        }
        catch ( ezcBaseValueException $e )
        {
        }

        try
        {
            $validator->default = array();
            $this->fail( "Exception not thrown on invalid value for property default." );
        }
        catch ( ezcBaseValueException $e )
        {
        }

        try
        {
            $validator->conversion = "Foo";
            $this->fail( "Exception not thrown on invalid value for property conversion." );
        }
        catch ( ezcBaseValueException $e )
        {
        }

        try
        {
            $validator->map = "Foo";
            $this->fail( "Exception not thrown on invalid value for property map." );
        }
        catch ( ezcBaseValueException $e )
        {
        }

        $exceptionCaught = false;
        try
        {
            $validator->foo = "Foo";
            $this->fail( "Exception not thrown on access of nonexistent property foo." );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
        }
    }

    public function testIssetAccess()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );
        $this->assertTrue( isset( $validator->collection ), "Property collection not set." );
        $this->assertTrue( isset( $validator->default ), "Property default not set." );
        $this->assertTrue( isset( $validator->conversion ), "Property conversion not set." );
        $this->assertTrue( isset( $validator->map ), "Property map not set." );

        $this->assertFalse( isset( $validator->foo ), "Property foo set." );
    }

    public function testValidate()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection );
        $this->assertTrue( $validator->validate( "foo" ) );
        $this->assertFalse( $validator->validate( "test" ) );
    }

    public function testFixup()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator( $collection, null );

        $this->assertEquals( "foo", $validator->fixup( "foo" ), 'Fixup incorrect without conversion.' );
        $this->assertEquals( "FOO", $validator->fixup( "FOO" ), 'Fixup incorrect without conversion.' );

        $validator->conversion = ezcConsoleQuestionDialogMappingValidator::CONVERT_UPPER;
        
        $this->assertEquals( "FOO", $validator->fixup( "foo" ), 'Fixup incorrect with conversion to upper case.' );
        $this->assertEquals( "FOO", $validator->fixup( "FOO" ), 'Fixup incorrect with conversion to upper case.' );

        $validator->conversion = ezcConsoleQuestionDialogMappingValidator::CONVERT_LOWER;
        
        $this->assertEquals( "foo", $validator->fixup( "foo" ), 'Fixup incorrect with conversion to lower case.' );
        $this->assertEquals( "foo", $validator->fixup( "FOO" ), 'Fixup incorrect with conversion to lower case.' );

        $this->assertEquals( "", $validator->fixup( "" ) );

        $validator->default = "foo";

        $this->assertEquals( "foo", $validator->fixup( "" ) );
    }

    public function testFixupWithMapping()
    {
        $collection = array( 'y', 'n' );
        $validator = new ezcConsoleQuestionDialogMappingValidator(
            $collection,
            null,
            ezcConsoleQuestionDialogMappingValidator::CONVERT_NONE,
            array(
                'yes' => 'y',
                'no'  => 'n',
                '1'   => 'y',
                '0'   => 'n',
            )
        );

        $this->assertEquals( "y", $validator->fixup( "yes" ) );
        $this->assertEquals( "y", $validator->fixup( "1" ) );
        $this->assertEquals( "YES", $validator->fixup( "YES" ) );
        
        $this->assertEquals( "n", $validator->fixup( "no" ) );
        $this->assertEquals( "n", $validator->fixup( "0" ) );
        $this->assertEquals( "NO", $validator->fixup( "NO" ) );

        $validator->conversion = ezcConsoleQuestionDialogMappingValidator::CONVERT_UPPER;

        $this->assertEquals( "YES", $validator->fixup( "yes" ) );
        $this->assertEquals( "y", $validator->fixup( "1" ) );
        $this->assertEquals( "YES", $validator->fixup( "YES" ) );
        
        $this->assertEquals( "NO", $validator->fixup( "no" ) );
        $this->assertEquals( "n", $validator->fixup( "0" ) );
        $this->assertEquals( "NO", $validator->fixup( "NO" ) );

        $validator->conversion = ezcConsoleQuestionDialogMappingValidator::CONVERT_LOWER;
        
        $this->assertEquals( "y", $validator->fixup( "yes" ) );
        $this->assertEquals( "y", $validator->fixup( "1" ) );
        $this->assertEquals( "y", $validator->fixup( "YES" ) );
        
        $this->assertEquals( "n", $validator->fixup( "no" ) );
        $this->assertEquals( "n", $validator->fixup( "0" ) );
        $this->assertEquals( "n", $validator->fixup( "NO" ) );

        $this->assertEquals( "", $validator->fixup( "" ) );

        $validator->default = "no";

        $this->assertEquals( "no", $validator->fixup( "" ) );
    }

    public function testGetResultString()
    {
        $collection = array( "foo", "bar", "baz" );
        $validator = new ezcConsoleQuestionDialogMappingValidator(
            $collection,
            null,
            ezcConsoleQuestionDialogMappingValidator::CONVERT_NONE,
            array( 'f' => 'foo', 'b' => 'bar' )
        );

        $this->assertEquals( "(foo/bar/baz)", $validator->getResultString() );

        $validator->default = "foo";

        $this->assertEquals( "(foo/bar/baz) [foo]", $validator->getResultString() );
    }
}

?>
