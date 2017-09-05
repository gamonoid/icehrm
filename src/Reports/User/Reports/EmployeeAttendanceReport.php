<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;
use Utils\LogManager;

class EmployeeAttendanceReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT 
(SELECT `employee_id` from Employees where id = at.employee) as 'Employee',
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = at.employee) as 'Employee',
in_time as 'Time In',
out_time as 'Time Out',
note as 'Note'
FROM Attendance at";

        return $query;
    }

    public function getWhereQuery($request)
    {

        $query = "where employee = ? and in_time >= ? and out_time <= ? order by in_time desc;";
        $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
                $request['date_start']." 00:00:00",
                $request['date_end']." 23:59:59",
        );

        LogManager::getInstance()->info("Query:".$query);
        LogManager::getInstance()->info("Params:".json_encode($params));

        return array($query, $params);
    }
}
