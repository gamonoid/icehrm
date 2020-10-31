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
 * Test suite for ezcConsoleTable class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleTableTest extends ezcTestCase
{
    private $tableData1 = array( 
        array( 'Heading no. 1', 'Longer heading no. 2', 'Head 3' ),
        array( 'Data cell 1', 'Data cell 2', 'Data cell 3' ),
        array( 'Long long data cell with even more text in it...', 'Data cell 4', 'Data cell 5' ),
        array( 'a b c d e f g h i j k l m n o p q r s t u v w x ', 'Data cell', 'Data cell' ),
    );

    private $tableData2 = array( 
        array( 'a', 'b', 'c', 'd', 'e', 'f' ),
        array( 'g', 'h', 'i', 'j', 'k', 'l' ),
    );

    private $tableData3 = array( 
        array( 'Parameter', 'Shortcut', 'Descrition' ),
        array( 'Append text to a file. This parameter takes a string value and may be used multiple times.', '--append', '-a' ),
        array( 'Prepend text to a file. This parameter takes a string value and may be used multiple times.', '--prepend', '-p' ),
        array( 'Forces the action desired without paying attention to any errors.', '--force', '-f' ),
        array( 'Silence all kinds of warnings issued by this program.', '--silent', '-s' ),
    );
    
    private $tableData4 = array( 
        array( 'Some very very long data here.... and it becomes even much much longer... and even longer....', 'Short', 'Some very very long data here.... and it becomes even much much longer... and even longer....', 'Short' ),
        array( 'Short', "Some very very long data here....\n\nand it becomes even much much longer...\n\nand even longer....", 'Short', 'Some very very long data here.... and it becomes even much much longer... and even longer....' ),
    );

    private $tableData5 = array(
        array( 'Short text', 'More short text' ),
        array( "Short text\nShort text\nShort text\nShort text\nShort text\nShort text\nShort text\n", "More short text\nMore short text\nMore short text\n     Short text" )
    );

    private $tableData6 = array(
        array( 'Non UTF-8 column 1', 'Non UTF-8 column 2', 'Long long long long long long long non UTF-8 column' ),
        array( 'Nön UTF-8 cölümn 1', 'Nön UTF-8 cölümn 2', 'Löng löng löng löng löng löng löng nön UTF-8 cölümn' ),
    );

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleTableTest" );
	}

    protected function setUp()
    {
        $this->output = new ezcConsoleOutput();
        $formats = array(
            'red' => array( 
                'color' => 'red',
                'style' => 'bold'
            ),
            'blue' => array( 
                'color' => 'blue',
                'style' => 'bold'
            ),
            'green' => array( 
                'color' => 'green',
                'style' => 'bold'
            ),
            'magenta' => array( 
                'color' => 'magenta',
                'style' => 'bold'
            ),
        );
        foreach ( $formats as $name => $format )
        {
            foreach ( $format as $type => $val )
            {
                $this->output->formats->$name->$type = $val;
            }
        }
    }

    public function testTable1a()
    {
        $this->commonTableTest(
            __FUNCTION__, 
            $this->tableData1,
            array( 'cols' => count( $this->tableData1[0] ), 'width' => 80 ),
            array( 'lineFormatHead' => 'green' ),
            array( 0 )
        );
    }
    
    public function testTable1b()
    {
        $this->commonTableTest(
            __FUNCTION__, 
            $this->tableData1,
            array( 'cols' => count( $this->tableData1[0] ), 'width' => 40 ),
            array( 'lineFormatHead' => 'red',  ),
            array( 0 )
        );
    }
    
    public function testTable2a()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData2,
            array( 'cols' => count( $this->tableData2[0] ), 'width' =>  60 ),
            array( 'lineFormatHead' => 'magenta', 'defaultAlign' => ezcConsoleTable::ALIGN_RIGHT, 'widthType' => ezcConsoleTable::WIDTH_FIXED )
        );
    }
    
    public function testTable2b()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData2,
            array( 'cols' => count( $this->tableData2[0] ), 'width' =>  60 ),
            array( 'lineFormatHead' => 'magenta', 'defaultAlign' => ezcConsoleTable::ALIGN_RIGHT )
        );
    }
   
    // Bug #8738: Unexpected behaviour with options->colPadding
    public function testTableColPadding1()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData2,
            array( 'width' =>  100 ),
            array( 'defaultAlign' => ezcConsoleTable::ALIGN_CENTER, 'colPadding' => '~~~', 'widthType' => ezcConsoleTable::WIDTH_FIXED )
        );
    }
    
    // Bug #8738: Unexpected behaviour with options->colPadding
    public function testTableColPadding2()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData2,
            array( 'width' =>  100 ),
            array( 'defaultAlign' => ezcConsoleTable::ALIGN_CENTER, 'colPadding' => '~~~' )
        );
    }
    
    public function testTable3a()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData3,
            array( 'cols' => count( $this->tableData3[0] ), 'width' =>  120 ),
            array( 'lineFormatHead' => 'blue', 'defaultAlign' => ezcConsoleTable::ALIGN_CENTER, 'lineVertical' => '#', 'lineHorizontal' => '#', 'corner' => '#' ),
            array( 0, 3 )
        );
    }
    
    public function testTable3b()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData3,
            array( 'cols' => count( $this->tableData3[0] ), 'width' =>  80 ),
            array( 'lineFormatHead' => 'magenta', 'lineVertical' => 'v', 'lineHorizontal' => 'h', 'corner' => 'c' ),
            array( 1, 2 )
        );
    }
    
    public function testTable3c()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData3,
            array( 'cols' => count( $this->tableData3[0] ), 'width' =>  30 ),
            array( 'lineFormatHead' => 'magenta', 'colWrap' => ezcConsoleTable::WRAP_NONE ),
            array( 1, 2 )
        );
    }
    
    public function testTable3d()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData3,
            array( 'cols' => count( $this->tableData3[0] ), 'width' =>  9 ),
            array( 'lineFormatHead' => 'magenta', 'colWrap' => ezcConsoleTable::WRAP_CUT, 'colWidth' => array( 3, 3, 3 ) ),
            array( 1, 2 )
        );
    }
     
    public function testTable4a()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineFormatHead' => 'blue', 'defaultAlign' => ezcConsoleTable::ALIGN_CENTER, 'colWrap' => ezcConsoleTable::WRAP_CUT ),
            array( 0 )
        );
    }
    
    public function testTable4b()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineFormatHead' => 'blue', 'defaultAlign' => ezcConsoleTable::ALIGN_LEFT, 'colWrap' => ezcConsoleTable::WRAP_AUTO ),
            array( 0 )
        );
    }
    
    public function testTable4c()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineFormatHead' => 'blue', 'defaultAlign' => ezcConsoleTable::ALIGN_CENTER, 'colWrap' => ezcConsoleTable::WRAP_CUT ),
            array( 0 )
        );
    }
    
    public function testTable5autowidth()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData5,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'widthType' => ezcConsoleTable::WIDTH_MAX ),
            array()
        );
    }
    
    public function testTableWithoutBorders()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineVertical' => null, 'lineHorizontal' => null, 'corner' => null ),
            array( 0 )
        );
    }
    
    public function testTableWithSpaceBorders()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineVertical' => ' ', 'lineHorizontal' => ' ', 'corner' => ' ' ),
            array( 0 )
        );
    }
    
    public function testTableWithoutVerticalBorders()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineVertical' => null ),
            array( 0 )
        );
    }
    
    public function testTableWithoutHorizontalBorders()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData4,
            array( 'cols' => count( $this->tableData4[0] ), 'width' =>  120 ),
            array( 'lineHorizontal' => null ),
            array( 0 )
        );
    }

    public function testEmptyTable()
    {
        $out = new ezcConsoleOutput();
        $tbl = new ezcConsoleTable( $out, 80 );

        $this->assertTableOutputEquals(
            __FUNCTION__,
            (string) $tbl
        );
    }

    public function testEmptyTableNoBorders()
    {
        $out = new ezcConsoleOutput();
        $tbl = new ezcConsoleTable( $out, 80 );

        $tbl->options->lineVertical   = null;
        $tbl->options->lineHorizontal = null;
        $tbl->options->corner         = null;

        $this->assertEquals(
            '',
            (string) $tbl
        );
    }

    public function testOneRowOneColumnTable()
    {
        $out = new ezcConsoleOutput();
        $tbl = new ezcConsoleTable( $out, 80 );
        $tbl[0][0]->content = 'foo';

        $this->assertTableOutputEquals(
            __FUNCTION__,
            (string) $tbl
        );
    }

    public function testUtf8TableHighlightNonUtf8()
    {
        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData6,
            array( 'cols' => count( $this->tableData6[0] ), 'width' =>  80 ),
            array( 'lineFormatHead' => 'red' ),
            array( 0 )
        );
    }

    public function testUtf8TableHighlightUtf8()
    {
        $data = array(
            0 => $this->tableData6[1],
            1 => $this->tableData6[0],
        );
        $this->commonTableTest(
            __FUNCTION__,
            $data,
            array( 'cols' => count( $data[0] ), 'width' =>  80 ),
            array( 'lineFormatHead' => 'red' ),
            array( 0 )
        );
    }

    public function testTableWithoutFormatting()
    {
        $this->output->options->useFormats = false;

        $this->commonTableTest(
            __FUNCTION__,
            $this->tableData2,
            array( 'cols' => count( $this->tableData2[0] ), 'width' => 80 ),
            array()
        );
    }
    
    public function testTableConfigurationFailure1 ()
    {
        // Missing 'cols' setting
        try
        {
            $table = new ezcConsoleTable( $this->output, null );
        }
        catch (ezcBaseValueException $e)
        {
            $this->assertTrue( 
                true,
                'Wrong exception code thrown on missing <cols> setting.'
            );
            return;
        }
        $this->fail( 'No or wrong exception thrown on missing <cols> setting.' );
    }
    
    public function testTableConfigurationFailure2 ()
    {
        // 'cols' setting wrong type
        try
        {
            $table = new ezcConsoleTable( $this->output, 'test' );
        }
        catch (ezcBaseValueException $e)
        {
            $this->assertTrue( 
                true,
                'Wrong exception code thrown on missing <cols> setting.'
            );
            return;
        }
        $this->fail( 'No or wrong exception thrown on wrong type for <cols> setting.' );
    }

    public function testTableConfigurationFailure3 ()
    {
        // 'cols' setting out of range
        try
        {
            $table = new ezcConsoleTable( $this->output, -10 );
        }
        catch (ezcBaseValueException $e)
        {
            $this->assertTrue( 
                true,
                'Wrong exception code thrown on missing <cols> setting.'
            );
            return;
        }
        $this->fail( 'No or wrong exception thrown on invalid value of <cols> setting.' );
    }

    public function testArrayAccess()
    {
        $table = new ezcConsoleTable( $this->output, 100 );
        $table[0];
    }

    public function testSetAccessOptionsSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        $table->options->colWidth = array( 1, 2, 3 );
        $table->options->colWrap = ezcConsoleTable::WRAP_CUT;
        $table->options->defaultAlign = ezcConsoleTable::ALIGN_CENTER;
        $table->options->colPadding = ':';
        $table->options->widthType = ezcConsoleTable::WIDTH_FIXED;
        $table->options->lineVertical = ':';
        $table->options->lineHorizontal = '-';
        $table->options->corner = 'o';
        $table->options->defaultFormat = 'test';
        $table->options->defaultBorderFormat = 'test2';
        
        $this->assertEquals( array( 1, 2, 3 ), $table->options->colWidth );
        $this->assertEquals( ezcConsoleTable::WRAP_CUT, $table->options->colWrap );
        $this->assertEquals( ezcConsoleTable::ALIGN_CENTER, $table->options->defaultAlign );
        $this->assertEquals( ':', $table->options->colPadding );
        $this->assertEquals( ezcConsoleTable::WIDTH_FIXED, $table->options->widthType );
        $this->assertEquals( ':', $table->options->lineVertical );
        $this->assertEquals( '-', $table->options->lineHorizontal );
        $this->assertEquals( 'o', $table->options->corner );
        $this->assertEquals( 'test', $table->options->defaultFormat );
        $this->assertEquals( 'test2', $table->options->defaultBorderFormat );
    }
    
    public function testSetAccessOptionsSuccess2()
    {
        $opt = new ezcConsoleTableOptions(
            array( 1, 2, 3 ),
            ezcConsoleTable::WRAP_CUT,
            ezcConsoleTable::ALIGN_CENTER,
            ':',
            ezcConsoleTable::WIDTH_FIXED,
            ':',
            '-',
            'o',
            'test',
            'test2'
        );
        $this->assertEquals( array( 1, 2, 3 ), $opt->colWidth );
        $this->assertEquals( ezcConsoleTable::WRAP_CUT, $opt->colWrap );
        $this->assertEquals( ezcConsoleTable::ALIGN_CENTER, $opt->defaultAlign );
        $this->assertEquals( ':', $opt->colPadding );
        $this->assertEquals( ezcConsoleTable::WIDTH_FIXED, $opt->widthType );
        $this->assertEquals( ':', $opt->lineVertical );
        $this->assertEquals( '-', $opt->lineHorizontal );
        $this->assertEquals( 'o', $opt->corner );
        $this->assertEquals( 'test', $opt->defaultFormat );
        $this->assertEquals( 'test2', $opt->defaultBorderFormat );
    }

    public function testSetAccessOptionsSuccess3()
    {
        $opt = new ezcConsoleTableOptions(
            array(
                "colWidth" => array( 1, 2, 3 ),
                "colWrap" => ezcConsoleTable::WRAP_CUT,
                "defaultAlign" => ezcConsoleTable::ALIGN_CENTER,
                "colPadding" => ':',
                "widthType" => ezcConsoleTable::WIDTH_FIXED,
                "lineVertical" => ':',
                "lineHorizontal" => '-',
                "corner" => 'o',
                "defaultFormat" => 'test',
                "defaultBorderFormat" => 'test2'
            )
        );
        $this->assertEquals( array( 1, 2, 3 ), $opt->colWidth );
        $this->assertEquals( ezcConsoleTable::WRAP_CUT, $opt->colWrap );
        $this->assertEquals( ezcConsoleTable::ALIGN_CENTER, $opt->defaultAlign );
        $this->assertEquals( ':', $opt->colPadding );
        $this->assertEquals( ezcConsoleTable::WIDTH_FIXED, $opt->widthType );
        $this->assertEquals( ':', $opt->lineVertical );
        $this->assertEquals( '-', $opt->lineHorizontal );
        $this->assertEquals( 'o', $opt->corner );
        $this->assertEquals( 'test', $opt->defaultFormat );
        $this->assertEquals( 'test2', $opt->defaultBorderFormat );
    }

    public function testSetAccessOptionsFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
    
        $exceptionThrown = false;
        
        try
        {
            $table->options->colWidth = 'test';
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <colWidth>.');
        }
        $exceptionThrown = false;

        try
        {
            $table->options->colWrap = 100;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <colWrap>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->defaultAlign = 101;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <defaultAlign>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->colPadding = 102;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <colPadding>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->widthType = 103;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <widthType>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->lineVertical = 104;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <lineVertical>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->lineHorizontal = 105;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <lineHorizontal>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->corner = 106;
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <corner>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->defaultFormat = array();
        } 
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <defaultFormat>.');
        }
        $exceptionThrown = false;
        
        try
        {
            $table->options->defaultBorderFormat = true;
        }
        catch ( ezcBaseValueException $e)
        {
            $exceptionThrown = true;
        }
        if ( !$exceptionThrown )
        {
            $this->fail( 'No exception thrown on invalid setting for <defaultBorderFormat>.');
        }
        $exceptionThrown = false;
        
    }

    public function testConstructorFailure()
    {
        try
        {
            $table = new ezcConsoleTable( $this->output, 80, 23 );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "ezcBaseValueException not thrown on invalid option parameter to constructor." );
    }

    public function testSetOptionsSuccess()
    {
        $optArr = array(
            "colWidth" => array( 1, 2, 3 ),
            "colWrap" => ezcConsoleTable::WRAP_CUT,
            "defaultAlign" => ezcConsoleTable::ALIGN_CENTER,
            "colPadding" => ':',
            "widthType" => ezcConsoleTable::WIDTH_FIXED,
            "lineVertical" => ':',
            "lineHorizontal" => '-',
            "corner" => 'o',
            "defaultFormat" => 'test',
            "defaultBorderFormat" => 'test2'
        );
        $optObj = new ezcConsoleTableOptions(
            array( 1, 2, 3 ),
            ezcConsoleTable::WRAP_CUT,
            ezcConsoleTable::ALIGN_CENTER,
            ':',
            ezcConsoleTable::WIDTH_FIXED,
            ':',
            '-',
            'o',
            'test',
            'test2'
        );

        $table = new ezcConsoleTable( $this->output, 80 );
        $table->setOptions( $optArr );

        $this->assertEquals( $optObj, $table->options );
        
        $table = new ezcConsoleTable( $this->output, 80 );
        $table->setOptions( $optObj );

        $this->assertSame( $optObj, $table->options );
    }

    public function testSetOptionsFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );

        try
        {
            $table->setOptions( 23 );
        }
        catch ( ezcBaseValueException $e  )
        {
            return;
        }
        $this->fail( "ezcBaseValueException not thrown on invalid Parameter to setOptions()." );
    }

    public function testGetOptions()
    {
        $optObj = new ezcConsoleTableOptions(
            array( 1, 2, 3 ),
            ezcConsoleTable::WRAP_CUT,
            ezcConsoleTable::ALIGN_CENTER,
            ':',
            ezcConsoleTable::WIDTH_FIXED,
            ':',
            '-',
            'o',
            'test',
            'test2'
        );
        $table = new ezcConsoleTable( $this->output, 80, $optObj );

        $this->assertSame( $optObj, $table->getOptions() );
    }

    public function testOutputTable()
    {
        $table = new ezcConsoleTable( $this->output, 80 );

        for ( $i = 0; $i < count( $this->tableData1 ); $i++ )
        {
            for ( $j = 0; $j < count( $this->tableData1[$i]); $j++ )
            {
                $table[$i][$j]->content = $this->tableData1[$i][$j];
            }
        }

        $table[0][0]->format = "red";
        $table[0]->borderFormat = "green";

        ob_start();
        $table->outputTable();
        $res = ob_get_clean();

        $refFile = dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . 'testTable1a.dat';
        $this->assertEquals(
            file_get_contents( $refFile ),
            $res,
            "Table not printed correctly on use of outputTable()"
        );
    }
    
    public function testOffsetExistsSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        $table[0]->borderFormat = "green";

        $this->assertTrue( isset( $table[0] ) );
        $this->assertFalse( isset( $table[1] ) );
    }

    public function testOffsetExistsFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        
        $exceptionThrown = false;
        try
        {
            isset( $table[-10] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on negative offset." );
        
        $exceptionThrown = false;
        try
        {
            isset( $table["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on character offset." );
    }

    public function testOffsetGetSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        $table[0] = new ezcConsoleTableRow();

        $this->assertEquals( new ezcConsoleTableRow(), $table[0] );
        // Test for bug #10710
        $this->assertEquals( "test", ( $table[][0]->format = "test" ) );
    }

    public function testOffsetGetFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        
        $exceptionThrown = false;
        try
        {
            echo $table["foo"];
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid string offset." );
        
        $exceptionThrown = false;
        try
        {
            echo $table[-23];
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid int offset." );
    }

    public function testOffsetSetSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        $table[0] = new ezcConsoleTableRow();
        $table[] = new ezcConsoleTableRow();
        
        $this->assertTrue( isset( $table[0] ) );
        $this->assertTrue( isset( $table[1] ) );
    }
    
    public function testOffsetSetFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        
        $exceptionThrown = false;
        try
        {
            $table[-10] = new ezcConsoleTableRow();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on negative offset." );
        
        $exceptionThrown = false;
        try
        {
            $table["foo"] = new ezcConsoleTableRow();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on character offset." );
        
        $exceptionThrown = false;
        try
        {
            $table[10] = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value." );
    }

    public function testOffsetUnsetSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        $table[0] = new ezcConsoleTableRow();
        $table[] = new ezcConsoleTableRow();
        
        $this->assertTrue( isset( $table[0] ) );
        $this->assertTrue( isset( $table[1] ) );

        unset( $table[0] );
        unset( $table[1] );

        $this->assertFalse( isset( $table[0] ) );
        $this->assertFalse( isset( $table[1] ) );
    }
    
    public function testOffsetUnsetFailure()
    {
        $table = new ezcConsoleTable( $this->output, 80 );
        
        $exceptionThrown = false;
        try
        {
            unset( $table[-10] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on negative offset." );
        
        $exceptionThrown = false;
        try
        {
           unset( $table["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on character offset." );
    }

    public function testIterator()
    {
        $table = new ezcConsoleTable( $this->output, 80 );

        for ( $i = 0; $i < 10; ++$i )
        {
            $table[$i]->borderFormat = "green";
        }
        $refRow = new ezcConsoleTableRow();
        $refRow->borderFormat = "green";
        
        // First iteration
        $i = 0;
        foreach ( $table as $id => $row )
        {
            $this->assertEquals( $i++, $id );
            $this->assertEquals( $refRow, $row );
        }
        $this->assertEquals( 10, $i, "Not iterated through all rows." );
        
        // Second iteration
        $i = 0;
        foreach ( $table as $id => $row )
        {
            $this->assertEquals( $i++, $id );
            $this->assertEquals( $refRow, $row );
        }
        $this->assertEquals( 10, $i, "Not iterated through all rows." );
    }

    public function testGetAccessSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 100 );
        $this->assertEquals( 100, $table->width );
        $this->assertEquals( new ezcConsoleTableOptions(), $table->options );
    }

    public function testGetAccessFailure()
    {
        $table = new ezcConsoleTable( $this->output, 100 );
        try
        {
            echo $table->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on get access to invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $table = new ezcConsoleTable( $this->output, 100 );
        $opt = new ezcConsoleTableOptions();
        
        $table->options = $opt;
        $table->width = 80;

        $this->assertEquals( 80, $table->width );
        $this->assertSame( $opt, $table->options );
    }

    public function testSetAccessFailure()
    {
        $table = new ezcConsoleTable( $this->output, 100 );

        $exceptionThrown = false;
        try
        {
            $table->options = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value for property options." );

        $exceptionThrown = false;
        try
        {
            $table->width = false;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBaseValueException not thrown on invalid value for property width." );

        $exceptionThrown = false;
        try
        {
            $table->foo = true;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "ezcBasePropertyNotFoundException not thrown set access to invalid property foo." );
    }

    public function testIssetAccess()
    {
        $table = new ezcConsoleTable( $this->output, 100 );

        $this->assertTrue( isset( $table->width ) );
        $this->assertTrue( isset( $table->options ) );
        $this->assertFalse( isset( $table->foo ) );
    }
    
    private function commonTableTest( $refFile, $tableData, $settings, $options, $headrows = array() )
    {
        $table =  new ezcConsoleTable( 
            $this->output,
            $settings['width']
        );
        
        // Set options
        foreach ( $options as $key => $val )
        {
            if ( $key == 'lineFormatHead' )
            {
                continue;
            }
            $table->options->$key = $val;
        }

        // Add data
        for ( $i = 0; $i < count( $tableData ); $i++ )
        {
            for ( $j = 0; $j < count( $tableData[$i]); $j++ )
            {
                $table[$i][$j]->content = $tableData[$i][$j];
            }
        }
        
        // Set a specific cell format
        $table[0][0]->format = 'red';

        // Apply head format to head rows
        foreach ( $headrows as $row )
        {
            $table[$row]->borderFormat = isset( $options['lineFormatHead'] ) ? $options['lineFormatHead'] : 'default';
        }
        
        // For visual inspection, uncomment this block
//        echo "\n\n";
//        echo "Old $refFile:\n:";
//        echo file_get_contents( dirname( __FILE__ ) . '/data/' . $refFile . '.dat' );
//        echo "New $refFile:\n:";
//        echo implode( "\n", $table->getTable() );
//        echo "\n\n";
        
        $this->assertTableOutputEquals(
            $refFile,
            (string) $table
        );
    }

    protected function assertTableOutputEquals( $expectedRef, $actualContent )
    {
        $refFile = dirname( __FILE__ ) 
            . '/data/' 
            . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" )
            . $expectedRef
            . '.dat';

        // To prepare test files, uncomment this block
        // file_put_contents( $refFile, $actualContent );
        
        if ( !file_exists( $refFile ) )
        {
            // Default assert for new files.
            $this->assertEquals(
                '',
                $actualContent,
                "Asserted against empty string, since reference file '$refFile' does not exist."
            );
        }
        else
        {
            $this->assertEquals(
                file_get_contents( $refFile ),
                $actualContent,
                'Table not correctly generated for ' . $expectedRef . '.'
            );
        }

    }
}
?>
