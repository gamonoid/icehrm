<?php
namespace Reports\Admin\Reports;

use Projects\Common\Model\Project;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;
use Utils\LogManager;

class EmployeeTimesheetReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT 
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) 
from Employees where id = te.employee) as 'Employee',
(SELECT name from Projects where id = te.project) as 'Project',
details as 'Details',
date_start as 'Start Time',
date_end as 'End Time',
SEC_TO_TIME(TIMESTAMPDIFF(SECOND,te.date_start,te.date_end)) as 'Duration'
FROM EmployeeTimeEntry te";

        return $query;
    }

    public function getWhereQuery($request)
    {

        $employeeList = array();
        if (!empty($request['employee'])) {
            $employeeList = json_decode($request['employee'], true);
        }

        $request['date_start'] = $request['date_start']." 00:00:00";
        $request['date_end'] = $request['date_end']." 23:59:59";

        if (in_array("NULL", $employeeList)) {
            $employeeList = array();
        }

        if (($request['client'] != "NULL" && !empty($request['client']))) {
            $project = new Project();
            $projects = $project->Find("client = ?", array($request['client']));
            $projectIds = array();
            foreach ($projects as $project) {
                $projectIds[] = $project->id;
            }

            if (!empty($employeeList) && ($request['project'] != "NULL" && !empty($request['project']))) {
                $query = "where employee in (" . implode(",", $employeeList)
                    . ") and date_start >= ? and date_end <= ? and project in ("
                    .implode(",", $projectIds).");";
                $params = array(
                    $request['date_start'],
                    $request['date_end']
                );
            } elseif (!empty($employeeList)) {
                $query = "where employee in (" . implode(",", $employeeList)
                    . ") and date_start >= ? and date_end <= ? and project in ("
                    .implode(",", $projectIds).");";
                $params = array(
                    $request['date_start'],
                    $request['date_end']
                );
            } else {
                $query = "where date_start >= ? and date_end <= ? and project in ("
                    .implode(",", $projectIds).");";
                $params = array(
                    $request['date_start'],
                    $request['date_end']
                );
            }
        } else {
            if (!empty($employeeList) && ($request['project'] != "NULL" && !empty($request['project']))) {
                $query = "where employee in (" . implode(",", $employeeList)
                    . ") and date_start >= ? and date_end <= ? and project = ?;";
                $params = array(
                    $request['date_start'],
                    $request['date_end'],
                    $request['project']
                );
            } elseif (!empty($employeeList)) {
                $query = "where employee in (" . implode(",", $employeeList)
                    . ") and date_start >= ? and date_end <= ?;";
                $params = array(
                    $request['date_start'],
                    $request['date_end']
                );
            } elseif (($request['project'] != "NULL" && !empty($request['project']))) {
                $query = "where project = ? and date_start >= ? and date_end <= ?;";
                $params = array(
                    $request['project'],
                    $request['date_start'],
                    $request['date_end']
                );
            } else {
                $query = "where date_start >= ? and date_end <= ?;";
                $params = array(
                    $request['date_start'],
                    $request['date_end']
                );
            }
        }

        LogManager::getInstance()->info("Query:".$query);
        LogManager::getInstance()->info("Params:".json_encode($params));

        return array($query, $params);
    }
}
