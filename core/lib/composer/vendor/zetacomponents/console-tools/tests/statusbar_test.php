<?php
/**
 * ezcConsoleOutputTest class.
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
 * Test suite for ezcConsoleStatusbar class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleStatusbarTest extends ezcTestCase
{
    private $stati = array( 
        true,
        false,
        true,
        true,
        false,
        true,
        true,
        true,
        false,
        false,
        true,
        true,
        false,
        true,
        true,
        false,
        true,
        false,
        true,
        true,
        false,
        false,
        false,
        true,
        false,
    );

    private $errorCaught = false;

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleStatusbarTest" );
	}

    public function testStatusbar1()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );
        ob_start();
        foreach ( $this->stati as $statusVal )
        {
            $status->add($statusVal);
        }
        $res = ob_get_contents();
        ob_end_clean();
        $this->assertEquals(
            file_get_contents( dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . 'testStatusbar1.dat' ),
            $res,
            "Formated statusbar not generated correctly."
        );
        // To prepare test files use this:
        // file_put_contents( dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . 'testStatusbar1.dat', $res );
    }
    
    public function testStatusbar2()
    {
        $out = new ezcConsoleOutput();
        $out->options->useFormats = false;
        $status = new ezcConsoleStatusbar( $out );
        ob_start();
        foreach ( $this->stati as $statusVal )
        {
            $status->add($statusVal);
        }
        $res = ob_get_contents();
        ob_end_clean();
        $this->assertEquals(
            file_get_contents( dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . 'testStatusbar2.dat' ),
            $res,
            "Unformated statusbar not generated correctly."
        );
        // To prepare test files use this:
        // file_put_contents( dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . 'testStatusbar2.dat', $res );
    }

    public function testGetAccessSuccess()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $this->assertEquals( new ezcConsoleStatusbarOptions(), $status->options );
        $this->assertEquals( "+", $status->successChar );
        $this->assertEquals( "-", $status->failureChar );
    }

    public function testGetAccessFailure()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        try
        {
            echo $status->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on get access to invalid property ezcConsoleStatusbar->foo." );
    }

    public function testSetAccessSuccess()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $refOpt = new ezcConsoleStatusbarOptions();
        $refOpt->successChar = "*";
        $refOpt->failureChar = "#";

        $status->successChar = "*";
        $status->failureChar = "#";

        $this->assertEquals( $refOpt, $status->options, "Options not set correctly through direct set access on ezcConsoleStatusbar." );

        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $status->options = $refOpt;

        $this->assertSame( $refOpt, $status->options, "Property ezcConsoleStatusbar->options not set correctly." );
    }

    public function testSetAccessFailure()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $exceptionThrown = false;
        try
        {
            $status->successChar = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value for property ezcConsoleStatusbar->successChar." );

        $exceptionThrown = false;
        try
        {
            $status->failureChar = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value for property ezcConsoleStatusbar->failureChar." );

        $exceptionThrown = false;
        try
        {
            $status->options = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value for property ezcConsoleStatusbar->options." );

        $exceptionThrown = false;
        try
        {
            $status->foo = 23;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBasePropertyNotFoundException not thrown on set access of invalid property ezcConsoleStatusbar->foo." );
    }

    public function testIssetAccess()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $this->assertTrue( isset( $status->successChar ) );
        $this->assertTrue( isset( $status->failureChar ) );
        $this->assertTrue( isset( $status->options ) );
        $this->assertFalse( isset( $status->foo ) );
    }

    public function testSetOptionsSuccess()
    {
        $refOpt = new ezcConsoleStatusbarOptions();
        $refOpt->successChar = "*";
        $refOpt->failureChar = "#";

        $optArr = array(
            "successChar" => "*",
            "failureChar" => "#",
        );

        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $status->setOptions( $optArr );

        $this->assertEquals( $refOpt, $status->options, "Options not correctly set from array." );

        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $status->setOptions( $refOpt );

        $this->assertSame( $refOpt, $status->options, "Options not correctly set from array." );
    }

    public function testSetOptionsFailure()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        try
        {
            $status->setOptions( 23 );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "ezcBaseValueException not thrown on invalid parameter to ezcConsoleStatusbar->setOptions()." );
    }

    public function testGetOptions()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $this->assertEquals( new ezcConsoleStatusbarOptions(), $status->getOptions() );
    }

    public function testReset()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );
        
        ob_start();
        foreach ( $this->stati as $statusVal )
        {
            $status->add($statusVal);
        }
        ob_end_clean();

        $counter = $this->readAttribute( $status, "counter" );
        $this->assertEquals( 14, $counter[true], "Success values not counted correctly." );
        $this->assertEquals( 11, $counter[false], "Failure values not counted correctly." );
        
        $status->reset();
        $counter = $this->readAttribute( $status, "counter" );
        $this->assertEquals( 0, $counter[true], "Success values not reset correctly." );
        $this->assertEquals( 0, $counter[false], "Failure values not reset correctly." );
    }

    public function testGetSuccessCount()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $this->assertEquals( 0, $status->getSuccessCount() );
        
        ob_start();
        foreach ( $this->stati as $statusVal )
        {
            $status->add($statusVal);
        }
        ob_end_clean();
        
        $this->assertEquals( 14, $status->getSuccessCount() );
    }

    public function testGetFailureCount()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        $this->assertEquals( 0, $status->getFailureCount() );
        
        ob_start();
        foreach ( $this->stati as $statusVal )
        {
            $status->add($statusVal);
        }
        ob_end_clean();
        
        $this->assertEquals( 11, $status->getFailureCount() );
    }

    public function testAddIncorrectStatus()
    {
        $out = new ezcConsoleOutput();
        $status = new ezcConsoleStatusbar( $out );

        set_error_handler( array( $this, "catchWarning" ), E_USER_WARNING );

        ob_start();
        $status->add( "foo" );
        ob_end_clean();

        restore_error_handler();

        $this->assertTrue( $this->errorCaught, "Warning not caught on invalid status 'foo'." );
    }

    public function catchWarning( $errno, $errstr, $errfile, $errline, $errcontext )
    {
        $this->assertEquals( "Unknown status 'foo'.", $errstr );
        $this->assertEquals( realpath( dirname( __FILE__ ) . "/../src/statusbar.php" ), $errfile );
        $this->errorCaught = true;
    }
}
?>
