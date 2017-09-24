<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:17 PM
 */

namespace TimeSheets\Common\Model;

use Model\BaseModel;
use Utils\CalendarTools;

class EmployeeTimeSheet extends BaseModel
{
    public $table = 'EmployeeTimeSheets';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function getTotalTime()
    {

        $start = $this->date_start . " 00:00:00";
        $end = $this->date_end . " 23:59:59";

        $timeEntry = new EmployeeTimeEntry();
        $list = $timeEntry->Find(
            "employee = ? and ((date_start >= ? and date_start <= ?) or (date_end >= ? and date_end <= ?))",
            array($this->employee, $start, $end, $start, $end)
        );

        $seconds = 0;

        foreach ($list as $entry) {
            $secondsTemp = (strtotime($entry->date_end) - strtotime($entry->date_start));
            if ($secondsTemp < 0) {
                $secondsTemp = 0;
            }

            $seconds += $secondsTemp;
        }

        $totMinutes = round($seconds / 60);
        $minutes = $totMinutes % 60;
        $hours = ($totMinutes - $minutes) / 60;

        return CalendarTools::addLeadingZero($hours)
            . ":" . CalendarTools::addLeadingZero($minutes);
    }

    public function postProcessGetData($entry)
    {
        $entry->total_time = $this->getTotalTime();
        return $entry;
    }

    public function postProcessGetElement($entry)
    {
        $entry->days = [];
        $days = CalendarTools::getDaysBetweenDates($entry->date_start, $entry->date_end);
        foreach ($days as $dayObj) {
            $entry->days[] = [$dayObj->format('Y-m-d'), $dayObj->format("(D) d M")];
        }
        return $entry;
    }
}
