<?php
namespace Test\Unit;

use Utils\CalendarTools;

class CalenderToolsUnit extends \TestTemplate
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testGetYearFromDate()
    {
        $date = '2019-08-12';
        self::assertEquals('2019', CalendarTools::getYearFromDate($date));
    }

    public function testAssignYearToDate()
    {
        $date = '2019-08-12';
        self::assertEquals('2017-08-12', CalendarTools::assignYearToDate($date, 2017));
    }
}
