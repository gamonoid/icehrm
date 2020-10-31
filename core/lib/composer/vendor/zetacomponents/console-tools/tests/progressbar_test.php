<?php
/**
 * ezcConsoleOutputTest class
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
 * Test suite for ezcConsoleProgressbar class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleProgressbarTest extends ezcTestCase
{
	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleProgressbarTest" );
	}
    
    public function testProgress1()
    {
        $this->commonProgressbarTest( __FUNCTION__, 42, 13, array () );
    }
    
    public function testProgress2()
    {
        $this->commonProgressbarTest( __FUNCTION__, 20, 32, array () );
    }
    
    public function testProgress3()
    {
        $this->commonProgressbarTest( __FUNCTION__, 42, 13, array ( 'barChar' => '#', 'emptyChar' => '*' ) );
    }
    
    public function testProgress4()
    {
        $this->commonProgressbarTest( __FUNCTION__, 55, 19, array ( 'progressChar' => '&' ) );
    }
    
    public function testProgress5()
    {
        $this->commonProgressbarTest( __FUNCTION__, 42, 13, array ( 'progressChar' => '&', 'width' => 55 ) );
    }
    
    public function testProgress6()
    {
        $this->commonProgressbarTest( __FUNCTION__, 22, 3, array ( 'barChar' => '#', 'emptyChar' => '*', 'progressChar' => '&', 'width' => 81 ) );
    }
    
    public function testProgress7()
    {
        $this->commonProgressbarTest( __FUNCTION__, 42, 7, array ( 'barChar' => '1234', 'emptyChar' => '9876' ) );
    }
    
    public function testProgress8()
    {
        $this->commonProgressbarTest( __FUNCTION__, 42, 7, array ( 'barChar' => '123', 'emptyChar' => '987', 'progressChar' => '---' ) );
    }
    
    public function testProgress9()
    {
        $out = new ezcConsoleOutput();
  
        $formatString = ''
            . $out->formatText( 'Actual progress', 'success' )
            . ': <'
            . $out->formatText( '%bar%', 'failure' )
            . '> '
            . $out->formatText( '%fraction%', 'success' );
        
        $this->commonProgressbarTest( 
            __FUNCTION__, 
            1073, 
            123, 
            array( 
                'barChar' => '123', 
                'emptyChar' => '987', 
                'progressChar' => '---', 
                'width' => 97, 
                'formatString' => $formatString,
                'fractionFormat' => '%o'
            ) 
       );
    }
    
    public function testProgress10()
    {
        $this->commonProgressbarTest( __FUNCTION__, 100, 1, array ( 'redrawFrequency' => 10 ) );
    }
    
    public function testProgress11()
    {
        $this->commonProgressbarTest( __FUNCTION__, 100, 2.5, array ( 'actFormat' => '%01.2f', 'maxFormat' => '%01.2f' ) );
    }
    
    public function testProgress12()
    {
        $this->commonProgressbarTest( __FUNCTION__, 100, 2.5, array ( 'actFormat' => '%01.2f', 'maxFormat' => '%01.8f' ) );
    }
    
    public function testProgress13()
    {
        $this->commonProgressbarTest( __FUNCTION__, 100, 2.5, array ( 'actFormat' => '%01.8f', 'maxFormat' => '%01.2f' ) );
    }
    
    public function testProgressUtfInBar()
    {
        $this->commonProgressbarTest(
            __FUNCTION__,
            10,
            2,
            array(
                'barChar' => 'ö'
            )
        );
    }
    
    public function testProgressUtfInText()
    {
        $this->commonProgressbarTest(
            __FUNCTION__,
            10,
            2,
            array(
                'formatString' => '%act%ö/ä%max%ü[%bar%]ß%fraction%%'
            )
        );
    }
    
    public function testProgressUtfInBoth()
    {
        $this->commonProgressbarTest(
            __FUNCTION__,
            10,
            2,
            array(
                'formatString' => '%act%ö/ä%max%ü[%bar%]ß%fraction%%',
                'barChar' => 'ö'
            )
        );
    }

    public function testProgressNoPrintMinVerbosity()
    {
        $out = new ezcConsoleOutput();
        $out->options->verbosityLevel = 0;

        $progress = new ezcConsoleProgressbar( $out, 10 );

        ob_start();

        for ( $i = 0; $i < 10; ++$i )
        {
            $progress->advance();
        }
        $progress->finish();

        $content = ob_get_clean();

        $this->assertEquals(
            '',
            $content,
            'Progress bar printed although verbosity level to low.'
        );
    }

    public function testProgressNoPrintMinVerbosityNoDefault()
    {
        $out = new ezcConsoleOutput();
        $out->options->verbosityLevel = 10;

        $progress = new ezcConsoleProgressbar( $out, 10 );
        $progress->options->minVerbosity = 20;

        ob_start();

        for ( $i = 0; $i < 10; ++$i )
        {
            $progress->advance();
        }
        $progress->finish();

        $content = ob_get_clean();

        $this->assertEquals(
            '',
            $content,
            'Progress bar printed although verbosity level to low.'
        );
    }

    public function testProgressNoPrintMaxVerbosity()
    {
        $out = new ezcConsoleOutput();

        $progress = new ezcConsoleProgressbar( $out, 10 );
        $progress->options->maxVerbosity = 0;

        ob_start();

        for ( $i = 0; $i < 10; ++$i )
        {
            $progress->advance();
        }
        $progress->finish();

        $content = ob_get_clean();

        $this->assertEquals(
            '',
            $content,
            'Progress bar printed although verbosity level to low.'
        );
    }

    public function testProgressNoPrintMaxVerbosityNoDefault()
    {
        $out = new ezcConsoleOutput();
        $out->options->verbosityLevel = 10;

        $progress = new ezcConsoleProgressbar( $out, 10 );
        $progress->options->maxVerbosity = 9;

        ob_start();

        for ( $i = 0; $i < 10; ++$i )
        {
            $progress->advance();
        }
        $progress->finish();

        $content = ob_get_clean();

        $this->assertEquals(
            '',
            $content,
            'Progress bar printed although verbosity level to low.'
        );
    }

    public function testProgressPrintMinMaxVerbosity()
    {
        $out = new ezcConsoleOutput();
        $out->options->verbosityLevel = 10;

        $progress = new ezcConsoleProgressbar( $out, 10 );
        $progress->options->maxVerbosity = 10;
        $progress->options->minVerbosity = 10;

        ob_start();

        for ( $i = 0; $i < 10; ++$i )
        {
            $progress->advance();
        }
        $progress->finish();

        $content = ob_get_clean();

        $this->assertNotEquals(
            '',
            $content,
            'Progress bar printed although verbosity level to low.'
        );
    }

    public function testSetOptionsSuccess()
    {
        $out = new ezcConsoleOutput();
        
        $opArr = array();
        
        $optArr['barChar'] = "*";
        $optArr['emptyChar'] = "#";
        $optArr['formatString'] = "-- %act% / %max% [%bar%] %fraction%% --";
        $optArr['fractionFormat'] = "%f";
        $optArr['progressChar'] = "<";
        $optArr['redrawFrequency'] = 42;
        $optArr['step'] = 23;
        $optArr['width'] = 42;
        $optArr['actFormat'] = '%f';
        $optArr['maxFormat'] = '%f';
        
        $optObj = new ezcConsoleProgressbarOptions();
        
        $optObj->barChar = "*";
        $optObj->emptyChar = "#";
        $optObj->formatString = "-- %act% / %max% [%bar%] %fraction%% --";
        $optObj->fractionFormat = "%f";
        $optObj->progressChar = "<";
        $optObj->redrawFrequency = 42;
        $optObj->step = 23;
        $optObj->width = 42;
        $optObj->actFormat = '%f';
        $optObj->maxFormat = '%f';

        $bar = new ezcConsoleProgressbar( $out, 10 );
        $bar->setOptions( $optArr );

        $this->assertEquals( $optObj, $bar->options, "Options not set correctly from array." );

        $bar = new ezcConsoleProgressbar( $out, 10 );
        $bar->setOptions( $optObj );

        $this->assertEquals( $optObj, $bar->options, "Options not set correctly from object." );
    }

    public function testSetOptionsFailure()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        try
        {
            $bar->setOptions( 23 );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exeception not thrown on invalid parameter for setOptions()." );
    }

    public function testGetOptions()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );
        $opt = new ezcConsoleProgressbarOptions();

        $bar->options = $opt;

        $this->assertSame( $opt, $bar->getOptions(), "Options not correctly returned from getOptions()." );
    }

    public function testGetAccessSuccess()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        $this->assertEquals( new ezcConsoleProgressbarOptions(), $bar->options );
        $this->assertEquals( 10, $bar->max );
        $this->assertEquals( 1, $bar->step );
    }

    public function testGetAccessFailure()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        try
        {
            echo $bar->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on get access of invalid property ezcConsoleProgressbar->foo." );
    }

    public function testSetAccessSuccess()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        $this->genericSetAccessTestSuccess( $bar, "options", new ezcConsoleProgressbarOptions() );
        $this->genericSetAccessTestSuccess( $bar, "step", 10 );
        $this->genericSetAccessTestSuccess( $bar, "max", 100 );
    }

    public function testSetAccessFailure()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        $this->genericSetAccessTestFailureValue( $bar, "options", 23 );
        $this->genericSetAccessTestFailureValue( $bar, "max", true );
        $this->genericSetAccessTestFailureValue( $bar, "max", -23 );
        $this->genericSetAccessTestFailureValue( $bar, "step", true );
        $this->genericSetAccessTestFailureValue( $bar, "step", -23 );
        $this->genericSetAccessTestFailureNonexistent( $bar );
    }

    public function testIssetAccess()
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );

        $this->assertTrue( isset( $bar->options ) );
        $this->assertTrue( isset( $bar->max ) );
        $this->assertTrue( isset( $bar->step ) );
        $this->assertFalse( isset( $bar->foo ) );
    }
     public function testFinish()
     {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, 10 );
        
        $res = array();
        for ( $i = 0; $i < 5; ++$i ) 
        {
            ob_start();
            $bar->advance();
            $resTmp = ob_get_clean();
            if ( trim( $resTmp ) !== '')
            {
                $res[] = $resTmp;
            }
        }
        ob_start();
        $bar->finish();
        $res[] = ob_get_clean();

        $refFile = dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . __FUNCTION__ . '.dat';
        // Use the following line to regenerate test reference files
        // file_put_contents( $refFile, implode( PHP_EOL, $res ) );
        $this->assertEquals(
            file_get_contents( $refFile ),
            implode( PHP_EOL, $res ),
            'Progressbar not correctly generated for ' . $refFile . '.'
        );

     }

    protected function genericSetAccessTestSuccess( $object, $propertyName, $value )
    {
        $object->$propertyName = $value;
        if ( is_object( $value ) )
        {
            $this->assertSame(
                $value,
                $object->$propertyName, "Property " . get_class( $object ) . "->$propertyName not set correctly on object of type " . get_class( $object ) . "."
            );
        }
        else
        {
            $this->assertEquals(
                $value,
                $object->$propertyName, "Property " . get_class( $object ) . "->$propertyName not set correctly on object of type " . get_class( $object ) . "."
            );
        }
    }
    
    protected function genericSetAccessTestFailureValue( $object, $propertyName, $value )
    {
        try
        {
            $object->$propertyName = $value;
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail(
            "ezcBaseValueException not thrown on value '" . ( is_object( $value ) ) ? get_class( $value ) : $value . "' for property " . get_class( $object ) . "->$propertyName."
        );
    }

    protected function genericSetAccessTestFailureNonexistent( $object )
    {
        try
        {
            $object->foo = 23;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on access of not existent property " . get_class( $object ) . "->foo." );
    }

    private function commonProgressbarTest( $refFile, $max, $step, $options )
    {
        $out = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $out, $max, $options );
        if ( $step != 1 )
        {
            $bar->options->step = $step;
        }
        $res = array();
        for ( $i = 0; $i < $max; $i+= $step) 
        {
            ob_start();
            $bar->advance();
//            sleep( 1 );
            $resTmp = ob_get_clean();
            if ( trim( $resTmp ) !== '' )
            {
                $res[] = $resTmp;
            }
        }

        $refFile = dirname( __FILE__ ) . '/data/' . ( ezcBaseFeatures::os() === "Windows" ? "windows/" : "posix/" ) . $refFile . '.dat';
        // Use the following line to regenerate test reference files
        // file_put_contents( $refFile, implode( PHP_EOL, $res ) );

        $expected = ( file_exists( $refFile ) ? file_get_contents( $refFile ) : '' );
        $this->assertEquals(
            $expected,
            implode( PHP_EOL, $res ),
            'Progressbar not correctly generated for ' . $refFile . '.'
        );
    }
}
?>
