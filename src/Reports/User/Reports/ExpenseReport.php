<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\CSVReportBuilderInterface;

class ExpenseReport extends CSVReportBuilder implements CSVReportBuilderInterface
{

    public function getMainQuery()
    {
        $query = "SELECT
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = employee) as 'Employee',
expense_date as 'Date',
(SELECT name from ExpensesPaymentMethods where id = payment_method) as 'Payment Method',
transaction_no as 'Transaction Ref',
payee as 'Payee',
(SELECT name from ExpensesCategories where id = category) as 'Category',
notes as 'Notes',
concat(`amount`,' ',`currency`) as 'Amount',
status as 'Status',
created as 'Created',
updated as 'Updated'
from EmployeeExpenses";

        return $query;
    }

    public function getWhereQuery($request)
    {

        if (($request['status'] != "NULL" && !empty($request['status']))) {
            $query = "where employee = ? and status = ? and date(expense_date) >= ? and date(expense_date) <= ?;";
            $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
                $request['status'],
                $request['date_start'],
                $request['date_end']
            );
        } else {
            $query = "where employee = ? and date(expense_date) >= ? and date(expense_date) <= ?;";
            $params = array(
                BaseService::getInstance()->getCurrentProfileId(),
                $request['date_start'],
                $request['date_end']
            );
        }

        return array($query, $params);
    }
}
