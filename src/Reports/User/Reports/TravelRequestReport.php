<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;

class TravelRequestReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = employee) as 'Employee',
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

        if (($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where employee = ? and status = ? and date(travel_date) >= ? and date(return_date) <= ?;";
            $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
                $request['status'],
                $request['date_start'],
                $request['date_end']
            );
        } else {
            $query = "where employee = ? and date(travel_date) >= ? and date(return_date) <= ?;";
            $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
                $request['date_start'],
                $request['date_end']
            );
        }

        return array($query, $params);
    }
}
