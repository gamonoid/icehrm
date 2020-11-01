<?php
/**
 * ezcConsoleStatusbarOptionsTest class.
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
 * Test suite for ezcConsoleStatusbarOptions struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleStatusbarOptionsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleStatusbarOptionsTest" );
	}
    
    /**
     * testConstructorNew
     * 
     * @access public
     */
    public function testConstructorNew()
    {
        $fake = new ezcConsoleStatusbarOptions(
            array( 
                "successChar" => "+",
                "failureChar" => "-",
            )
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleStatusbarOptions(),
            'Default values incorrect for ezcConsoleStatusbarOptions.'
        );
    }

    public function testNewAccess()
    {
        $opt = new ezcConsoleStatusbarOptions();
        $this->assertEquals( "+", $opt->successChar );
        $this->assertEquals( "-", $opt->failureChar );

        $this->assertEquals( $opt["successChar"], "+" );
        $this->assertEquals( $opt["failureChar"], "-" );
    }

    public function testGetAccessSuccess()
    {
        $opt = new ezcConsoleStatusbarOptions();
        $this->assertEquals( "+", $opt->successChar );
        $this->assertEquals( "-", $opt->failureChar );
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcConsoleStatusbarOptions();

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
        $opt = new ezcConsoleStatusbarOptions();
        $opt->successChar = "1";
        $opt->failureChar = "0";

        $this->assertEquals( "1", $opt->successChar );
        $this->assertEquals( "0", $opt->failureChar );
    }

    public function testSetAccessFailure()
    {
        $opt = new ezcConsoleStatusbarOptions();

        $exceptionThrown = false;
        try
        {
            $opt->successChar = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property successChar." );

        $exceptionThrown = false;
        try
        {
            $opt->failureChar = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property failureChar." );

        $exceptionThrown = false;
        try
        {
            $opt->foo = true;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on set access of invalid property foo." );
    }

    public function testIsset()
    {
        $opt = new ezcConsoleStatusbarOptions();
        $this->assertTrue( isset( $opt->successChar ) );
        $this->assertTrue( isset( $opt->failureChar ) );
        $this->assertFalse( isset( $opt->foo ) );
    }
}

?>
