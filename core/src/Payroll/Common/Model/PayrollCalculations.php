<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:17 PM
 */

namespace Payroll\Common\Model;

use Attendance\Common\Model\Attendance;
use Model\BaseModel;
use Salary\Common\Model\EmployeeSalary;
use TimeSheets\Common\Model\EmployeeTimeSheet;

class PayrollCalculations extends BaseModel
{

    public function getRegularPayAmount($edata, $employeeId, $payroll)
    {
        $hours = empty($edata['hours'])?"0":floatval($edata['hours']);
        $ot = empty($edata['overtime_pay'])?"0":floatval($edata['overtime_pay']);

        if (!empty($edata['pay_rate']) && floatval($edata['pay_rate']) > 0) {
            $amount = floatval($edata['pay_rate']) * ($hours + $ot * 1.5);
            $amount = round($amount, 2);
        } else {
            //Find all pay period salary
            $employeeSalary = new EmployeeSalary();
            $list = $employeeSalary->Find(
                "employee = ? and pay_frequency = ?",
                array($employeeId, $payroll->pay_period)
            );
            $amount = 0;
            foreach ($list as $sal) {
                $amount += floatval($sal->amount);
            }
        }

        return $amount;
    }

    public function findWorkHours($edata, $employeeId, $payroll)
    {
        return $this->getWorkingTime(
            $employeeId,
            date('Y-m-d', strtotime($payroll->date_start)),
            date('Y-m-d', strtotime($payroll->date_end))
        );
    }

    public function isTimesheetApproved($attendance)
    {

        $start = date("Y-m-d", strtotime($attendance->in_time));
        $end = date("Y-m-d", strtotime($attendance->out_time));

        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load(
            "date_start <= ? and date_end >= ? and employee = ?",
            array($start, $end, $attendance->employee)
        );

        if (!empty($timesheet->id)) {
            if ($timesheet->status == "Approved") {
                return true;
            }
        }

        return false;
    }

    private function getWorkingTime($employee, $start, $end)
    {

        $start = $start . " 00:00:00";
        $startTime = strtotime($start);
        $end = $end . " 23:59:59";
        $endTime = strtotime($end);

        $attendance = new Attendance();
        $list = $attendance->Find(
            "employee = ? and ((in_time >= ? and in_time <= ?) or (out_time >= ? and out_time <= ?))",
            array($employee, $start, $end, $start, $end)
        );

        $seconds = 0;
        $unapprovedSeconds = 0;
        foreach ($list as $entry) {
            if (strtotime($entry->in_time) >= strtotime($entry->out_time)) {
                continue;
            }

            $tStart = strtotime($entry->in_time);
            $tEnd = strtotime($entry->out_time);

            $secondsTemp = ($tEnd - $tStart);
            if ($secondsTemp < 0) {
                $secondsTemp = 0;
            }

            $seconds += $secondsTemp;
            if (!$this->isTimesheetApproved($entry)) {
                $unapprovedSeconds += $secondsTemp;
            }
        }

        $totMinutes = round($seconds / 60);
        $minutes = $totMinutes % 60;
        $hours = ($totMinutes - $minutes) / 60;

        $hoursPartial = $hours + ($minutes/60);

        $totMinutes = round($unapprovedSeconds / 60);
        $minutes = $totMinutes % 60;
        $hours = ($totMinutes - $minutes) / 60;

        $hoursPartialUnapproved = $hours + ($minutes/60);

        return round($hoursPartial, 2) . "(".round($hoursPartialUnapproved, 2).")";
    }
}
