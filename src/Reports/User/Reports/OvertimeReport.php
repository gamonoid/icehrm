<?php
namespace Reports\User\Reports;

use Attendance\Common\Model\Attendance;
use Classes\BaseService;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;

class OvertimeReport extends ClassBasedReportBuilder implements ReportBuilderInterface
{
    public function getData($report, $request)
    {

        $employeeList = array();
        $employeeList[] = BaseService::getInstance()->getCurrentProfileId();

        $sevenDateBefore = date('Y-m-d', strtotime('-7 days', strtotime($request['date_start'])));

        $query = "employee in (".implode(",", $employeeList).") and in_time >= ? and out_time <= ? order by in_time;";
        $params = array(
            $sevenDateBefore." 00:00:00",
            $request['date_end']." 23:59:59",
        );

        $at = new Attendance();
        $attendance = $at->Find($query, $params);

        //Group records by employee
        $employeeAttendance = array();
        foreach ($attendance as $entry) {
            if (!isset($employeeAttendance[$entry->employee])) {
                $employeeAttendance[$entry->employee] = array();
            }

            $employeeAttendance[$entry->employee][] = $entry;
        }

        $atCalClassName = SettingsManager::getInstance()->getSetting('Attendance: Overtime Calculation Class');
        $atCalClassName = '\\Attendance\\Common\\Calculations\\'.$atCalClassName;
        $atCal = new $atCalClassName();

        $reportData = array();
        if (!$this->isAggregated()) {
            $reportData[] = array(
                "Date", "Employee ID", "Employee", "Time in Office",
                "Regular Time", "Overtime", "Double Time"
            );
        } else {
            $reportData[] = array(
                "Employee ID", "Employee", "Time in Office", "Regular Time", "Overtime",
                "Double Time"
            );
        }

        foreach ($employeeAttendance as $employeeId => $atData) {
            $employee = new Employee();
            $employee->Load("id = ?", array($employeeId));
            $atSum = $atCal->getData($atData, $request['date_start'], $this->isAggregated());
            if (!$this->isAggregated()) {
                foreach ($atSum as $date => $counts) {
                    $reportData[] = array(
                        $date,
                        $employee->employee_id,
                        $employee->first_name." ".$employee->last_name,
                        $counts['t'],
                        $counts['r'],
                        $counts['o'],
                        $counts['d']
                    );
                }
            } else {
                $reportData[] = array(
                    $employee->employee_id,
                    $employee->first_name." ".$employee->last_name,
                    $atSum['t'],
                    $atSum['r'],
                    $atSum['o'],
                    $atSum['d']
                );
            }
        }

        return $reportData;
    }

    protected function isAggregated()
    {
        return false;
    }
}
