<?php
/**
 * ezcConsoleInputTest class.
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
 * Test suite for ezcConsoleInput class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleInputTest extends ezcTestCase
{
    private $testOptions = array( 
        array( 
            'short'     => 't',
            'long'      => 'testing',
            'options'   => array(),
        ),
        array( 
            'short'     => 's',
            'long'      => 'subway',
            'options'   => array(),
        ),
        array( 
            'short'     => '',
            'long'      => 'carry',
            'options'   => array(),
        ),
        array( 
            'short'     => 'v',
            'long'      => 'visual',
            'options'   => array(
                'multiple'  => true,
                'arguments' => false,
            ),
        ),
        array( 
            'short'     => 'o',
            'long'      => 'original',
            'options'   => array(
                'type'      => ezcConsoleInput::TYPE_STRING,
            ),
        ),
        array( 
            'short'     => 'b',
            'long'      => 'build',
            'options'   => array(
                'type'      => ezcConsoleInput::TYPE_INT,
                'default'   => 42,
            ),
        ),
        array( 
            'short'     => 'd',
            'long'      => 'destroy',
            'options'   => array(
                'type'      => ezcConsoleInput::TYPE_STRING,
                'default'   => 'world',
            ),
        ),
        array( 
            'short'     => 'y',
            'long'      => 'yank',
            'options'   => array(
                'type'          => ezcConsoleInput::TYPE_STRING,
                'multiple'      => true,
                'shorthelp'     => 'Some stupid short text.',
                'longhelp'      => 'Some even more stupid, but somewhat longer long describtion.',
            ),
        ),
        array( 
            'short'     => 'c',
            'long'      => 'console',
            'options'   => array(
                'shorthelp'     => 'Some stupid short text.',
                'longhelp'      => 'Some even more stupid, but somewhat longer long describtion.',
                'depends'       => array( 't', 'o', 'b', 'y' ),
            ),
        ),
        array( 
            'short'     => 'e',
            'long'      => 'edit',
            'options'   => array(
                'excludes'      => array( 't', 'y' ),
                'arguments'     => false,
            ),
        ),
        array( 
            'short'     => 'n',
            'long'      => 'new',
            'options'   => array(
                'depends'       => array( 't', 'o' ),
                'excludes'      => array( 'b', 'y' ),
                'arguments'     => false,
            ),
        ),
    );

    private $testAliasesSuccess = array( 
        array(
            'short' => 'k',
            'long'  => 'kelvin',
            'ref'   => 't',
        ),
        array(
            'short' => 'f',
            'long'  => 'foobar',
            'ref'   => 'o',
        ),
    );

    private $testAliasesFailure = array( 
        array(
            'short' => 'l',
            'long'  => 'lurking',
            'ref'   => 'x',
        ),
        array(
            'short' => 'e',
            'long'  => 'elvis',
            'ref'   => 'z',
        ),
        array(
            'short' => 'd',
            'long'  => 'destroy',
            'ref'   => 'd',
        ),
    );

    private $testArgsSuccess = array( 
        array(
            'foo.php',
            '-o',
            '"Test string2"',
            '--build',
            '42',
        ),
        array(
            'foo.php',
            '-b',
            '42',
            '--yank',
            '"a"',
            '--yank',
            '"b"',
            '--yank',
            '"c"',
        ),
        array(
            'foo.php',
            '--yank=a',
            '--yank=b',
            '--yank="c"',
            '-y',
            '1',
            '-y',
            '2'
        ),
        array(
            'foo.php',
            '--yank=a',
            '--yank=b',
            '-y',
            '1',
            'arg1',
            'arg2',
        ),
    );

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleInputTest" );
	}

    protected function setUp()
    {
        $this->input = new ezcConsoleInput();
        foreach ( $this->testOptions as $paramData )
        {
            $this->input->registerOption( $this->createFakeOption( $paramData ) );
        }
    }

    protected function createFakeOption( $paramData )
    {
        $param = new ezcConsoleOption( $paramData['short'], $paramData['long'] );
        foreach ( $paramData['options'] as $name => $val )
        {
            if ( $name === 'depends' )
            {
                foreach ( $val as $dep )
                {
                    $param->addDependency( new ezcConsoleOptionRule( $this->input->getOption( $dep ) ) );
                }
                continue;
            }
            if ( $name === 'excludes' )
            {
                foreach ( $val as $dep )
                {
                    $param->addExclusion(new ezcConsoleOptionRule( $this->input->getOption( $dep ) ) );
                }
                continue;
            }
            $param->$name = $val;
        }
        return $param;
    }

    protected function tearDown()
    {
        unset( $this->input );
    }

    public function testRegisterOptionSuccess()
    {
        $input = new ezcConsoleInput();
        foreach ( $this->testOptions as $optionData )
        {
            $option = $this->createFakeOption( $optionData );
            $input->registerOption( $option );
            if ( $option->short !== '' )
            {
                $this->assertEquals( 
                    $option,
                    $input->getOption( $optionData['short'] ),
                    'Parameter not registered correctly with short name <' . $optionData['short'] . '>.'
                );
            }
            $this->assertEquals( 
                $option,
                $input->getOption( $optionData['long'] ),
                'Parameter not registered correctly with long name <' . $optionData['long'] . '>.'
            );
        }
    }

    public function testRegisterOptionFailure()
    {
        $input = new ezcConsoleInput();
        foreach ( $this->testOptions as $optionData )
        {
            $option = $this->createFakeOption( $optionData );
            $input->registerOption( $option );
        }
        foreach ( $this->testOptions as $optionData )
        {
            $option = $this->createFakeOption( $optionData );
            $exceptionThrown = false;
            try
            {
                $input->registerOption( $option );
            }
            catch ( ezcConsoleOptionAlreadyRegisteredException $e )
            {
                $exceptionThrown = true;
            }
            $this->assertTrue(
                $exceptionThrown,
                "Exception not thrown on double registered option " . $optionData["short"] === "" ? "determined by long name." : "determined by short name."
            );
        }
    }

    public function testUnregisterOptionSuccess()
    {
        // register aliases for testing
        $validParams = array();
        foreach ( $this->input->getOptions() as $param )
        {
            $validParams[$param->short] = $param;
        }
        foreach ( $this->testAliasesSuccess as $alias )
        {
            $this->input->registerAlias( $alias['short'], $alias['long'], $validParams[$alias['ref']]  );
        }

        // test itself
        foreach ( $this->input->getOptions() as $option )
        {
            $this->input->unregisterOption( $option );
            $exceptionThrown = false;
            try
            {
                $this->input->getOption( isset( $option->short ) ? $option->short : $option->long );
            }
            catch ( ezcConsoleOptionNotExistsException $e )
            {
                $exceptionThrown = true;
            }
            $this->assertTrue( $exceptionThrown, "Exception not unregistered correctly -{$option->short}/--{$option->long}." );
        }

        $this->assertEquals( 0, count( $this->input->getOptions() ) );
    }

    public function testUnregisterOptionFailure()
    {
        $option = new ezcConsoleOption( "x", "execute" );
        try
        {
            $this->input->unregisterOption( $option );
        }
        catch ( ezcConsoleOptionNotExistsException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on unregistering a non existent option." );
    }

    public function testFromStringSuccess()
    {
        $param = new ezcConsoleInput();
        $param->registerOptionString( '[a:|all:][u?|user?][i|info][o+test|overall+][d*|destroy*]' );
        $res['a'] = new ezcConsoleOption(
            'a', 
            'all', 
            ezcConsoleInput::TYPE_NONE, 
            NULL, 
            false, 
            'No help available.', 
            'Sorry, there is no help text available for this parameter.', 
            array(), 
            array (), 
            true 
        );
        $res['u'] = new ezcConsoleOption(
            'u',
            'user',
            ezcConsoleInput::TYPE_STRING,
            '',
            false,
            'No help available.',
            'Sorry, there is no help text available for this parameter.',
            array (),
            array (),
            true
        );
        $res['o'] = new ezcConsoleOption(
            'o',
            'overall',
            ezcConsoleInput::TYPE_STRING,
            'test',
            true,
            'No help available.',
            'Sorry, there is no help text available for this parameter.',
            array (),
            array (),
            true
        );
        $res['d'] = new ezcConsoleOption(
            'd',
            'destroy',
            ezcConsoleInput::TYPE_NONE,
            null,
            true,
            'No help available.',
            'Sorry, there is no help text available for this parameter.',
            array (),
            array (),
            true
        );
        $this->assertEquals( $res['a'], $param->getOption( 'a' ), 'Option -a not registered correctly.'  );
        $this->assertEquals( $res['u'], $param->getOption( 'u' ), 'Option -u not registered correctly.'  );
        $this->assertEquals( $res['o'], $param->getOption( 'o' ), 'Option -o not registered correctly.'  );
        $this->assertEquals( $res['d'], $param->getOption( 'd' ), 'Option -d not registered correctly.'  );
    }

    public function testFromStringFailure()
    {
        $param = new ezcConsoleInput();
        try
        {
            $param->registerOptionString( '[a:]' );
        }
        catch ( ezcConsoleOptionStringNotWellformedException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on not wellformed option string." );
    }

    /**
     * testRegisterAliasSuccess
     * 
     * @access public
     */
    public function testRegisterAliasSuccess()
    {
        $validParams = array();
        foreach ( $this->input->getOptions() as $param )
        {
            $validParams[$param->short] = $param;
        }
        foreach ( $this->testAliasesSuccess as $alias )
        {
            $this->input->registerAlias( $alias['short'], $alias['long'], $validParams[$alias['ref']]  );
            $this->assertTrue( $this->input->hasOption( $alias['short'] ), "Short name not available after alias registration." );
            $this->assertTrue( $this->input->hasOption( $alias['long'] ), "Long name not available after alias registration." );
        }
    }
    
    /**
     * testRegisterAliasFailure
     * 
     * @access public
     */
    public function testRegisterAliasFailure()
    {
        $refOption = new ezcConsoleOption( 'foo', 'bar' );
        foreach ( $this->testAliasesFailure as $alias )
        {
            $exceptionThrown = false;
            try 
            {
                $this->input->registerAlias( $alias['short'], $alias['long'], $refOption );
            }
            catch ( ezcConsoleOptionNotExistsException $e )
            {
                $exceptionThrown = true;
            }
            $this->assertTrue( $exceptionThrown, "Exception not thrown on regstering invalid alias --{$alias['short']}/--{$alias['long']}." );
        }
        foreach ( $this->testOptions as $option )
        {
            $exceptionThrown = false;
            try 
            {
                $this->input->registerAlias( $option['short'], $option['long'], $this->input->getOption( "t" ) );
            }
            catch ( ezcConsoleOptionAlreadyRegisteredException $e )
            {
                $exceptionThrown = true;
            }
            $this->assertTrue( $exceptionThrown, "Exception not thrown on regstering already existent option as alias --{$alias['short']}/--{$alias['long']}." );
        }
    }
    
    public function testUnregisterAliasSuccess()
    {
        // test preperation
        $validParams = array();
        foreach ( $this->input->getOptions() as $param )
        {
            $validParams[$param->short] = $param;
        }
        foreach ( $this->testAliasesSuccess as $alias )
        {
            $this->input->registerAlias( $alias['short'], $alias['long'], $validParams[$alias['ref']]  );
        }

        foreach ( $this->testAliasesSuccess as $alias )
        {
            $this->assertTrue( $this->input->hasOption( $alias['short'] ), "Alias incorrectly registered, cannot unregister." );
            $this->input->unregisterAlias( $alias['short'], $alias['long'] );
            $this->assertFalse( $this->input->hasOption( $alias['short'] ), "Alias incorrectly unregistered." );
        }
    }
    
    public function testUnregisterAliasFailure()
    {
        foreach ( $this->testOptions as $option )
        {
            $exceptionThrown = false;
            try
            {
                $this->input->unregisterAlias( !empty( $option['short'] ) ? $option['short'] : "f", $option['long'] );
            }
            catch ( ezcConsoleOptionNoAliasException $e )
            {
                $exceptionThrown = true;
            }
            $this->assertTrue( $exceptionThrown, "Exception not trown on try to unregister an option as an alias." );
        }
    }

    public function testGetAccessSuccess()
    {
        $this->assertNull( $this->input->argumentDefinition );
    }

    public function testGetAccessFailure()
    {
        try
        {
            echo $this->input->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on get access to invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $this->assertSetProperty(
            $this->input,
            "argumentDefinition",
            array( new ezcConsoleArguments(), null )
        );
    }

    public function testSetAccessFailure()
    {
        $this->assertSetPropertyFails(
            $this->input,
            "argumentDefinition",
            array( "", "foo", 23, true, array(), new stdClass() ),
            "ezcBaseValueException"
        );
        $this->assertSetPropertyFails(
            $this->input,
            "foo",
            array( "" ),
            "ezcBasePropertyNotFoundException"
        );
    }

    public function testIssetAccess()
    {
        $this->assertTrue( isset( $this->input->argumentDefinition ) );
        $this->assertFalse( isset( $this->input->foo ) );
    }

    // Single parameter tests
    public function testProcessSuccessSingleShortNoValue()
    {
        $args = array(
            'foo.php',
            '-t',
        );
        $res = array( 
            't' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessSingleShortValue()
    {
        $args = array(
            'foo.php',
            '-o',
            'bar'
        );
        $res = array( 
            'o' => 'bar',
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessSingleLongNoValue()
    {
        $args = array(
            'foo.php',
            '--testing',
        );
        $res = array( 
            't' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessSingleLongValue()
    {
        $args = array(
            'foo.php',
            '--original',
            'bar'
        );
        $res = array( 
            'o' => 'bar',
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessFailureSingleShortDefault()
    {
        $args = array(
            'foo.php',
            '-b'
        );
        $res = array( 
            'b' => 42,
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMissingValueException' );
    }
    
    public function testProcessFailureSingleLongDefault()
    {
        $args = array(
            'foo.php',
            '--build'
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMissingValueException' );
    }
    
    public function testProcessSuccessFromArgv()
    {
        $_SERVER["argv"] = array(
            'foo.php',
            '--build',
            '42'
        );
        $this->input->process();
        $this->assertEquals(
            array( "b" => 42, "d" => "world" ),
            $this->input->getOptionValues(),
            "Processing from \$_SERVER['argv'] did not work."
        );
    }
    
    public function testProcessSuccessGetOptionValuesLongnames()
    {
        $_SERVER["argv"] = array(
            'foo.php',
            '--build',
            '42'
        );
        $this->input->process();
        $this->assertEquals(
            array( "build" => 42, "destroy" => "world" ),
            $this->input->getOptionValues( true ),
            "Processing from \$_SERVER['argv'] did not work."
        );
    }

    public function testProcessSuccessSingleShortNoValueArguments()
    {
        $args = array(
            'foo.php',
            '-s',
            '--',
            '-foo',
            '--bar',
            'baz',
        );
        $res = array( 
            's' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessSingleLongNoValueArguments()
    {
        $args = array(
            'foo.php',
            '--subway',
            '--',
            '-foo',
            '--bar',
            'baz',
        );
        $res = array( 
            's' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    // Multiple parameter tests
    public function testProcessSuccessMultipleShortNoValue()
    {
        $args = array(
            'foo.php',
            '-t',
            '-s',
        );
        $res = array( 
            't' => true,
            's' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMultipleShortValue()
    {
        $args = array(
            'foo.php',
            '-o',
            'bar',
            '-b',
            '23'
        );
        $res = array( 
            'o' => 'bar',
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMultipleLongNoValue()
    {
        $args = array(
            'foo.php',
            '--testing',
            '--subway',
        );
        $res = array( 
            't' => true,
            's' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMultipleLongValue()
    {
        $args = array(
            'foo.php',
            '--original',
            'bar',
            '--build',
            '23',
        );
        $res = array( 
            'o' => 'bar',
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMultipleLongValueWithEquals()
    {
        $args = array(
            'foo.php',
            '--original',
            'bar',
            '--build=23',
        );
        $res = array( 
            'o' => 'bar',
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessFailureMultipleShortDefault()
    {
        $args = array(
            'foo.php',
            '-b',
            '-d',
        );
        $res = array( 
            'b' => 42,
            'd' => 'world',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMissingValueException' );
    }

    public function testProcessFailureMultipleLongDefault()
    {
        $args = array(
            'foo.php',
            '--build',
            '--destroy',
        );
        $res = array( 
            'b' => 42,
            'd' => 'world',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMissingValueException' );
    }
    
    // Bug #8645: Default values not set correctly in ezcConsoleInput
    public function testProcessSuccessDefault()
    {
        $args = array(
            'foo.php',
        );
        $res = array( 
            'b' => 42,
            'd' => 'world',
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessMultipleLongSameNoValue()
    {
        $args = array(
            'foo.php',
            '--visual',
            '--visual',
        );
        $res = array( 
            'v' => array( true, true ),
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessArguments_1()
    {
        $args = array(
            'foo.php',
            '--original',
            'bar',
            '--build',
            '23',
            'argument',
            '1',
            '2',
        );
        $res = array( 
            0 => 'argument',
            1 => '1',
            2 => '2',
        );
        $this->argumentsProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessDependencies()
    {
        $args = array(
            'foo.php',
            '-t',
            '-o',
            'bar',
            '--build',
            23,
            '-y',
            'text',
            '--yank',
            'moretext',
            '-c'            // This one depends on -t, -o, -b and -y
        );
        $res = array( 
            't' => true,
            'o' => 'bar',
            'b' => 23,
            'y' => array( 
                'text',
                'moretext'
            ),
            'c' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessExclusions()
    {
        $args = array(
            'foo.php',
            '-o',
            'bar',
            '--build',
            23,
            '--edit'            // This one exclude -t and -y
        );
        $res = array( 
            'o' => 'bar',
            'b' => 23,
            'e' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessDependenciesExclusions()
    {
        $args = array(
            'foo.php',
            '-t',
            '-o',
            'bar',
            '-n'            // This one depends on -t and -o, but excludes -b and -y
        );
        $res = array( 
            't' => true,
            'o' => 'bar',
            'n' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessDependencieValues()
    {
        $rule = new ezcConsoleOptionRule( $this->input->getOption( "y" ), array( "foo", "bar" ) );
        $option = new ezcConsoleOption( "x", "execute" );
        $option->addDependency( $rule );
        $this->input->registerOption( $option );

        $args = array(
            'foo.php',
            '-x',
            '-y',
            'bar',
        );

        $res = array(
            'x' => true,
            'y' => array( 'bar' ),
        );

        $this->commonProcessTestSuccess( $args, $res );
    }
    

    public function testProcessSuccessExclusionValues()
    {
        $rule = new ezcConsoleOptionRule( $this->input->getOption( "y" ), array( "foo", "bar" ) );
        $option = new ezcConsoleOption( "x", "execute" );
        $option->addExclusion( $rule );
        $this->input->registerOption( $option );

        $args = array(
            'foo.php',
            '-x',
            '-y',
            'baz',
        );

        $res = array(
            'x' => true,
            'y' => array( 'baz' ),
        );

        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMandatory()
    {
        $args = array(
            'foo.php',
            '-q',
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'q',
                    'long'      => 'quite',
                    'options'   => array(
                        'mandatory' => true,
                    ),
                )
            )
        );
        $res = array( 
            'q' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessMandatoryDefault()
    {
        $args = array(
            'foo.php',
            '-q',
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'q',
                    'long'      => 'quite',
                    'options'   => array(
                        'default'   => 'test',
                        'mandatory' => true,
                    ),
                )
            )
        );
        $res = array( 
            'q' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessSuccessHelp()
    {
        $args = array(
            'foo.php',
            '-h',
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'q',
                    'long'      => 'quite',
                    'options'   => array(
                        'mandatory' => true,
                    ),
                )
            )
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'h',
                    'long'      => 'help',
                    'options'   => array(
                        'isHelpOption' => true,
                    ),
                )
            )
        );
        $res = array( 
            'h' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );
    }

    public function testProcessSuccessNewArgumentsSimple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );

        $this->input->process(
            array( "foo.php", "'some file'", "file" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( "file", $this->input->argumentDefinition["file2"]->value );
    }

    public function testProcessFailureNewArgumentsSimple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessFailureNewArgumentsTooMany()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );

        $args = array( "foo.php", "'test'", "'foo'", "'bar'" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleTooManyArgumentsException' );
    }

    public function testProcessSuccessNewArgumentsOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;

        $this->input->process(
            array( "foo.php", "'some file'", "file" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( "file", $this->input->argumentDefinition["file2"]->value );
    }

    public function testProcessFailureNewArgumentsOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsAutoOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "file3" );

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( "file", $this->input->argumentDefinition["file2"]->value );
        $this->assertEquals( "another file", $this->input->argumentDefinition["file3"]->value );
    }

    public function testProcessFailureNewArgumentsAutoOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "file3" );

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;

        $this->input->process(
            array( "foo.php", "'some file'" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file2"]->value );
    }

    // Issue #10873: ezcConsoleArgument default value not working
    public function testProcessSuccessNewArgumentsOptionalNotAvailableDefault()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[1]->default   = "some other file";

        $this->input->process(
            array( "foo.php", "'some file'" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( "some other file", $this->input->argumentDefinition["file2"]->value );
    }

    public function testProcessFailureNewArgumentsOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsAutoOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "file3" );

        $this->input->process(
            array( "foo.php", "'some file'" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file2"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file3"]->value );
    }

    public function testProcessFailureNewArgumentsAutoOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "file3" );

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsMultipleOne()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[0]->multiple = true;

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( array( "some file", "file", "another file" ), $this->input->argumentDefinition["file1"]->value );
    }

    public function testProcessFailureNewArgumentsMultipleOne()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[0]->multiple = true;

        $args = array( "foo.php" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsMultipleMultiple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple = true;

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( array( "file", "another file" ), $this->input->argumentDefinition["file2"]->value );
    }

    public function testProcessFailureNewArgumentsMultipleMultiple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple = true;

        $args = array( "foo.php", "'test'" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessSuccessNewArgumentsMultipleOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple  = true;
        $this->input->argumentDefinition[1]->mandatory = false;

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( array( "file", "another file" ), $this->input->argumentDefinition["file2"]->value );
        
        // Old handling
        $this->assertEquals( array( "some file", "file", "another file" ), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsMultipleOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple  = true;
        $this->input->argumentDefinition[1]->mandatory = false;

        $this->input->process(
            array( "foo.php", "'some file'" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file2"]->value );
        
        // Old handling
        $this->assertEquals( array( "some file" ), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsMultipleAutoOptionalAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[0]->mandatory = false;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple  = true;

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( array( "file", "another file" ), $this->input->argumentDefinition["file2"]->value );
        
        // Old handling
        $this->assertEquals( array( "some file", "file", "another file" ), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsMultipleAutoOptionalNotAvailable()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[0]->mandatory = false;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[1]->multiple  = true;

        $this->input->process(
            array( "foo.php", "'some file'" )
        );

        $this->assertEquals( "some file", $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file2"]->value );
        
        // Old handling
        $this->assertEquals( array( "some file" ), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsMultipleIgnore()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "file1" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "file2" );
        $this->input->argumentDefinition[0]->multiple = true;

        $this->input->process(
            array( "foo.php", "'some file'", "file", "\"another file\"" )
        );

        $this->assertEquals( array( "some file", "file", "another file" ), $this->input->argumentDefinition["file1"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["file2"]->value );
        
        // Old handling
        $this->assertEquals( array( "some file", "file", "another file" ), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsTypeInt()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;

        $this->input->process(
            array( "foo.php", 23 )
        );

        $this->assertEquals( 23, $this->input->argumentDefinition["number"]->value );
        
        // Old handling
        $this->assertEquals( array( 23 ), $this->input->getArguments() );
    }

    public function testProcessFailureNewArgumentsTypeInt()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;

        $args = array( "foo.php", "'test'" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentTypeViolationException' );
    }

    public function testProcessSuccessNewArgumentsMultipleTypeInt()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[0]->multiple = true;

        $this->input->process(
            array( "foo.php", 23, 42 )
        );

        $this->assertEquals( array( 23, 42 ), $this->input->argumentDefinition["number"]->value );
        
        // Old handling
        $this->assertEquals( array( 23, 42 ), $this->input->getArguments() );
    }

    public function testProcessFailureNewArgumentsMultipleTypeInt()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[0]->multiple = true;

        $args = array( "foo.php", 23, "test" );

        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentTypeViolationException' );
    }

    public function testProcessSuccessNewArgumentsComplex()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "42", "'test string'", "val1", "val2" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertEquals( 42, $this->input->argumentDefinition["number"]->value );
        $this->assertEquals( "test string", $this->input->argumentDefinition["string"]->value );
        $this->assertEquals( array( "val1", "val2" ), $this->input->argumentDefinition["array"]->value );
        
        // Old handling
        $this->assertEquals( array( 42, "test string", "val1", "val2"), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsHelpOptionSet()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $this->input->getOption( 't' )->isHelpOption = true;

        $args = array( "foo.php", "-t" );

        $res = array( 
            't' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertNull( $this->input->argumentDefinition["number"]->value );
        $this->assertNull( $this->input->argumentDefinition["string"]->value );
        $this->assertNull( $this->input->argumentDefinition["array"]->value );

        $this->assertTrue( $this->input->helpOptionSet() );
        
        // Old handling
        $this->assertEquals( array(), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsDisallowedSuccess()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $this->input->getOption( 't' )->arguments = false;

        $args = array( "foo.php", "-t" );

        $res = array( 
            't' => true,
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertNull( $this->input->argumentDefinition["number"]->value );
        $this->assertNull( $this->input->argumentDefinition["string"]->value );
        $this->assertNull( $this->input->argumentDefinition["array"]->value );

        // Old handling
        $this->assertEquals( array(), $this->input->getArguments() );
    }

    public function testProcessSuccessNewArgumentsDisallowedFailure()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $this->input->getOption( 't' )->arguments = false;

        $args = array( "foo.php", "-t", "--", "23" );

        $res = array( 
            't' => true,
        );
        $this->commonProcessTestFailure( $args, "ezcConsoleOptionArgumentsViolationException" );
    }

    public function testReset()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "42", "'test string'", "val1", "val2" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertEquals( 42, $this->input->argumentDefinition["number"]->value );
        $this->assertEquals( "test string", $this->input->argumentDefinition["string"]->value );
        $this->assertEquals( array( "val1", "val2" ), $this->input->argumentDefinition["array"]->value );
        
        // Old handling
        $this->assertEquals( array( 42, "test string", "val1", "val2"), $this->input->getArguments() );

        $this->input->reset();

        $this->assertEquals( array(), $this->input->getOptionValues() );
        foreach ( $this->input->argumentDefinition as $argument )
        {
            $this->assertNull( $argument->value );
        }
        $this->assertEquals( array(), $this->input->getArguments() );
    }

    public function testProcessTwice()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[1]->mandatory = false;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "42", "'test string'" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertEquals( 42, $this->input->argumentDefinition["number"]->value );
        $this->assertEquals( "test string", $this->input->argumentDefinition["string"]->value );
        
        // Old handling
        $this->assertEquals( array( 42, "test string" ), $this->input->getArguments() );

        // Second run

        $args = array( "foo.php", "-t", '23' );

        $res = array( 
            't' => true
        );
        $this->commonProcessTestSuccess( $args, $res );

        $this->assertEquals( 23, $this->input->argumentDefinition["number"]->value );
        $this->assertEquals( null, $this->input->argumentDefinition["string"]->value );
        
        // Old handling
        $this->assertEquals( array( '23' ), $this->input->getArguments() );
    }

    public function testProcessFailureNewArgumentsComplexType()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "foo", "'test string'", "val1", "val2" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentTypeViolationException' );
    }

    public function testProcessFailureNewArgumentsComplexMissing()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "42" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessFailureNewArgumentsComplexMissing_2()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "string" );
        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "array" );
        $this->input->argumentDefinition[2]->multiple = true;

        $args = array( "foo.php", "-o", "'test file'", "-b", "23", "42", "'test string'" );

        $res = array( 
            'o' => "test file",
            'b' => 23,
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleArgumentMandatoryViolationException' );
    }

    public function testProcessFailureNewArgumentsSwitchedOff()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_INT;

        $args = array( "foo.php", "-v", "--", 23 );

        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionArgumentsViolationException' );
    }

    public function testProcessFailureExistance_1()
    {
        $args = array(
            'foo.php',
            '-q',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionNotExistsException' );
    }
    
    public function testProcessFailureExistance_2()
    {
        $args = array(
            'foo.php',
            '-tools',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionNotExistsException' );
    }
    
    public function testProcessFailureExistance_3()
    {
        $args = array(
            'foo.php',
            '-testingaeiou',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionNotExistsException' );
    }
    
    public function testProcessFailureTypeInt()
    {
        $args = array(
            'foo.php',
            '-b',
            'not_an_int'
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionTypeViolationException' );
    }
    
    // Bug #9046: New bug: [ConsoleTools] Last argument not treated invalid option value
    public function testProcessNoFailureTypeNone()
    {
        $args = array(
            'foo.php',
            '-s',
            'a_parameter'
        );
        $res = array( "s" => true );
        $this->commonProcessTestSuccess( $args, $res );
    }
    
    public function testProcessFailureTypeNone()
    {
        $args = array(
            'foo.php',
            '-s',
            'a_parameter',
            'another_parameter'
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionTypeViolationException' );
    }
    
    public function testProcessFailureNovalue()
    {
        $args = array(
            'foo.php',
            '-o',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMissingValueException' );
    }
    
    public function testProcessFailureMultiple()
    {
        $args = array(
            'foo.php',
            '-d',
            'mars',
            '--destroy',
            'venus',
            
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionTooManyValuesException' );
    }
    
    public function testProcessFailureDependencies()
    {
        $args = array(
            'foo.php',
            '-t',
//            '-o',
//            'bar',
            '--build',
            23,
            '-y',
            'text',
            '--yank',
            'moretext',
            '-c'            // This one depends on -t, -o, -b and -y
        );
        $this->commonProcessTestFailure(
            $args,
            'ezcConsoleOptionDependencyViolationException',
            "The option 'console' depends on the option 'original' but this one was not submitted."
        );
    }

    public function testProcessFailureDependencieValues()
    {
        $rule = new ezcConsoleOptionRule( $this->input->getOption( "y" ), array( "foo", "bar" ) );
        $option = new ezcConsoleOption( "x", "execute" );
        $option->addDependency( $rule );
        $this->input->registerOption( $option );

        $args = array(
            'foo.php',
            '-y',
            'baz',
            '-x',
        );

        $this->commonProcessTestFailure(
            $args,
            'ezcConsoleOptionDependencyViolationException',
            "The option 'execute' depends on the option 'yank' to have a value in 'foo, bar' but this one was not submitted."
        );
    }
    
    public function testProcessFailureExclusions()
    {
        $args = array(
            'foo.php',
            '-t',
            '-o',
            'bar',
            '--build',
            23,
            '--edit'            // This one excludes -t and -y
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionExclusionViolationException' );
    }

    public function testProcessFailureExclusionValues()
    {
        $rule = new ezcConsoleOptionRule( $this->input->getOption( "y" ), array( "foo", "bar" ) );
        $option = new ezcConsoleOption( "x", "execute" );
        $option->addExclusion( $rule );
        $this->input->registerOption( $option );

        $args = array(
            'foo.php',
            '-y',
            'bar',
            '-x',
        );

        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionExclusionViolationException' );
    }
    
    public function testProcessFailureArguments()
    {
        $args = array(
            'foo.php',
            '-t',
            '--visual',         // This one forbids arguments
            '-o',
            'bar',
            'someargument',
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionArgumentsViolationException' );
    }
    
    public function testProcessFailureMandatory()
    {
        $args = array(
            'foo.php',
            '-s',
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'q',
                    'long'      => 'quite',
                    'options'   => array(
                        'mandatory' => true,
                    ),
                )
            )
        );
        $this->commonProcessTestFailure( $args, 'ezcConsoleOptionMandatoryViolationException' );
    }

    public function testGetHelp1()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'No help available.',
            ),
            array( 
                '-s / --subway',
                'No help available.',
            ),
            array( 
                '--carry',
                'No help available.',
            ),
            array( 
                '-v / --visual',
                'No help available.',
            ),
            array( 
                '-o / --original',
                'No help available.',
            ),
            array( 
                '-b / --build',
                'No help available.',
            ),
            array( 
                '-d / --destroy',
                'No help available.',
            ),
            array( 
                '-y / --yank',
                'Some stupid short text.',
            ),
            array( 
                '-c / --console',
                'Some stupid short text.',
            ),
            array( 
                '-e / --edit',
                'No help available.',
            ),
            array( 
                '-n / --new',
                'No help available.',
            ),
        );
        $this->assertEquals( 
            $res,
            $this->input->getHelp(),
            'Help array was not generated correctly.'
        );
    }

    public function testGetHelpWithGrouping()
    {
        $res = array( 
            array( 'Section 1', '' ),
            array( 
                '-t / --testing',
                'No help available.',
            ),
            array( 
                '--carry',
                'No help available.',
            ),
            array( 
                '-b / --build',
                'No help available.',
            ),
            array( '', '' ),
            array( 'Another section', '' ),
            array( 
                '-c / --console',
                'Some stupid short text.',
            ),
            array( 
                '-n / --new',
                'No help available.',
            ),
            array( 
                '-e / --edit',
                'No help available.',
            ),
            array( '', '' ),
            array( 'Third section', '' ),
            array( 
                '-s / --subway',
                'No help available.',
            ),
            array( 
                '-v / --visual',
                'No help available.',
            ),
            array( 
                '-o / --original',
                'No help available.',
            ),
            array( 
                '-d / --destroy',
                'No help available.',
            ),
            array( '', '' ),
            array( 'Last section', '' ),
            array( 
                '-y / --yank',
                'Some stupid short text.',
            ),
        );
        $this->assertEquals( 
            $res,
            $this->input->getHelp(
                false,
                array(),
                array(
                    'Section 1' => array(
                        't', 'carry', 'build'
                    ),
                    'Another section' => array(
                        'c', 'new', 'edit'
                    ),
                    'Third section' => array(
                        'subway', 'v', 'o', 'd',
                    ),
                    'Last section' => array(
                        'y',
                    ),
                )
            ),
            'Help array was not generated correctly.'
        );
    }

    public function testGetHelpNewArgs()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'No help available.',
            ),
            array( 
                '-s / --subway',
                'No help available.',
            ),
            array( 
                '--carry',
                'No help available.',
            ),
            array( 
                '-v / --visual',
                'No help available.',
            ),
            array( 
                '-o / --original',
                'No help available.',
            ),
            array( 
                '-b / --build',
                'No help available.',
            ),
            array( 
                '-d / --destroy',
                'No help available.',
            ),
            array( 
                '-y / --yank',
                'Some stupid short text.',
            ),
            array( 
                '-c / --console',
                'Some stupid short text.',
            ),
            array( 
                '-e / --edit',
                'No help available.',
            ),
            array( 
                '-n / --new',
                'No help available.',
            ),
            array(
                "Arguments:",
                "",
            ),
            array(
                '<string:text>',
                'A text.',
            ),
            array(
                '<int:number>',
                'A number.',
            ),
        );

        $this->input->argumentDefinition = new ezcConsoleArguments();
        
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[0]->shorthelp = 'A text.';
        $this->input->argumentDefinition[0]->longhelp = 'This argument is a simple text.';

        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->shorthelp = 'A number.';
        $this->input->argumentDefinition[1]->longhelp = 'This argument is a number.';

        $this->assertEquals( 
            $res,
            $this->input->getHelp(),
            'Help array was not generated correctly.'
        );
    }
    
    public function testGetHelp2()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-s / --subway',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '--carry',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-v / --visual',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-o / --original',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-b / --build',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-d / --destroy',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-y / --yank',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-c / --console',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-e / --edit',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-n / --new',
                'Sorry, there is no help text available for this parameter.',
            ),
        );
        $this->assertEquals( 
            $res,
            $this->input->getHelp( true ),
            'Help array was not generated correctly.'
        );
        
    }
    
    public function testGetHelp2NewArgs()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-s / --subway',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '--carry',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-v / --visual',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-o / --original',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-b / --build',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-d / --destroy',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-y / --yank',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-c / --console',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-e / --edit',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-n / --new',
                'Sorry, there is no help text available for this parameter.',
            ),
            array(
                "Arguments:",
                "",
            ),
            array(
                '<string:text>',
                'This argument is a simple text.',
            ),
            array(
                '<int:number>',
                'This argument is a number.',
            ),
        );

        $this->input->argumentDefinition = new ezcConsoleArguments();
        
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[0]->shorthelp = 'A text.';
        $this->input->argumentDefinition[0]->longhelp = 'This argument is a simple text.';

        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->shorthelp = 'A number.';
        $this->input->argumentDefinition[1]->longhelp = 'This argument is a number.';

        $this->assertEquals( 
            $res,
            $this->input->getHelp( true ),
            'Help array was not generated correctly.'
        );
        
    }
    
    public function testGetHelp2NewArgsOptional()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-s / --subway',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '--carry',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-v / --visual',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-o / --original',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-b / --build',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-d / --destroy',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-y / --yank',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-c / --console',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-e / --edit',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-n / --new',
                'Sorry, there is no help text available for this parameter.',
            ),
            array(
                "Arguments:",
                "",
            ),
            array(
                '<string:text>',
                'This argument is a simple text. (optional)',
            ),
            array(
                '<int:number>',
                "This argument is a number. (optional, default = '23')",
            ),
            array(
                '<string:misc>',
                "Testing multiple values. (optional, default = 'foo' 'bar' 'baz')",
            ),
        );

        $this->input->argumentDefinition = new ezcConsoleArguments();
        
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[0]->shorthelp = 'A text.';
        $this->input->argumentDefinition[0]->longhelp  = 'This argument is a simple text.';
        $this->input->argumentDefinition[0]->mandatory = false;

        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->shorthelp = 'A number.';
        $this->input->argumentDefinition[1]->longhelp  = 'This argument is a number.';
        $this->input->argumentDefinition[1]->mandatory = false;
        $this->input->argumentDefinition[1]->default   = 23;

        $this->input->argumentDefinition[2] = new ezcConsoleArgument( "misc" );
        $this->input->argumentDefinition[2]->shorthelp = 'A misc argument.';
        $this->input->argumentDefinition[2]->longhelp  = 'Testing multiple values.';
        $this->input->argumentDefinition[2]->multiple  = true;
        $this->input->argumentDefinition[2]->mandatory = false;
        $this->input->argumentDefinition[2]->default   = array( "foo", "bar", "baz" );

        $this->assertEquals( 
            $res,
            $this->input->getHelp( true ),
            'Help array was not generated correctly.'
        );
        
    }
    
    public function testGetHelp3()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'No help available.',
            ),
            array( 
                '-s / --subway',
                'No help available.',
            ),
            array( 
                '-v / --visual',
                'No help available.',
            ),
        );
        $this->assertEquals( 
            $res,
            $this->input->getHelp(false, array( 't', 's', 'v' ) ),
            'Help array was not generated correctly.'
        );
    }
    
    public function testGetHelp4()
    {
        $res = array( 
            array( 
                '-t / --testing',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-s / --subway',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-y / --yank',
                'Some even more stupid, but somewhat longer long describtion.',
            ),
            array( 
                '-e / --edit',
                'Sorry, there is no help text available for this parameter.',
            ),
            array( 
                '-n / --new',
                'Sorry, there is no help text available for this parameter.',
            ),
        );
        $this->assertEquals( 
            $res,
            $this->input->getHelp( true, array( 't', 'subway', 'yank', 'e', 'n' ) ),
            'Help array was not generated correctly.'
        );
        
    }
    
    public function testGetSynopsis()
    {
        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [--carry] [-v] [-o <string>] [-b 42] [-d "world"] [-y <string>] [-c] [-e] [-n]  [[--] <args>]',
            $this->input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }

    // Issue #012561 : getSynopsis() bugs when at least 2 options don't have short-names.
    public function testGetSynopsisLongOptionsWithoutShortNames()
    {
        $input = new ezcConsoleInput();
        $input->registerOption(
            new ezcConsoleOption(
                "",
                "set-dericktory",
                ezcConsoleInput::TYPE_NONE
            )
        );

        $input->registerOption(
            new ezcConsoleOption(
                "",
                "set-directoby",
                ezcConsoleInput::TYPE_NONE
            )
        );

        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [--set-dericktory] [--set-directoby]  [[--] <args>]',
            $input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }

    public function testGetSynopsisNewArgumentsSimple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;

        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [--carry] [-v] [-o <string>] [-b 42] [-d "world"] [-y <string>] [-c] [-e] [-n] [--] <string:text> <int:number>',
            $this->input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetSynopsisNewArgumentsMultiple()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->multiple = true;

        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [--carry] [-v] [-o <string>] [-b 42] [-d "world"] [-y <string>] [-c] [-e] [-n] [--] <string:text> <int:number> [<int:number> ...]',
            $this->input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetSynopsisNewArgumentsOptional()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->mandatory = false;

        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [--carry] [-v] [-o <string>] [-b 42] [-d "world"] [-y <string>] [-c] [-e] [-n] [--] <string:text> [<int:number>]',
            $this->input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetSynopsisNewArgumentsMultipleOptional()
    {
        $this->input->argumentDefinition = new ezcConsoleArguments();
        $this->input->argumentDefinition[0] = new ezcConsoleArgument( "text" );
        $this->input->argumentDefinition[0]->mandatory = false;
        $this->input->argumentDefinition[1] = new ezcConsoleArgument( "number" );
        $this->input->argumentDefinition[1]->type = ezcConsoleInput::TYPE_INT;
        $this->input->argumentDefinition[1]->multiple = true;

        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [--carry] [-v] [-o <string>] [-b 42] [-d "world"] [-y <string>] [-c] [-e] [-n] [--] [<string:text>] [<int:number>] [<int:number> ...]',
            $this->input->getSynopsis(),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetHelpTable()
    {
        $output = new ezcConsoleOutput();
        
        $res = new ezcConsoleTable( $output, 80 ); 
        $res[0][0]->content = '-t / --testing';
        $res[0][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $res[1][0]->content = '-s / --subway';
        $res[1][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $res[2][0]->content = '-y / --yank';
        $res[2][1]->content = 'Some even more stupid, but somewhat longer long describtion.';
                
        $res[3][0]->content = '-e / --edit';
        $res[3][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $table = new ezcConsoleTable( $output, 80 );
        $table = $this->input->getHelpTable( $table, true, array( 't', 'subway', 'yank', 'e' ) );
        $this->assertEquals(
            $res,
            $table,
            'Help table not generated correctly.'
        );
    }
    
    public function testGetHelpTableGrouping()
    {
        $output = new ezcConsoleOutput();
        
        $res = new ezcConsoleTable( $output, 80 ); 
        $res[0][0]->content = 'Section uno';
        $res[0][1]->content = '';

        $res[1][0]->content = '-t / --testing';
        $res[1][1]->content = 'Sorry, there is no help text available for this parameter.';

        $res[2][0]->content = '';
        $res[2][1]->content = '';

        $res[3][0]->content = 'Section 2';
        $res[3][1]->content = '';

        $res[4][0]->content = '-e / --edit';
        $res[4][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $res[5][0]->content = '-s / --subway';
        $res[5][1]->content = 'Sorry, there is no help text available for this parameter.';

        $res[6][0]->content = '';
        $res[6][1]->content = '';

        $res[7][0]->content = 'Final section';
        $res[7][1]->content = '';
                
        $res[8][0]->content = '-y / --yank';
        $res[8][1]->content = 'Some even more stupid, but somewhat longer long describtion.';
                
                
        $table = new ezcConsoleTable( $output, 80 );
        $table = $this->input->getHelpTable(
            $table,
            true,
            array( 't', 'subway', 'yank', 'e' ),
            array(
                'Section uno' => array(
                    't'
                ),
                'Section 2' => array(
                    'e', 'subway'
                ),
                'Final section' => array(
                    'y',
                ),
            )
        );
        $this->assertEquals(
            $res,
            $table,
            'Help table not generated correctly.'
        );
    }

    public function testGetHelpTableDefaultParameters()
    {
        $output = new ezcConsoleOutput();
        
        $res = new ezcConsoleTable( $output, 80 ); 
        $res[0][0]->content = '-t / --testing';
        $res[0][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $res[1][0]->content = '-s / --subway';
        $res[1][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $res[2][0]->content = '-y / --yank';
        $res[2][1]->content = 'Some even more stupid, but somewhat longer long describtion.';
                
        $res[3][0]->content = '-e / --edit';
        $res[3][1]->content = 'Sorry, there is no help text available for this parameter.';
                
        $table = new ezcConsoleTable( $output, 80 );
        $table = $this->input->getHelpTable( $table );

        $this->assertEquals( 11, sizeof( $table ), "Expected 11 elements in the generated HelpTable" );
    }

    public function testGetHelpTextBinarySafe()
    {
        $input = new ezcConsoleInput();
        $input->registerOption(
            new ezcConsoleOption(
                'ö',
                'öder',
                ezcConsoleInput::TYPE_NONE,
                null,
                false,
                'ööö äää ööö äää',
                'ööö äää ööö äää ööö äää ööö äää ööö äää ööö äää'
            )
        );

        $res = "Usage: $ {$_SERVER['argv'][0]} [-ö]  [[--] <args>]" . PHP_EOL
. 'Test with UTF-8' . PHP_EOL
. 'characters...' . PHP_EOL
. '' . PHP_EOL
. '-ö / --öder  ööö äää' . PHP_EOL
. '             ööö äää' . PHP_EOL
. '             ööö äää' . PHP_EOL
. '             ööö äää' . PHP_EOL
. '             ööö äää' . PHP_EOL
. '             ööö äää' . PHP_EOL;

        $this->assertEquals(
            $res,
            $input->getHelpText( 'Test with UTF-8 characters...', 20, true ),
            'Help text not generated correctly.'
        );
    }

    public function testGetHelpText()
    {
        $res = "Usage: $ {$_SERVER['argv'][0]} [-y <string>] [-e]  [[--] <args>]" . PHP_EOL
. 'Lala' . PHP_EOL
. '' . PHP_EOL
. '-y / --yank  Some' . PHP_EOL
. '             even' . PHP_EOL
. '             more' . PHP_EOL
. '             stupid,' . PHP_EOL
. '             but' . PHP_EOL
. '             somewhat' . PHP_EOL
. '             longer' . PHP_EOL
. '             long' . PHP_EOL
. '             describtion.' . PHP_EOL
. '-e / --edit  Sorry,' . PHP_EOL
. '             there' . PHP_EOL
. '             is no' . PHP_EOL
. '             help' . PHP_EOL
. '             text' . PHP_EOL
. '             available' . PHP_EOL
. '             for' . PHP_EOL
. '             this' . PHP_EOL
. '             parameter.' . PHP_EOL;

        $this->assertEquals(
            $res,
            $this->input->getHelpText( 'Lala', 20, true, array( 'e', 'y' ) ),
            'Help text not generated correctly.'
        );
    }
    
    public function testGetSynopsis1()
    {
        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [-o <string>]  [[--] <args>]',
            $this->input->getSynopsis( array( 't', 's', 'o' ) ),
            'Program synopsis not generated correctly.'
        );
    }
    
    /**
     * Tests bug #7923. 
     * 
     * @return void
     */
    public function testGetSynopsis2()
    {
        $this->assertEquals( 
            '$ '.$_SERVER['argv'][0].' [-t] [-s] [-v]  [[--] <args>]',
            $this->input->getSynopsis( array( 't', 's', 'v' ) ),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetSynopsis3()
    {
        $this->assertEquals( 
            '$ ' . $_SERVER['argv'][0] . ' [-s] [-b 42]  [[--] <args>]',
            $this->input->getSynopsis( array( 'b', 's' ) ),
            'Program synopsis not generated correctly.'
        );
    }
    
    public function testGetSynopsis4()
    {
        $this->input->registerOption(
            new ezcConsoleOption(
                "x",
                "execute",
                ezcConsoleInput::TYPE_INT
            )
        );
        $this->assertEquals( 
            '$ ' . $_SERVER['argv'][0] . ' [-s] [-x <int>]  [[--] <args>]',
            $this->input->getSynopsis( array( 'x', 's' ) ),
            'Program synopsis not generated correctly.'
        );
    }

    public function testHelpOptionSet()
    {
        $args = array(
            'foo.php',
            '-h',
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'q',
                    'long'      => 'quite',
                    'options'   => array(
                        'mandatory' => true,
                    ),
                )
            )
        );
        $this->input->registerOption(
            $this->createFakeOption(
                array( 
                    'short'     => 'h',
                    'long'      => 'help',
                    'options'   => array(
                        'isHelpOption' => true,
                    ),
                )
            )
        );
        $res = array( 
            'h' => true,
        );

        $this->assertFalse( $this->input->helpOptionSet(), "Help option seems to be set, algthough nothing was processed." );
        $this->commonProcessTestSuccess( $args, $res );
        $this->assertTrue( $this->input->helpOptionSet(), "Help option seems not to be set, algthough it was." );
    }

    public function testDependOptionNoShortName()
    {
        $inputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'input' )
        );
        $outputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'output' )
        );

        $inputOpt->addDependency(
            new ezcConsoleOptionRule( $outputOpt )
        );
        $outputOpt->addDependency(
            new ezcConsoleOptionRule( $inputOpt )
        );
        
        $args = array( 'somescript', '--input' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}
        
        $args = array( 'somescript', '--output' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}
    }

    // Issue #014803: Problem with ezcConsoleOption when short option name is empty
    public function testExcludeOptionNoShortName()
    {
        $inputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'input' )
        );
        $outputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'output' )
        );

        $inputOpt->addExclusion(
            new ezcConsoleOptionRule( $outputOpt )
        );
        $outputOpt->addExclusion(
            new ezcConsoleOptionRule( $inputOpt )
        );
        
        $args = array( 'somescript', '--input' );

        // Should not throw an exception
        $this->input->process( $args );
        
        $args = array( 'somescript', '--output' );

        // Should not throw an exception
        $this->input->process( $args );
    }

    public function testDependOptionValueNotSetNoShortName()
    {
        $inputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'input' )
        );
        $outputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'output' )
        );

        $inputOpt->addDependency(
            new ezcConsoleOptionRule( $outputOpt, array( 'foo', 'bar' ) )
        );
        $outputOpt->addDependency(
            new ezcConsoleOptionRule( $inputOpt, array( 'foo', 'bar' ) )
        );
        
        $args = array( 'somescript', '--input' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}
        
        $args = array( 'somescript', '--output' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}

    }

    public function testDependOptionValueWrongValueNoShortName()
    {
        $inputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'input', ezcConsoleInput::TYPE_STRING )
        );
        $outputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'output', ezcConsoleInput::TYPE_STRING )
        );

        $inputOpt->addDependency(
            new ezcConsoleOptionRule( $outputOpt, array( 'foo', 'bar' ) )
        );
        $outputOpt->addDependency(
            new ezcConsoleOptionRule( $inputOpt, array( 'foo', 'bar' ) )
        );
        
        $args = array( 'somescript', '--output=lala', '--input=lala' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}
        
        $args = array( 'somescript', '--input=lala', '--output=lala' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        } catch ( ezcConsoleOptionDependencyViolationException $e ) {}

    }

    public function testExcludeOptionValueWrongValueNoShortName()
    {
        $inputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'input', ezcConsoleInput::TYPE_STRING )
        );
        $outputOpt = $this->input->registerOption(
            new ezcConsoleOption( '', 'output', ezcConsoleInput::TYPE_STRING )
        );

        $inputOpt->addExclusion(
            new ezcConsoleOptionRule( $outputOpt, array( 'foo', 'bar' ) )
        );
        $outputOpt->addExclusion(
            new ezcConsoleOptionRule( $inputOpt, array( 'foo', 'bar' ) )
        );
        
        $args = array( 'somescript', '--output=foo', '--input=lala' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated exclusion.' );
        }
        catch ( ezcConsoleOptionExclusionViolationException $e )
        {
            $this->assertEquals(
                "The option 'input' excludes the option 'output' to have a value in 'foo, bar' but this one was submitted.",
                $e->getMessage()
            );
        }
        
        $args = array( 'somescript', '--output=lala', '--input=bar' );

        try
        {
            $this->input->process( $args );
            $this->fail( 'Processing did not throw an exception on violated dependency.' );
        }
        catch ( ezcConsoleOptionExclusionViolationException $e )
        {
            $this->assertEquals(
                "The option 'output' excludes the option 'input' to have a value in 'foo, bar' but this one was submitted.",
                $e->getMessage()
            );
        }

    }

    public function testDependencyOptionNotSet()
    {
        $aOpt = $this->input->registerOption(
            new ezcConsoleOption( 'a', 'abbrev', ezcConsoleInput::TYPE_NONE )
        );
        $aOpt->addDependency(
            new ezcConsoleOptionRule( $this->input->getOption( 't' ), array(), false )
        );

        $this->commonProcessTestFailure(
            array(
                'foo.php'
            ),
            'ezcConsoleOptionDependencyViolationException'
        );
    }

    public function testExclusionOptionNotSet()
    {
        $aOpt = $this->input->registerOption(
            new ezcConsoleOption( 'a', 'abbrev', ezcConsoleInput::TYPE_NONE )
        );
        $aOpt->addExclusion(
            new ezcConsoleOptionRule( $this->input->getOption( 't' ), array(), false )
        );

        $this->commonProcessTestFailure(
            array(
                'foo.php',
                '-t'
            ),
            'ezcConsoleOptionExclusionViolationException'
        );
    }

    private function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue)
        {
            if (array_key_exists($mKey, $aArray2))
            {
                if (is_array($mValue))
                {
                      $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                      if (count($aRecursiveDiff))
                      {
                          $aReturn[$mKey] = $aRecursiveDiff;
                      }
                }
                else
                {
                    if ($mValue != $aArray2[$mKey])
                    {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            }
            else
            {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }
    
    private function commonProcessTestSuccess( $args, $res )
    {
        $this->input->process( $args );
        $values = $this->input->getOptionValues();
        $this->assertTrue( count( $this->arrayRecursiveDiff( $res, $values ) ) == 0, 'Parameters processed incorrectly.' );
    }
    
    private function commonProcessTestFailure( $args, $exceptionClass, $message = null )
    {
        try 
        {
            $this->input->process( $args );
        }
        catch ( ezcConsoleException $e )
        {
            $this->assertSame(
                $exceptionClass,
                get_class( $e ),
                'Wrong exception thrown for invalid parameter submission. Expected class <'.$exceptionClass.'>, received <'.get_class( $e ).'>'
            );

            if ( $message !== null )
            {
                $this->assertEquals(
                    $message,
                    $e->getMessage(),
                    'Exception message incorrect.'
                );
            }
            return;
        }
        $this->fail( 'Exception not thrown for invalid parameter submition.' );
    }

    private function argumentsProcessTestSuccess( $args, $res )
    {
        $this->input->process( $args );
        $this->assertEquals(
            $res,
            $this->input->getArguments(),
            'Arguments not parsed correctly.'
        );
    }
}
?>
