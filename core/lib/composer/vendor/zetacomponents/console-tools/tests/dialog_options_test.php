<?php
/**
 * ezcConsoleDialogOptionsTest class.
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
 * Test suite for ezcConsoleDialogOptions class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleDialogOptionsTest extends ezcTestCase
{
	public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( "ezcConsoleDialogOptionsTest" );
    }

    public function testGetAccessDefaultSuccess()
    {
        $opts = new ezcConsoleDialogOptions();
        $this->assertNull( $opts->validator );
        $this->assertEquals( "default", $opts->format );
    }

    public function testGetAccessCustomSuccess()
    {
        $opts = new ezcConsoleDialogOptions(
            array(
                "validator"         => new ezcConsoleQuestionDialogCollectionValidator( array( "a", "b" ) ),
                "format"            => "test",
            )
        );
        $this->assertInstanceOf( "ezcConsoleQuestionDialogCollectionValidator", $opts->validator );
        $this->assertEquals( "test", $opts->format );
    }

    public function testGetAccessFailure()
    {
        $opts = new ezcConsoleDialogOptions();
        
        try
        {
            echo $opts->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $opts = new ezcConsoleDialogOptions();
        $opts->validator   = new ezcConsoleQuestionDialogCollectionValidator( array( "a", "b" ) );
        $opts->format      = "test";
        
        $this->assertInstanceOf( "ezcConsoleQuestionDialogCollectionValidator", $opts->validator );
        $this->assertEquals( "test", $opts->format );
    }

    public function testSetAccessFailure()
    {
        $opts = new ezcConsoleDialogOptions();
        
        $exceptionCaught = false;
        try
        {
            $opts->validator = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property validator." );

        $exceptionCaught = false;
        try
        {
            $opts->format = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on invalid value for property format." );

        $exceptionCaught = false;
        try
        {
            $opts->foo = "Foo";
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionCaught = true;
        }
        $this->assertTrue( $exceptionCaught, "Exception not thrown on access of nonexistent property foo." );
    }

    public function testIssetAccess()
    {
        $opts = new ezcConsoleDialogOptions();
        $this->assertTrue( isset( $opts->validator ), "Property validator not set." );
        $this->assertTrue( isset( $opts->format ), "Property format not set." );

        $this->assertFalse( isset( $opts->foo ), "Property foo set." );
    }
}

?>
