<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:07 AM
 */

namespace Attendance\Admin\Api;

use Attendance\Common\Model\Attendance;
use Classes\BaseService;
use Classes\IceResponse;
use Classes\SettingsManager;

class AttendanceUtil
{
    public function getAttendanceSummary($employeeId, $startDate, $endDate)
    {
        $startTime = $startDate." 00:00:00";
        $endTime = $endDate." 23:59:59";
        $attendance = new Attendance();
        $atts = $attendance->Find(
            "employee = ? and in_time >= ? and out_time <= ?",
            array($employeeId, $startTime, $endTime)
        );

        $atCalClassName = SettingsManager::getInstance()->getSetting('Attendance: Overtime Calculation Class');
        $atCalClassName = '\\Attendance\\Common\\Calculations\\'.$atCalClassName;
        $atCal = new $atCalClassName();
        $atSum = $atCal->getDataSeconds($atts, $startDate, true);

        return $atSum;
    }

    public function getTimeWorkedHours($employeeId, $startDate, $endDate)
    {
        $atSum = $this->getAttendanceSummary($employeeId, $startDate, $endDate);
        return round(($atSum['t']/60)/60, 2);
    }

    public function getRegularWorkedHours($employeeId, $startDate, $endDate)
    {
        $atSum = $this->getAttendanceSummary($employeeId, $startDate, $endDate);
        return round(($atSum['r']/60)/60, 2);
    }

    public function getOverTimeWorkedHours($employeeId, $startDate, $endDate)
    {
        $atSum = $this->getAttendanceSummary($employeeId, $startDate, $endDate);
        return round(($atSum['o']/60)/60, 2);
    }

    public function getWeeklyBasedRegularHours($employeeId, $startDate, $endDate)
    {
        $atSum = $this->getWeeklyBasedOvertimeSummary($employeeId, $startDate, $endDate);
        return round(($atSum['r']/60)/60, 2);
    }

    public function getWeeklyBasedOvertimeHours($employeeId, $startDate, $endDate)
    {
        $atSum = $this->getWeeklyBasedOvertimeSummary($employeeId, $startDate, $endDate);
        return round(($atSum['o']/60)/60, 2);
    }

    public function getWeeklyBasedOvertimeSummary($employeeId, $startDate, $endDate)
    {

        $attendance = new Attendance();
        $atTimeByWeek = array();

        //Find weeks starting from sunday and ending from saturday in day period

        $weeks = $this->getWeeklyDays($startDate, $endDate);
        foreach ($weeks as $k => $week) {
            $startTime = $week[0]." 00:00:00";
            $endTime = $week[count($week) - 1]." 23:59:59";
            $atts = $attendance->Find(
                "employee = ? and in_time >= ? and out_time <= ?",
                array($employeeId, $startTime, $endTime)
            );
            foreach ($atts as $atEntry) {
                if ($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)) {
                    continue;
                }
                if (!isset($atTimeByWeek[$k])) {
                    $atTimeByWeek[$k]   = 0;
                }

                $diff = strtotime($atEntry->out_time) - strtotime($atEntry->in_time);
                if ($diff < 0) {
                    $diff = 0;
                }

                $atTimeByWeek[$k] += $diff;
            }
        }

        $overtimeStarts = SettingsManager::getInstance()->getSetting('Attendance: Overtime Start Hour');
        $overtimeStarts = (is_numeric($overtimeStarts))?floatval($overtimeStarts) * 60 * 60 * 5 : 0;
        $regTime = 0;
        $overTime = 0;
        foreach ($atTimeByWeek as $value) {
            if ($value > $overtimeStarts) {
                $regTime += $overtimeStarts;
                $overTime = $value - $overtimeStarts;
            } else {
                $regTime += $value;
            }
        }

        return array('r'=>$regTime,'o'=>$overTime);
    }

    private function getWeeklyDays($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate.' 23:59');
        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($start, $interval, $end);

        $weekNumber = 1;
        $weeks = array();
        /* @var \DateTime $date */
        foreach ($dateRange as $date) {
            $weeks[$weekNumber][] = $date->format('Y-m-d');
            if ($date->format('w') == 6) {
                $weekNumber++;
            }
        }

        return $weeks;
    }

    public function isEmployeeHasOpenPunch($date, $employeeId)
    {
        $attendance = new Attendance();
        $attendance->Load(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ? and out_time is NULL",
            array($employeeId,$date)
        );

        if ($attendance->employee == $employeeId) {
            //found an open punch
            return true;
        } else {
            return false;
        }
    }


    public function isEmployeePunchedIn($date, $employeeId)
    {
        $attendance = new Attendance();
        $attendance->Load(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ?",
            array($employeeId,$date)
        );

        if ($attendance->employee == $employeeId) {
            return true;
        } else {
            return false;
        }
    }


    public function isEmployeePunchedOut($date, $employeeId)
    {
        //Find any open punch
        $attendance = new Attendance();
        $attendance->Load(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ? and out_time is NOT NULL",
            array($employeeId,$date)
        );

        if ($attendance->employee == $employeeId) {
            //found a closed punch
            return true;
        } else {
            return false;
        }
    }
}
