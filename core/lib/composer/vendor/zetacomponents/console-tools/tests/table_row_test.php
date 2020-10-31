<?php
/**
 * ezcConsoleTableRowTest class.
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
 * Test suite for ezcConsoleTableRow class.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
class ezcConsoleTableRowTest extends ezcTestCase
{
	public static function suite()
	{
		return new PHPUnit\Framework\TestSuite( "ezcConsoleTableRowTest" );
	}

    public function testCtorSuccess_1()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        $this->assertEquals( 
            2,
            count( $row ),
            "ezcConsoleTableRow not correctly created."
        );
    }
    
    public function testCtorSuccess_2()
    {
        $row = new ezcConsoleTableRow();
        $this->assertEquals( 
            0,
            count( $row ),
            "ezcConsoleTableRow not correctly created."
        );
    }
    
    public function testCtorFailure()
    {
        $exceptionThrown = false;
        try
        {
            $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), 'foo' );
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invald constructor parameter." );
    }
    
    public function testAppend()
    {
        $row = new ezcConsoleTableRow();
        $row[]->content = 'foo';
        $this->assertTrue( 
            $row[0] instanceof ezcConsoleTableCell,
            "ezcConsoleTableCell not correctly created on write access."
        );
    }
    
    public function testOntheflyCreationRead_1()
    {
        $row = new ezcConsoleTableRow();
        $this->assertTrue( 
            $row[0] instanceof ezcConsoleTableCell,
            "ezcConsoleTableCell not correctly created on write access."
        );
    }
    
    public function testOntheflyCreationRead_2()
    {
        $row = new ezcConsoleTableRow();
        $row[0]; $row[1]; $row[2];
        $this->assertTrue( 
            count( $row ) == 3,
            "ezcConsoleTableCell not correctly created on write access."
        );
    }

    public function testOntheflyCreationRead_3()
    {
        $row = new ezcConsoleTableRow();
        $row[0]->content = 'test';
        $row[1]->format = 'test';
        $row[2]->align = ezcConsoleTable::ALIGN_CENTER;
        $this->assertTrue( 
            count($row) == 3,
            "ezcConsoleTableCell not correctly created on write access."
        );
    }

    public function testOntheflyCreationWrite_1()
    {
        $row = new ezcConsoleTableRow();
        $row[0] = new ezcConsoleTableCell();
        $row[0]->content = 'test';
        $this->assertTrue( 
            count($row) == 1 && $row[0] instanceof ezcConsoleTableCell && $row[0]->content === 'test',
            "ezcConsoleTableCell not correctly created on write access."
        );
    }
    
    public function testNoOntheflyCreationIsset()
    {
        $row = new ezcConsoleTableRow();
        $this->assertEquals( 
            isset( $row[0] ),
            false,
            "ezcConsoleTableCell created on isset access."
        );
        $this->assertEquals( 
            count($row),
            0,
            "ezcConsoleTableCell created on isset access."
        );
    } 

    public function testForeach_1()
    {
        $row = new ezcConsoleTableRow();
        for ( $i = 0; $i < 10; $i++ )
        {
            $row[$i]->content = 'Is '.$i;
        }
        $this->assertEquals( 
            count( $row ),
            10,
            "ezcConsoleTableCells not correctly created on write access."
        );
        foreach ( $row as $id => $cell )
        {
            $this->assertEquals( 
                'Is ' . $id,
                $cell->content,
                "Cell with wrong content found on iteration."
            );
        }
    }

    public function testForeach_2()
    {
        $row = new ezcConsoleTableRow();
        for ( $i = 0; $i < 20; $i += 2 )
        {
            $row[$i]->content = 'Is '.$i;
        }
        $this->assertEquals( 
            count( $row ),
            19,
            "ezcConsoleTableCells."
        );
        foreach ( $row as $id => $cell );
        {
            $this->assertEquals( 
                'Is ' . $id,
                $cell->content,
                "Cell with wrong content found on iteration."
            );
        }
    }

    public function testCount_1()
    {
        $row = new ezcConsoleTableRow();
        $row[0]->content = "0";
        $this->assertEquals( 
            1,
            count( $row ),
            "Did not count number of cells correctly"
        );
    }

    public function testCount_2()
    {
        $row = new ezcConsoleTableRow();
        $row[1]->content = "0";
        $this->assertEquals( 
            2,
            count( $row ),
            "Did not count number of cells correctly"
        );
    }

    public function testCount_3()
    {
        $row = new ezcConsoleTableRow();
        $row[10]->content = "0";
        $this->assertEquals( 
            11,
            count( $row ),
            "Did not count number of cells correctly"
        );
    }

    public function testNotSetAllCellsProperties_1()
    {
        $row = new ezcConsoleTableRow();
        for ( $i = 0; $i < 10; $i++ )
        {
            $row[$i]->content = (string) $i;
        }
        
        $row->align = ezcConsoleTable::ALIGN_CENTER;
        
        foreach ( $row as $cell )
        {
            $this->assertEquals( 
                ezcConsoleTable::ALIGN_DEFAULT,
                $cell->align,
                "Did not set alignment correctly for all contained cells."
            );
        }
    }

    public function testNotSetAllCellsProperties_2()
    {
        $row = new ezcConsoleTableRow();
        for ( $i = 0; $i < 10; $i++ )
        {
            $row[$i]->content = (string) $i;
        }
        
        $row->format = 'headline';
        
        foreach ( $row as $cell )
        {
            $this->assertEquals( 
                'default',
                $cell->format,
                "Did not set alignment correctly for all contained cells."
            );
        }
    }

    public function testGetAccessSuccess()
    {
        $row = new ezcConsoleTableRow();
        $this->assertEquals( "default", $row->borderFormat );
        $this->assertEquals( "default", $row->format );
        $this->assertEquals( ezcConsoleTable::ALIGN_DEFAULT, $row->align );
    }

    public function testGetAccessFailure()
    {
        $row = new ezcConsoleTableRow();
        try
        {
            echo $row->foo;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on get access of invalid property foo." );
    }

    public function testSetAccessSuccess()
    {
        $row = new ezcConsoleTableRow();
        
        $row->borderFormat = "foo";
        $row->format = "foo";
        $row->align = ezcConsoleTable::ALIGN_RIGHT;
        
        $this->assertEquals( "foo", $row->borderFormat );
        $this->assertEquals( "foo", $row->format );
        $this->assertEquals( ezcConsoleTable::ALIGN_RIGHT, $row->align );
    }

    public function testSetAccessFailure()
    {
        $row = new ezcConsoleTableRow();

        $exceptionThrown = false;
        try
        {
            $row->borderFormat = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property borderFormat." );

        $exceptionThrown = false;
        try
        {
            $row->format = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property format." );

        $exceptionThrown = false;
        try
        {
            $row->align = 23;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on invalid value for property align." );

        $exceptionThrown = false;
        try
        {
            $row->foo = 23;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on set access to invalid property foo." );
    }

    public function testIssetAccess()
    {
        $row = new ezcConsoleTableRow();
        $this->assertTrue( isset( $row->format ) );
        $this->assertTrue( isset( $row->borderFormat ) );
        $this->assertTrue( isset( $row->align ) );
        $this->assertFalse( isset( $row->foo ) );
    }

    public function testOffsetExistsSuccess()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        $this->assertTrue( isset( $row[0] ) );
        $this->assertTrue( isset( $row[1] ) );
        $this->assertTrue( isset( $row[2] ) );
    }

    public function testOffsetExistsFailure()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );

        try
        {
            isset( $row["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid offset to offsetExists()." );
    }

    public function testOffsetGetSuccess()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        $this->assertInstanceOf( "ezcConsoleTableCell", $row[0] );
        $this->assertInstanceOf( "ezcConsoleTableCell", $row[1] );
        $this->assertInstanceOf( "ezcConsoleTableCell", $row[2] );
    }
    
    public function testOffsetGetFailure()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );

        try
        {
             var_dump( $row["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid offset to offsetGet()." );
    }

    public function testOffsetSetSuccess()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        $newCell = new ezcConsoleTableCell();

        $row[1] = $newCell;
        $this->assertSame( $newCell, $row[1] );
        
        $row[] = $newCell;
        $this->assertSame( $newCell, $row[3] );
    }
    
    public function testOffsetSetFailure()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );

        $exceptionThrown = false;
        try
        {
            $row["foo"] = new ezcConsoleTableCell();
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on offsetSet() with invalid offset." );

        $exceptionThrown = false;
        try
        {
            $row[1] = true;
        }
        catch ( ezcBaseValueException $e )
        {
            $exceptionThrown = true;
        }
        $this->assertTrue( $exceptionThrown, "Exception not thrown on offsetSet() with invalid value." );
    }

    public function testOffsetUnsetSuccess()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        
        unset( $row[1] );

        $this->assertFalse( isset( $row[1] ) );
    }
    
    public function testOffsetUnsetFailure()
    {
        $row = new ezcConsoleTableRow( new ezcConsoleTableCell(), new ezcConsoleTableCell(), new ezcConsoleTableCell() );
        
        try
        {
            unset( $row["foo"] );
        }
        catch ( ezcBaseValueException $e )
        {
            return;
        }
        $this->fail( "Exception not thrown on invalid offset to offsetUnset()." );
    }
}
?>
