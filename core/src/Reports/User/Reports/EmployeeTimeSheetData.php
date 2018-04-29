<?php
namespace Reports\User\Reports;

use Employees\Common\Model\Employee;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Utils\LogManager;

class EmployeeTimeSheetData extends ClassBasedReportBuilder implements ReportBuilderInterface
{
    public function getData($report, $request)
    {

        $employeeCache = array();

        $employeeList = array();
        if (!empty($request['employee'])) {
            $employeeList = json_decode($request['employee'], true);
        }

        if (in_array("NULL", $employeeList)) {
            $employeeList = array();
        }

        $employee_query = "";
        if (!empty($employeeList)) {
            $employee_query = "employee in (".implode(",", $employeeList).") and ";
        }

        $timeSheet = new EmployeeTimeSheet();
        if ($request['status'] != "NULL") {
            $timeSheets = $timeSheet->Find(
                $employee_query."status = ? and date_start >= ? and date_end <= ?",
                array($request['status'],$request['date_start'],$request['date_end'])
            );
        } else {
            $timeSheets = $timeSheet->Find(
                $employee_query."date_start >= ? and date_end <= ?",
                array($request['date_start'],$request['date_end'])
            );
        }

        if (!$timeSheets) {
            LogManager::getInstance()->info($timeSheet->ErrorMsg());
        }

        $reportData = array();
        $reportData[] = array("Employee ID","Employee","Name","Start","End","Total Time","Status");

        foreach ($timeSheets as $ts) {
            $employee = $employeeCache[$ts->employee];
            if (empty($employee)) {
                $employee = new Employee();
                $employee->Load("id = ?", array($ts->employee));
                if (empty($employee->id)) {
                    continue;
                }
                $employeeCache[$employee->id] = $employee;
            }
            $reportData[] = array(
                $employee->employee_id,
                $employee->first_name." ".$employee->last_name,
                date("F j, Y", strtotime($ts->date_start))
                    ." - "
                    .date("F j, Y", strtotime($ts->date_end)),
                $ts->date_start,
                $ts->date_end,
                $ts->getTotalTime(),
                $ts->status
            );
        }

        return $reportData;
    }
}
