<?php
namespace Reports\Admin\Reports;

use Employees\Common\Model\Employee;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;
use Utils\LogManager;

class EmployeeLeavesReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT 
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = employee) 
as 'Employee',
(SELECT name from LeaveTypes where id = leave_type) as 'Leave Type',
(SELECT name from LeavePeriods where id = leave_period) as 'Leave Period',
date_start as 'Start Date',
date_end as 'End Date',
details as 'Reason',
status as 'Leave Status',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id 
and leave_type = 'Full Day') as 'Full Day Count',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id 
and leave_type = 'Half Day - Morning') as 'Half Day (Morning) Count',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id 
and leave_type = 'Half Day - Afternoon') as 'Half Day (Afternoon) Count'
from EmployeeLeaves lv";

        return $query;
    }

    public function getWhereQuery($request)
    {

        $employeeList = array();
        if (!empty($request['employee'])) {
            $employeeList = json_decode($request['employee'], true);
        }

        if (in_array("NULL", $employeeList)) {
            $employeeList = array();
        }

        if ($request['department'] != "NULL" && empty($employeeList)) {
            $empTmp = new Employee();
            $empTemps = $empTmp->Find("department = ? and status = Active", array($request['department']));
            foreach ($empTemps as $empTmp) {
                $employeeList[] = $empTmp->id;
            }
        }


        if (!empty($employeeList) && ($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where employee in (".implode(",", $employeeList)
                .") and ((date_start >= ? and date_start <= ?) or (date_end >= ? and date_end <= ?)) and status = ?;";
            $params = array(
                    $request['date_start'],
                    $request['date_end'],
                    $request['date_start'],
                    $request['date_end'],
                    $request['status']
            );
        } elseif (!empty($employeeList)) {
            $query = "where employee in (".implode(",", $employeeList)
                .") and ((date_start >= ? and date_start <= ?) or (date_end >= ? and date_end <= ?));";
            $params = array(
                    $request['date_start'],
                    $request['date_end'],
                    $request['date_start'],
                    $request['date_end']
            );
        } elseif (($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where status = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?));";
            $params = array(
                    $request['status'],
                    $request['date_start'],
                    $request['date_end'],
                    $request['date_start'],
                    $request['date_end']
            );
        } else {
            $query = "where ((date_start >= ? and date_start <= ?) or (date_end >= ? and date_end <= ?));";
            $params = array(
                    $request['date_start'],
                    $request['date_end'],
                    $request['date_start'],
                    $request['date_end']
            );
        }

        LogManager::getInstance()->info("Query:".$query);
        LogManager::getInstance()->info("Params:".json_encode($params));

        return array($query, $params);
    }
}
