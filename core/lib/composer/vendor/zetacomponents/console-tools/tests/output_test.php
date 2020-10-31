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
 * Test suite for ezcConsoleOutput class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleOutputTest extends ezcTestCase
{
    /**
     * testString 
     * 
     * @var string
     */
    private $testString = 'A passion for php';

    private $testFormats = array(
        'color_only_1' => array(
            'in'  => array( 
                'color' => 'blue',
            ),
            'out' => "\033[34m%s\033[0m"
        ),
        'color_only_2' => array( 
            'in'  => array( 
                'color' => 'red',
            ),
            'out' => "\033[31m%s\033[0m"
        ),
        'bgcolor_only_1' => array( 
            'in'  => array( 
                'bgcolor' => 'green',
            ),
            'out' => "\033[42m%s\033[0m"
        ),
        'bgcolor_only_2' => array( 
            'in'  => array( 
                'bgcolor' => 'yellow',
            ),
            'out' => "\033[43m%s\033[0m"
        ),
        'style_only_1' => array( 
            'in'  => array( 
                'style' => 'bold',
            ),
            'out' => "\033[1m%s\033[0m"
        ),
        'style_only_2' => array( 
            'in'  => array( 
                'style' => 'negative',
            ),
            'out' => "\033[7m%s\033[0m"
        ),
    );

    /**
     * consoleOutput 
     * 
     * @var mixed
     */
    private $consoleOutput;

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleOutputTest" );
	}

    protected function setUp()
    {
        $this->consoleOutput = new ezcConsoleOutput();
        foreach ( $this->testFormats as $name => $inout ) 
        {
            foreach ( $inout['in'] as $formatName => $val )
            {
                $this->consoleOutput->formats->$name->$formatName = $val;
            }
        }
    }

    protected function tearDown()
    {
        unset( $this->consoleOutput );
    }

    /**
     * testFormatText
     * 
     * @access public
     */
    public function testFormatText()
    {
        foreach ( $this->testFormats as $name => $inout ) 
        {
            $realRes = $this->consoleOutput->formatText( $this->testString, $name );
            $fakeRes = ezcBaseFeatures::os() !== "Windows" ? sprintf( $inout['out'], $this->testString ) : $this->testString;
            $this->assertEquals( 
                $realRes,
                $fakeRes, 
                "Test <{$name}> failed. String <{$realRes}> (real) is not equal to <{$fakeRes}> (fake)."
            );
        }
    }

    /**
     * testOutputText
     * 
     * @access public
     */
    public function testOutputText()
    {
        foreach ( $this->testFormats as $name => $inout ) 
        {
            ob_start();
            $this->consoleOutput->outputText( $this->testString, $name );
            $realRes = ob_get_contents();
            ob_end_clean();
            $fakeRes = ezcBaseFeatures::os() !== "Windows" ? sprintf( $inout['out'], $this->testString ) : $this->testString;
            $this->assertEquals( 
                $fakeRes, 
                $realRes,
                "Test <{$name}> failed. String <{$realRes}> (real) is not equal to <{$fakeRes}> (fake)."
            );
        }
    }

    /**
     * testOutputTextAutobreak
     * 
     * @access public
     */
    public function testOutputTextAutobreak()
    {
        $this->consoleOutput->options->autobreak = 20;
        $testText = 'Some text which is obviously longer than 20 characters and should be broken.';

        $testResText = 'Some text which is' . PHP_EOL . 'obviously longer' . PHP_EOL . 'than 20 characters' . PHP_EOL . 'and should be' . PHP_EOL . 'broken.';

        foreach ( $this->testFormats as $name => $inout ) 
        {
            ob_start();
            $this->consoleOutput->outputText( $testText, $name );
            $realRes = ob_get_contents();
            ob_end_clean();
            
            $fakeRes = ezcBaseFeatures::os() !== "Windows" ? sprintf( $inout['out'], $testResText ) : $testResText;
            $this->assertEquals( 
                $fakeRes, 
                $realRes, 
                'Test "' . $name . ' failed. String <' . $realRes . '> (real) is not equal to <' . $fakeRes . '> (fake).' 
            );
        }
    }

    public function testOutputColorAliases()
    {
        $this->consoleOutput->formats->aliasBG->bgcolor = "gray";
        $this->consoleOutput->formats->aliasBG->color = "white";
        $this->consoleOutput->formats->realBG->bgcolor = "black";
        $this->consoleOutput->formats->realBG->color = "white";
        
        $this->consoleOutput->formats->realFG->color = "gray";
        $this->consoleOutput->formats->realFG->bgcolor = "white";
        $this->consoleOutput->formats->aliasFG->color = "black";
        $this->consoleOutput->formats->aliasFG->bgcolor = "white";

        $this->assertEquals(
            $this->consoleOutput->formatText( "I am black!", "aliasBG" ),
            $this->consoleOutput->formatText( "I am black!", "realBG" ),
            "Backgroundcolor <gray> not correctly aliased to <black>."
        );

        $this->assertEquals(
            $this->consoleOutput->formatText( "I am gray!", "aliasFG" ),
            $this->consoleOutput->formatText( "I am gray!", "realFG" ),
            "Foregroundcolor <black> not correctly aliased to <gray>."
        );
    }

    public function testOutputToTarget()
    {
        $outFile = $this->createTempDir( __FUNCTION__ ) . "/outfile";
        touch( $outFile );

        $this->consoleOutput->formats->targetFile->target = $outFile;
        $this->consoleOutput->formats->targetFile->color = "blue";
        $this->consoleOutput->outputText( "Hello, I'm a cool text, written to a file!", "targetFile" );
        
        $fakeRes = $this->consoleOutput->formatText( "Hello, I'm a cool text, written to a file!", "targetFile" );

        unset( $this->consoleOutput );

        $this->assertEquals(
            $fakeRes,
            file_get_contents( $outFile )
        );

        $this->removeTempDir();
    }

    public function testSetOptionsSuccess()
    {
        $optObj = new ezcConsoleOutputOptions();
        $optObj->verbosityLevel = 10;
        $optObj->autobreak      = 80;
        $optObj->useFormats     = false;

        $output = new ezcConsoleOutput();
        $output->setOptions( $optObj );

        $this->assertEquals( $optObj, $output->options );

        $output = new ezcConsoleOutput();
        $output->setOptions(
            array(
                "verbosityLevel" => 10,
                "autobreak"      => 80,
                "useFormats"     => false,
            )
        );
        $this->assertEquals( $optObj, $output->options );
    }

    public function testSetOptionsFailure()
    {
        $output = new ezcConsoleOutput();
        try
        {
            $output->setOptions( true );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid parameter to ezcConsoleOutput->setOptions()." );
    }

    public function testGetOptions()
    {
        $output = new ezcConsoleOutput();
        $this->assertEquals( new ezcConsoleOutputOptions(), $output->getOptions() );
    }

    public function testGetAccessSuccess()
    {
        $output = new ezcConsoleOutput();
        $this->assertEquals( new ezcConsoleOutputOptions(), $output->options );
        $this->assertEquals( new ezcConsoleOutputFormats(), $output->formats );
    }

    public function testGetAccessFailure()
    {
        $output = new ezcConsoleOutput();
        try
        {
            echo $output->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on get access to invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $optObj = new ezcConsoleOutputOptions();
        $forObj = new ezcConsoleOutputFormats();
        $output = new ezcConsoleOutput();

        $output->options = $optObj;
        $output->formats = $forObj;

        $this->assertSame( $optObj, $output->options );
        $this->assertSame( $forObj, $output->formats );
    }

    public function testSetAccessFailure()
    {
        $output = new ezcConsoleOutput();

        $exceptionThrown = false;
        try
        {
            $output->options = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property options." );

        $exceptionThrown = false;
        try
        {
            $output->formats = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property formats." );

        $exceptionThrown = false;
        try
        {
            $output->foo = 23;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on set access to invalid property foo." );
    }

    public function testIssetAccess()
    {
        $output = new ezcConsoleOutput();
        
        $this->assertTrue( isset( $output->options ) );
        $this->assertTrue( isset( $output->formats ) );
        $this->assertFalse( isset( $output->foo ) );
    }

    public function testOutputTextFailure()
    {
        $output = new ezcConsoleOutput();
        $output->formats->invalid->target = "foo://bar";

        $exceptionThrown = false;
        try
        {
            $output->outputText( "Foo Bar", "invalid" );
        }
        catch ( ezcConsoleInvalidOutputTargetException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on output to invalid output target." );
    }

    public function testToPos()
    {
	if ( ezcBaseFeatures::os() === 'Windows' )
	{
	    $this->markTestSkipped( "Does not work on Windows" );
	}
        $output = new ezcConsoleOutput();
        ob_start();
        $output->outputText( "Test 123" );
        $output->toPos( 6 );
        $output->outputText( "456" );
        
        $res = ob_get_clean();
        $exp = '[0mTest 123[0m[6G[0m456[0m';

        $this->assertEquals( $exp, $res, "Position code not generated correctly." );
    }

    public function testRestorePosFailure()
    {
	if ( ezcBaseFeatures::os() === 'Windows' )
	{
	    $this->markTestSkipped( "Does not work on Windows" );
	}
        $output = new ezcConsoleOutput();
        try
        {
            $output->restorePos();
        }
        catch ( ezcConsoleNoPositionStoredException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on restore position without stored position." );
    }
}
?>
