<?php
namespace Reports\User\Reports;

use Classes\BaseService;
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

        if (($request['client'] != "NULL" && !empty($request['client']))) {
            if (($request['project'] != "NULL" && !empty($request['project']))) {
                $query = "where employee = ? and project = ? and date_start >= ? and date_end <= ?;";
                $params = array(
                    BaseService::getInstance()->getCurrentProfileId(),
                    $request['project'],
                    $request['date_start'],
                    $request['date_end']
                );
            } else {
                $project = new Project();
                $projects = $project->Find("client = ?", array($request['client']));
                $projectIds = array();
                foreach ($projects as $project) {
                    $projectIds[] = $project->id;
                }

                $query = "where project in (".implode(",", $projectIds)
                    .") and employee = ? and date_start >= ? and date_end <= ?;";
                $params = array(
                    BaseService::getInstance()->getCurrentProfileId(),
                    $request['date_start'],
                    $request['date_end']
                );
            }
        } else {
            if (($request['project'] != "NULL" && !empty($request['project']))) {
                $query = "where employee = ? and project = ? and date_start >= ? and date_end <= ?;";
                $params = array(
                    BaseService::getInstance()->getCurrentProfileId(),
                    $request['project'],
                    $request['date_start'],
                    $request['date_end']
                );
            } else {
                $query = "where employee = ? and date_start >= ? and date_end <= ?;";
                $params = array(
                    BaseService::getInstance()->getCurrentProfileId(),
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
