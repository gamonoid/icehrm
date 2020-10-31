<?php
/**
 * ezcConsoleOptionRuleTest class.
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
 * Test suite for ezcConsoleOption class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleOptionRuleTest extends ezcTestCase
{

	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleOptionRuleTest" );
	}

    public function testGetAccessSuccess()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a", "b", "c" ) );

        $this->assertSame( $option, $rule->option );
        $this->assertEquals( array( "a", "b", "c" ), $rule->values );
        $this->assertTrue( $rule->ifSet );
    }

    public function testGetAccessFailure()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a", "b", "c" ) );

        try
        {
            $foo = $rule->nonExistent;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return true;
        }
        $this->fail( "Exception not thrown on access of invalid property." );
    }
    
    public function testSetAccessSuccess()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        $rule->option = $option;
        $rule->values = array( "a", "b", "c" );
        $rule->ifSet  = false;

        $this->assertSame( $option, $rule->option );
        $this->assertEquals( array( "a", "b", "c" ), $rule->values );
        $this->assertFalse( $rule->ifSet );
    }

    public function testSetAccessFailureOption()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        try
        {
            $rule->option = array();
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid value for ezcConsoleOptionRule->option." );
    }

    public function testSetAccessFailureValues()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        try
        {
            $rule->values = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid value for ezcConsoleOptionRule->values." );
    }

    public function testSetAccessFailureIfSet()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        try
        {
            $rule->ifSet = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid value for ezcConsoleOptionRule->ifSet." );
    }

    public function testSetAccessFailureNonExsitent()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        try
        {
            $rule->nonExistent = 23;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid value for ezcConsoleOptionRule->nonExistent." );
    }

    public function testIssetAccessSuccess()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        $this->assertTrue( isset( $rule->option ) );
        $this->assertTrue( isset( $rule->values ) );
        $this->assertTrue( isset( $rule->ifSet ) );
    }

    public function testIssetAccessFailure()
    {
        $option = new ezcConsoleOption( "a", "aaa" );
        $rule = new ezcConsoleOptionRule( $option, array( "a" ) );

        $this->assertFalse( isset( $rule->nonExsistent ) );
    }
}

?>
