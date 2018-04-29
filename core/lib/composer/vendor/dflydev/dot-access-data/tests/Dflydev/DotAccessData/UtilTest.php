<?php

/*
 * This file is a part of dflydev/dot-access-data.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\DotAccessData;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAssoc()
    {
        $this->assertTrue(Util::isAssoc(array('a' => 'A',)));
        $this->assertTrue(Util::isAssoc(array()));
        $this->assertFalse(Util::isAssoc(array(1 => 'One',)));
    }

    /**
     * @dataProvider mergeAssocArrayProvider
     */
    public function testMergeAssocArray($message, $to, $from, $clobber, $expectedResult)
    {
        $result = Util::mergeAssocArray($to, $from, $clobber);
        $this->assertEquals($expectedResult, $result, $message);
    }

    public function mergeAssocArrayProvider()
    {
        return array(

            array(
                'Clobber should replace to value with from value for strings (shallow)',
                // to
                array('a' => 'A'),
                // from
                array('a' => 'B'),
                // clobber
                true,
                // expected result
                array('a' => 'B'),
            ),

            array(
                'Clobber should replace to value with from value for strings (deep)',
                // to
                array('a' => array('b' => 'B',),),
                // from
                array('a' => array('b' => 'C',),),
                // clobber
                true,
                // expected result
                array('a' => array('b' => 'C',),),
            ),

            array(
                'Clobber should  NOTreplace to value with from value for strings (shallow)',
                // to
                array('a' => 'A'),
                // from
                array('a' => 'B'),
                // clobber
                false,
                // expected result
                array('a' => 'A'),
            ),

            array(
                'Clobber should NOT replace to value with from value for strings (deep)',
                // to
                array('a' => array('b' => 'B',),),
                // from
                array('a' => array('b' => 'C',),),
                // clobber
                false,
                // expected result
                array('a' => array('b' => 'B',),),
            ),

            array(
                'Associative arrays should be combined',
                // to
                array('a' => array('b' => 'B',),),
                // from
                array('a' => array('c' => 'C',),),
                // clobber
                null,
                // expected result
                array('a' => array('b' => 'B', 'c' => 'C',),),
            ),

            array(
                'Arrays should be replaced (with clobber enabled)',
                // to
                array('a' => array('b', 'c',)),
                // from
                array('a' => array('B', 'C',),),
                // clobber
                true,
                // expected result
                array('a' => array('B', 'C',),),
            ),

            array(
                'Arrays should be NOT replaced (with clobber disabled)',
                // to
                array('a' => array('b', 'c',)),
                // from
                array('a' => array('B', 'C',),),
                // clobber
                false,
                // expected result
                array('a' => array('b', 'c',),),
            ),
        );
    }
}
