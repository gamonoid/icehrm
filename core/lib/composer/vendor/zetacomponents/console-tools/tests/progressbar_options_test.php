<?php
/**
 * ezcConsoleProgressbarOptionsTest class.
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
 * Test suite for ezcConsoleProgressbarOptions struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleProgressbarOptionsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleProgressbarOptionsTest" );
	}
    
    /**
     * testConstructorNew
     * 
     * @access public
     */
    public function testConstructorNew()
    {
        $fake = new ezcConsoleProgressbarOptions(
            array( 
                "barChar" => "+",
                "emptyChar" => "-",
                "formatString" => "%act% / %max% [%bar%] %fraction%%",
                "fractionFormat" => "%01.2f",
                "progressChar" => ">",
                "redrawFrequency" => 1,
                "step" => 1,
                "width" => 78,
                "actFormat" => "%.0f",
                "maxFormat" => "%.0f",
                "minVerbosity" => 1,
                "maxVerbosity" => false,
            )
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleProgressbarOptions(),
            'Default values incorrect for ezcConsoleProgressbarOptions.'
        );
    }

    public function testNewAccess()
    {
        $opt = new ezcConsoleProgressbarOptions();
        $this->assertEquals( "+", $opt->barChar );
        $this->assertEquals( "-", $opt->emptyChar );
        $this->assertEquals( "%act% / %max% [%bar%] %fraction%%", $opt->formatString );
        $this->assertEquals( "%01.2f", $opt->fractionFormat );
        $this->assertEquals( ">", $opt->progressChar );
        $this->assertEquals( 1, $opt->redrawFrequency );
        $this->assertEquals( 1, $opt->step );
        $this->assertEquals( 78, $opt->width );
        $this->assertEquals( "%.0f", $opt->actFormat );
        $this->assertEquals( "%.0f", $opt->maxFormat );
        $this->assertEquals( 1, $opt->minVerbosity );
        $this->assertEquals( false, $opt->maxVerbosity );

        $this->assertEquals( $opt["barChar"], "+" );
        $this->assertEquals( $opt["emptyChar"], "-" );
        $this->assertEquals( $opt["formatString"], "%act% / %max% [%bar%] %fraction%%" );
        $this->assertEquals( $opt["fractionFormat"], "%01.2f" );
        $this->assertEquals( $opt["progressChar"], ">" );
        $this->assertEquals( $opt["redrawFrequency"], 1 );
        $this->assertEquals( $opt["step"], 1 );
        $this->assertEquals( $opt["width"], 78 );
        $this->assertEquals( $opt["actFormat"], "%.0f" );
        $this->assertEquals( $opt["maxFormat"], "%.0f" );
        $this->assertEquals( $opt["minVerbosity"], 1 );
        $this->assertEquals( $opt["maxVerbosity"], false );
    }

    public function testGetAccessSuccess()
    {
        $opt = new ezcConsoleProgressbarOptions();
        $this->assertEquals( "+", $opt->barChar );
        $this->assertEquals( "-", $opt->emptyChar );
        $this->assertEquals( "%act% / %max% [%bar%] %fraction%%", $opt->formatString );
        $this->assertEquals( "%01.2f", $opt->fractionFormat );
        $this->assertEquals( ">", $opt->progressChar );
        $this->assertEquals( 1, $opt->redrawFrequency );
        $this->assertEquals( 1, $opt->step );
        $this->assertEquals( 78, $opt->width );
        $this->assertEquals( '%.0f', $opt->actFormat );
        $this->assertEquals( '%.0f', $opt->maxFormat );
        $this->assertEquals( 1, $opt->minVerbosity );
        $this->assertEquals( false, $opt->maxVerbosity );
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcConsoleProgressbarOptions();

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
        $opt = new ezcConsoleProgressbarOptions();
        $opt->barChar = "*";
        $opt->emptyChar = "_";
        $opt->formatString = " %fraction%% %act% - %max% (%bar%)";
        $opt->fractionFormat = "%01.1f";
        $opt->progressChar = "]";
        $opt->redrawFrequency = 5;
        $opt->step = 5;
        $opt->width = 42;
        $opt->actFormat = '%.10f';
        $opt->maxFormat = '%.10f';
        $opt->minVerbosity = 23;
        $opt->maxVerbosity = 42;

        $this->assertEquals( "*", $opt->barChar );
        $this->assertEquals( "_", $opt->emptyChar );
        $this->assertEquals( " %fraction%% %act% - %max% (%bar%)", $opt->formatString );
        $this->assertEquals( "%01.1f", $opt->fractionFormat );
        $this->assertEquals( "]", $opt->progressChar );
        $this->assertEquals( 5, $opt->redrawFrequency );
        $this->assertEquals( 5, $opt->step );
        $this->assertEquals( 42, $opt->width );
        $this->assertEquals( '%.10f', $opt->actFormat );
        $this->assertEquals( '%.10f', $opt->maxFormat );
        $this->assertEquals( 23, $opt->minVerbosity );
        $this->assertEquals( 42, $opt->maxVerbosity );
    }

    public function testSetAccessFailure()
    {
        $opt = new ezcConsoleProgressbarOptions();

        $exceptionThrown = false;
        try
        {
            $opt->fractionFormat = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property fractionFormat." );

        $exceptionThrown = false;
        try
        {
            $opt->progressChar = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property progressChar." );

        $exceptionThrown = false;
        try
        {
            $opt->redrawFrequency = "foo";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property redrawFrequency." );

        $exceptionThrown = false;
        try
        {
            $opt->step = "foo";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property step." );

        $exceptionThrown = false;
        try
        {
            $opt->width = "foo";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property width." );

        $exceptionThrown = false;
        try
        {
            $opt->actFormat = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property actFormat." );

        $exceptionThrown = false;
        try
        {
            $opt->maxFormat = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property maxFormat." );

        $exceptionThrown = false;
        try
        {
            $opt->barChar = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property barChar." );

        $exceptionThrown = false;
        try
        {
            $opt->emptyChar = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property emptyChar." );

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
            $opt->minVerbosity = 'some val';
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property minVerbosity." );

        $exceptionThrown = false;
        try
        {
            $opt->maxVerbosity = 'some val';
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property maxVerbosity." );

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
        $opt = new ezcConsoleProgressbarOptions();
        $this->assertTrue( isset( $opt->barChar ) );
        $this->assertTrue( isset( $opt->emptyChar ) );
        $this->assertTrue( isset( $opt->formatString ) );
        $this->assertTrue( isset( $opt->fractionFormat ) );
        $this->assertTrue( isset( $opt->progressChar ) );
        $this->assertTrue( isset( $opt->redrawFrequency ) );
        $this->assertTrue( isset( $opt->step ) );
        $this->assertTrue( isset( $opt->width ) );
        $this->assertTrue( isset( $opt->actFormat ) );
        $this->assertTrue( isset( $opt->maxFormat ) );
        $this->assertTrue( isset( $opt->minVerbosity ) );
        $this->assertTrue( isset( $opt->maxVerbosity ) );
        $this->assertFalse( isset( $opt->foo ) );
    }
}

?>
