<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;
use Utils\LogManager;

class EmployeeLeavesReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT 
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) 
from Employees where id = employee) as 'Employee',
(SELECT name from LeaveTypes where id = leave_type) as 'Leave Type',
(SELECT name from LeavePeriods where id = leave_period) as 'Leave Period',
date_start as 'Start Date',
date_end as 'End Date',
details as 'Reason',
status as 'Leave Status',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id and leave_type = 'Full Day') 
as 'Full Day Count',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id 
and leave_type = 'Half Day - Morning') as 'Half Day (Morning) Count',
(select count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id 
and leave_type = 'Half Day - Afternoon') as 'Half Day (Afternoon) Count'
from EmployeeLeaves lv";

        return $query;
    }

    public function getWhereQuery($request)
    {

        if (($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where employee = ? and status = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?));";
            $params = array(
                    BaseService::getInstance()->getCurrentProfileId(),
                    $request['status'],
                    $request['date_start'],
                    $request['date_end'],
                    $request['date_start'],
                    $request['date_end']
            );
        } else {
            $query = "where employee = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?));";
            $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
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
