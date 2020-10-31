<?php
/**
 * Tests for the ezcConsoleStringTools class.
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
 * Test suite for ezcConsoleStringTools class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleStringToolsTest extends ezcTestCase
{
    private static $provideTestWordWrap;

    private static $provideTestStrPad;

    /**
     * testWordWrap 
     * 
     * @param mixed $input 
     * @param mixed $expected 
     * @return void
     *
     * @dataProvider provideTestWordWrap
     */
    public function testWordWrap( $input, $expected )
    {
        $tools = new ezcConsoleStringTool();
        $actual = call_user_func_array(
            array(
                $tools,
                'wordwrap'
            ),
            $input
        );
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function provideTestWordWrap()
    {
        if ( !isset( self::$provideTestWordWrap ) )
        {
            self::$provideTestWordWrap = require dirname( __FILE__ ) . '/data/string_tools_wordwrap_data.php';
        }
        return self::$provideTestWordWrap;
    }

    /**
     * testStrPad 
     * 
     * @param mixed $input 
     * @param mixed $expected 
     * @return void
     *
     * @dataProvider provideTestStrPad
     */
    public function testStrPad( $input, $expected )
    {
        $tools = new ezcConsoleStringTool();
        $actual = call_user_func_array(
            array(
                $tools,
                'strPad'
            ),
            $input
        );
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function provideTestStrPad()
    {
        if ( !isset( self::$provideTestStrPad ) )
        {
            self::$provideTestStrPad = require dirname( __FILE__ ) . '/data/string_tools_strpad_data.php';
        }
        return self::$provideTestStrPad;
    }

    public static function suite()
    {
		return new PHPUnit\Framework\TestSuite( __CLASS__ );
    }
}
?>
