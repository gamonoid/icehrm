<?php
namespace Reports\Admin\Reports;

use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;
use Utils\LogManager;

class OvertimeRequestReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT 
(SELECT `employee_id` from Employees where id = at.employee) as 'Employee',
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = at.employee) as 'Employee',
(SELECT `name` from OvertimeCategories where id = at.category) as 'Category',
(SELECT `name` from Projects where id = at.project) as 'Project',
start_time as 'Start Time',
end_time as 'End Time',
notes as 'Notes',
status as 'Status'
FROM EmployeeOvertime at";

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

        if (!empty($employeeList)) {
            $query = "where employee in (".implode(",", $employeeList).") and start_time >= ? and end_time <= ?";
            $params = array(
                $request['date_start']." 00:00:00",
                $request['date_end']." 23:59:59",
            );
        } else {
            $query = "where start_time >= ? and end_time <= ?";
            $params = array(
                $request['date_start']." 00:00:00",
                $request['date_end']." 23:59:59",
            );
        }

        if (!empty($request['category']) && $request['category'] != "NULL") {
            $query.= " and category = ?";
            $params[] = $request['category'];
        }

        if (!empty($request['project']) && $request['project'] != "NULL") {
            $query.= " and project = ?";
            $params[] = $request['project'];
        }

        $query.="  order by start_time desc;";

        LogManager::getInstance()->info("Query:".$query);
        LogManager::getInstance()->info("Params:".json_encode($params));

        return array($query, $params);
    }
}
