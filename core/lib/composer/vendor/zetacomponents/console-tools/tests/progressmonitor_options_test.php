<?php
/**
 * ezcConsoleProgressMonitorOptionsTest class.
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
 * Test suite for ezcConsoleProgressMonitorOptions struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleProgressMonitorOptionsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleProgressMonitorOptionsTest" );
	}
    
    /**
     * testConstructorNew
     * 
     * @access public
     */
    public function testConstructorNew()
    {
        $fake = new ezcConsoleProgressMonitorOptions(
            array( 
                "formatString" => "%8.1f%% %s %s",
            )
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleProgressMonitorOptions(),
            'Default values incorrect for ezcConsoleProgressMonitorOptions.'
        );
    }

    public function testNewAccess()
    {
        $opt = new ezcConsoleProgressMonitorOptions();

        $this->assertEquals( $opt["formatString"], "%8.1f%% %s %s" );
    }

    public function testGetAccessSuccess()
    {
        $opt = new ezcConsoleProgressMonitorOptions();
        $this->assertEquals( "%8.1f%% %s %s", $opt->formatString );
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcConsoleProgressMonitorOptions();

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
        $opt = new ezcConsoleProgressMonitorOptions();
        $opt->formatString = "foo %s";

        $this->assertEquals( "foo %s", $opt->formatString );
    }

    public function testSetAccessFailure()
    {
        $opt = new ezcConsoleProgressMonitorOptions();

        $exceptionThrown = false;
        try
        {
            $opt->formatString = "";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property formatString." );

        $exceptionThrown = false;
        try
        {
            $opt->formatString = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property formatString." );

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
        $opt = new ezcConsoleProgressMonitorOptions();
        $this->assertTrue( isset( $opt->formatString ) );
        $this->assertFalse( isset( $opt->foo ) );
    }
}

?>
