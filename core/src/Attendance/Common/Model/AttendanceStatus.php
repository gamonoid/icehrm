<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:06 AM
 */

namespace Attendance\Common\Model;

use Classes\ModuleAccess;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Model\BaseModel;

/**
 * Class AttendanceStatus
 *
 * This is a read-only class. Should never be used to query data for a different purpose other
 * than checking attendance status
 *
 * @package Attendance\Common\Model
 */
class AttendanceStatus extends BaseModel
{
    public $table = 'Attendance';

    public function getRecentAttendanceEntries($limit)
    {
        $shift = intval(SettingsManager::getInstance()->getSetting("Attendance: Shift (Minutes)"));
        $attendance = new Attendance();
        $attendanceToday = $attendance->Find("1 = 1 order by in_time desc limit ".$limit, array());
        $employees = array();
        foreach ($attendanceToday as $atEntry) {
            $entry = new \stdClass();
            $entry->id = $atEntry->employee;
            $dayArr = explode(" ", $atEntry->in_time);
            $day = $dayArr[0];
            if ($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)) {
                if (strtotime($atEntry->in_time) < (time() + $shift * 60) && $day == date("Y-m-d")) {
                    $entry->status = "Clocked In";
                    $entry->statusId = 0;
                    $entry->color = 'green';

                    $employee = new Employee();
                    $employee->Load("id = ?", array($entry->id));
                    $entry->employee = $employee->first_name." ".$employee->last_name;
                    $employees[$entry->id] = $entry;
                }
            }

            if (!isset($employees[$entry->id])) {
                $employee = new Employee();
                $employee->Load("id = ?", array($entry->id));
                if ($day == date("Y-m-d")) {
                    $entry->status = "Clocked Out";
                    $entry->statusId = 1;
                    $entry->color = 'yellow';
                } else {
                    $entry->status = "Not Clocked In";
                    $entry->statusId = 2;
                    $entry->color = 'gray';
                }
                $entry->employee = $employee->first_name." ".$employee->last_name;
                $employees[$entry->id] = $entry;
            }
        }

        return array_values($employees);
    }
    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        $shift = intval(SettingsManager::getInstance()->getSetting("Attendance: Shift (Minutes)"));
        $employee = new Employee();
        $data = array();
        if (strstr($whereOrderBy, 'department=?')) {
            $employees = $employee->Find("department=?", $bindarr);
        } else {
            $employees = $employee->Find("1=1");
        }


        $attendance = new Attendance();
        $attendanceToday = $attendance->Find("date(in_time) = ?", array(date("Y-m-d")));
        $attendanceData = array();
        //Group by employee
        foreach ($attendanceToday as $attendance) {
            if (isset($attendanceData[$attendance->employee])) {
                $attendanceData[$attendance->employee][] = $attendance;
            } else {
                $attendanceData[$attendance->employee] = array($attendance);
            }
        }

        foreach ($employees as $employee) {
            $entry = new BaseModel();
            $entry->id = $employee->id;
            $entry->employee = $employee->id;

            if (isset($attendanceData[$employee->id])) {
                $attendanceEntries = $attendanceData[$employee->id];
                foreach ($attendanceEntries as $atEntry) {
                    if ($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)) {
                        if (strtotime($atEntry->in_time) < time() + $shift * 60) {
                            $entry->status = "Clocked In";
                            $entry->statusId = 0;
                        }
                    }
                }

                if (empty($entry->status)) {
                    $entry->status = "Clocked Out";
                    $entry->statusId = 1;
                }
            } else {
                $entry->status = "Not Clocked In";
                $entry->statusId = 2;
            }

            $data[] = $entry;
        }


        usort($data, function ($a, $b) {
            return $a->statusId - $b->statusId;
        });

        return $data;
    }

    public function countRows($query, $data)
    {
        $employee = new Employee();
        if (strstr($query, 'department=?')) {
            return $employee->Count("department=?", $data);
        }

        return $employee->Count("1=1");
    }

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
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('attendance', 'admin'),
            new ModuleAccess('attendance', 'user'),
        ];
    }
}
