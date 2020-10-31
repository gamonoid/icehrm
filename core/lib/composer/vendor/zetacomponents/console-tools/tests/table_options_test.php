<?php
/**
 * ezcConsoleTableOptionsTest class.
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
 * Test suite for ezcConsoleTableOptions struct.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleTableOptionsTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleTableOptionsTest" );
	}

    /**
     * testConstructor
     * 
     * @access public
     */
    public function testConstructor()
    {
        $fake = new ezcConsoleTableOptions( 
            "auto",
            ezcConsoleTable::WRAP_AUTO,
            ezcConsoleTable::ALIGN_LEFT,
            " ",
            ezcConsoleTable::WIDTH_MAX,
            "-",
            "|",
            "+",
            "default",
            "default"
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleTableOptions(),
            'Default values incorrect for ezcConsoleTableOptions.'
        );
    }
    
    /**
     * testConstructorNew
     * 
     * @access public
     */
    public function testConstructorNew()
    {
        $fake = new ezcConsoleTableOptions( 
            array(
                "colWidth" => "auto",
                "colWrap" => ezcConsoleTable::WRAP_AUTO,
                "defaultAlign" => ezcConsoleTable::ALIGN_LEFT,
                "colPadding" => " ",
                "widthType" => ezcConsoleTable::WIDTH_MAX,
                "lineVertical" => "-",
                "lineHorizontal" => "|",
                "corner" => "+",
                "defaultFormat" => "default",
                "defaultBorderFormat" => "default"
            )
        );
        $this->assertEquals( 
            $fake,
            new ezcConsoleTableOptions(),
            'Default values incorrect for ezcConsoleTableOptions.'
        );
    }

    public function testCompatibility()
    {
        $old = new ezcConsoleTableOptions( 
            array( 10, 20, 10 ),
            ezcConsoleTable::WRAP_CUT,
            ezcConsoleTable::ALIGN_CENTER,
            "-",
            ezcConsoleTable::WIDTH_FIXED,
            "_",
            "I",
            "x",
            "red",
            "blue"
        );
        $new = new ezcConsoleTableOptions( 
            array(
                "colWidth" => array( 10, 20, 10 ),
                "colWrap" => ezcConsoleTable::WRAP_CUT,
                "defaultAlign" => ezcConsoleTable::ALIGN_CENTER,
                "colPadding" => "-",
                "widthType" => ezcConsoleTable::WIDTH_FIXED,
                "lineVertical" => "_",
                "lineHorizontal" => "I",
                "corner" => "x",
                "defaultFormat" => "red",
                "defaultBorderFormat" => "blue"
            )
        );
        $this->assertEquals( $old, $new, "Old construction method did not produce same result as old one." );
    }

    public function testAccess()
    {
        $opt = new ezcConsoleTableOptions();

        $this->assertEquals( $opt->colWidth, "auto" );
        $this->assertEquals( $opt->colWrap, ezcConsoleTable::WRAP_AUTO );
        $this->assertEquals( $opt->defaultAlign, ezcConsoleTable::ALIGN_LEFT );
        $this->assertEquals( $opt->colPadding, " " );
        $this->assertEquals( $opt->widthType, ezcConsoleTable::WIDTH_MAX );
        $this->assertEquals( $opt->lineVertical, "-" );
        $this->assertEquals( $opt->lineHorizontal, "|" );
        $this->assertEquals( $opt->corner, "+" );
        $this->assertEquals( $opt->defaultFormat, "default" );
        $this->assertEquals( $opt->defaultBorderFormat, "default" );

        $this->assertEquals( $opt["colWidth"], "auto" );
        $this->assertEquals( $opt["colWrap"], ezcConsoleTable::WRAP_AUTO );
        $this->assertEquals( $opt["defaultAlign"], ezcConsoleTable::ALIGN_LEFT );
        $this->assertEquals( $opt["colPadding"], " " );
        $this->assertEquals( $opt["widthType"], ezcConsoleTable::WIDTH_MAX );
        $this->assertEquals( $opt["lineVertical"], "-" );
        $this->assertEquals( $opt["lineHorizontal"], "|" );
        $this->assertEquals( $opt["corner"], "+" );
        $this->assertEquals( $opt["defaultFormat"], "default" );
        $this->assertEquals( $opt["defaultBorderFormat"], "default" );
    }

    public function testConstructorFirstParameter()
    {
        $colWidthArray = new ezcConsoleTableOptions(
            array( 1, 2, 3 )
        );

        $optionsArray = new ezcConsoleTableOptions(
            array(
                "colWidth" => array( 1, 2, 3 ),
            )
        );

        $this->assertEquals( $colWidthArray, $optionsArray, "Did not detect options array correctly." );
    }

    public function testTableConstructorCompatibility()
    {
        $out = new ezcConsoleOutput();
        $old = new ezcConsoleTable(
            $out,
            100,
            new ezcConsoleTableOptions(
                array( 1, 2, 3 )
            )
        );
        $new = new ezcConsoleTable(
            $out,
            100,
            array(
                "colWidth" => array( 1, 2, 3 ),
            )
        );
        $this->assertEquals( $old, $new, "Constructor calls did not produce same table objects." );
    }

    public function testGetAccessSuccess()
    {
        $opt = new ezcConsoleTableOptions();
        $this->assertEquals( $opt->colWidth, "auto" );
        $this->assertEquals( $opt->colWrap, ezcConsoleTable::WRAP_AUTO );
        $this->assertEquals( $opt->defaultAlign, ezcConsoleTable::ALIGN_LEFT );
        $this->assertEquals( $opt->colPadding, " " );
        $this->assertEquals( $opt->widthType, ezcConsoleTable::WIDTH_MAX );
        $this->assertEquals( $opt->lineVertical, "-" );
        $this->assertEquals( $opt->lineHorizontal, "|" );
        $this->assertEquals( $opt->corner, "+" );
        $this->assertEquals( $opt->defaultFormat, "default" );
        $this->assertEquals( $opt->defaultBorderFormat, "default" );
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcConsoleTableOptions();

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
        $opt = new ezcConsoleTableOptions();
        $opt->colWidth = 10;
        $opt->colWrap = ezcConsoleTable::WRAP_NONE;
        $opt->defaultAlign = ezcConsoleTable::ALIGN_CENTER;
        $opt->colPadding = "--";
        $opt->widthType = ezcConsoleTable::WIDTH_FIXED;
        $opt->lineVertical = "_";
        $opt->lineHorizontal = "/";
        $opt->corner = "o";
        $opt->defaultFormat = "foo";
        $opt->defaultBorderFormat = "bar";

        $this->assertEquals( $opt->colWidth, 10 );
        $this->assertEquals( $opt->colWrap, ezcConsoleTable::WRAP_NONE );
        $this->assertEquals( $opt->defaultAlign, ezcConsoleTable::ALIGN_CENTER );
        $this->assertEquals( $opt->colPadding, "--" );
        $this->assertEquals( $opt->widthType, ezcConsoleTable::WIDTH_FIXED );
        $this->assertEquals( $opt->lineVertical, "_" );
        $this->assertEquals( $opt->lineHorizontal, "/" );
        $this->assertEquals( $opt->corner, "o" );
        $this->assertEquals( $opt->defaultFormat, "foo" );
        $this->assertEquals( $opt->defaultBorderFormat, "bar" );
    }

    public function testSetAccessFailure()
    {
        $opt = new ezcConsoleTableOptions();

        $exceptionThrown = false;
        try
        {
            $opt->colWidth = "foo";
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property colWidth." );

        $exceptionThrown = false;
        try
        {
            $opt->colWrap = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property colWrap." );

        $exceptionThrown = false;
        try
        {
            $opt->defaultAlign = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property defaultAlign." );

        $exceptionThrown = false;
        try
        {
            $opt->colPadding = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property colPadding." );

        $exceptionThrown = false;
        try
        {
            $opt->widthType = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property widthType." );

        $exceptionThrown = false;
        try
        {
            $opt->lineVertical = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property lineVertical." );

        $exceptionThrown = false;
        try
        {
            $opt->lineHorizontal = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property lineHorizontal." );

        $exceptionThrown = false;
        try
        {
            $opt->corner = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property corner." );

        $exceptionThrown = false;
        try
        {
            $opt->defaultFormat = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property defaultFormat." );

        $exceptionThrown = false;
        try
        {
            $opt->defaultBorderFormat = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property defaultBorderFormat." );
        
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
        $opt = new ezcConsoleTableOptions();
        $this->assertTrue( isset( $opt->colWidth ) );
        $this->assertTrue( isset( $opt->colWrap ) );
        $this->assertTrue( isset( $opt->defaultAlign ) );
        $this->assertTrue( isset( $opt->colPadding ) );
        $this->assertTrue( isset( $opt->widthType ) );
        $this->assertTrue( isset( $opt->lineVertical ) );
        $this->assertTrue( isset( $opt->lineHorizontal ) );
        $this->assertTrue( isset( $opt->corner ) );
        $this->assertTrue( isset( $opt->defaultFormat ) );
        $this->assertTrue( isset( $opt->defaultBorderFormat ) );
        $this->assertFalse( isset( $opt->foo ) );
    }
}

?>
