<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:06 AM
 */

namespace Attendance\Common\Model;

use Classes\FileService;
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

        // This override DISCARDS the caller's scoping clause (getData() passes
        // "employee in (<subordinate ids>)") and enumerates the whole Employees table, so
        // a Manager saw every employee's live clock-in status — the full roster, names,
        // profile images and presence — regardless of the reporting line. Re-apply the
        // manager row scope here; Admin and all-employee-data roles get an empty scope
        // and are unaffected.
        $employees = self::restrictToManagerScope($employees);


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

            $employee = FileService::getInstance()->updateSmallProfileImage($employee);
            $entry->image = $employee->image;

            $data[] = $entry;
        }


        usort(
            $data,
            function ($a, $b) {
                return $a->statusId - $b->statusId;
            }
        );

        return $data;
    }

    public function countRows($query, $data)
    {
        $employee = new Employee();
        if (strstr($query, 'department=?')) {
            $rows = $employee->Find("department=?", $data);
        } else {
            $rows = $employee->Find("1=1");
        }

        // Mirror Find(): the paging total must reflect the same manager scope, otherwise
        // it still discloses the company-wide headcount.
        return count(self::restrictToManagerScope($rows));
    }

    /**
     * Filter an Employee list down to the rows the current caller may see, using the
     * shared manager row scope. Returns the list unchanged for Admin levels, for roles
     * granted all employee data, and for callers the scope does not apply to.
     *
     * @param  array $employees
     * @return array
     */
    private static function restrictToManagerScope($employees)
    {
        if (empty($employees) || !is_array($employees)) {
            return $employees;
        }

        $scope = \Classes\BaseService::getInstance()->getManagerListScopeClause(new Employee());
        if (empty($scope[0])) {
            return $employees;
        }

        $allowed = array_map('strval', $scope[1]);
        $filtered = array();
        foreach ($employees as $emp) {
            if (in_array((string) $emp->id, $allowed, true)) {
                $filtered[] = $emp;
            }
        }

        return $filtered;
    }

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    /**

     * No module grants Employee access to this model (module meta.json user_levels),

     * so no employee-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getUserAccess()

    {

        return array();

    }

    public function getUserOnlyMeAccess()
    {
        return array("get");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('attendance', 'admin'),
            new ModuleAccess('attendance', 'user'),
        ];
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
