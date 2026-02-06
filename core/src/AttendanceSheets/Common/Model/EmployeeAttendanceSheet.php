<?php
namespace AttendanceSheets\Common\Model;

use Attendance\Common\Model\Attendance;
use Classes\ModuleAccess;
use Model\BaseModel;
use Utils\CalendarTools;

class EmployeeAttendanceSheet extends BaseModel
{
    public $table = 'EmployeeAttendanceSheets';

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

    public static function getAttendanceEntries($id)
    {
        $ats = new EmployeeAttendanceSheet();
        $ats->Load("id = ?", array($id));
        $start = $ats->date_start . " 00:00:00";
        $end = $ats->date_end . " 23:59:59";
        $timeEntry = new Attendance();
        $list = $timeEntry->Find(
            "employee = ? and ((in_time >= ? and in_time <= ?) or (out_time >= ? and out_time <= ?))",
            array($ats->employee, $start, $end, $start, $end)
        );
        return $list;
    }

    public function getTotalTime()
    {

        $start = $this->date_start . " 00:00:00";
        $end = $this->date_end . " 23:59:59";

        $timeEntry = new Attendance();
        $list = $timeEntry->Find(
            "employee = ? and ((in_time >= ? and in_time <= ?) or (out_time >= ? and out_time <= ?))",
            array($this->employee, $start, $end, $start, $end)
        );

        $seconds = 0;

        foreach ($list as $entry) {
            $secondsTemp = (strtotime($entry->out_time) - strtotime($entry->in_time));
            if ($secondsTemp < 0) {
                $secondsTemp = 0;
            }

            $seconds += $secondsTemp;
        }

        $totMinutes = round($seconds / 60);
        $minutes = $totMinutes % 60;
        $hours = ($totMinutes - $minutes) / 60;

        return CalendarTools::addLeadingZero($hours) . ":" . CalendarTools::addLeadingZero($minutes);
    }

    public function postProcessGetElement($entry)
    {
        $entry->total_time = $this->getTotalTime();
        return $entry;
    }

    public function postProcessGetData($entry)
    {
        $entry->total_time = $this->getTotalTime();
        return $entry;
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('attendance_sheets', 'user'),
        ];
    }
}
