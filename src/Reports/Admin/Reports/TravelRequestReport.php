<?php
namespace Reports\Admin\Reports;

use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;

class TravelRequestReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) 
from Employees where id = employee) as 'Employee',
type as 'Type',
purpose as 'Purpose',
travel_from as 'Travel From',
travel_to as 'Travel To',
travel_date as 'Travel Date',
return_date as 'Return Date',
details as 'Other Details',
concat(`funding`,' ',`currency`) as 'Funding',
status as 'Status',
created as 'Created',
updated as 'Updated'
from EmployeeTravelRecords";

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

        if (!empty($employeeList) && ($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where employee in (".implode(",", $employeeList)
                .") and date(travel_date) >= ? and date(return_date) <= ? and status = ?;";
            $params = array(
                $request['date_start'],
                $request['date_end'],
                $request['status']
            );
        } elseif (!empty($employeeList)) {
            $query = "where employee in (".implode(",", $employeeList)
                .") and date(travel_date) >= ? and date(return_date) <= ?;";
            $params = array(
                $request['date_start'],
                $request['date_end']
            );
        } elseif (($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where status = ? and date(travel_date) >= ? and date(return_date) <= ?;";
            $params = array(
                $request['status'],
                $request['date_start'],
                $request['date_end']
            );
        } else {
            $query = "where date(travel_date) >= ? and date(return_date) <= ?;";
            $params = array(
                $request['date_start'],
                $request['date_end']
            );
        }

        return array($query, $params);
    }
}
