<?php
if(!class_exists('ReportBuilder')){
    include_once APP_BASE_PATH.'admin/reports/reportClasses/ReportBuilder.php';
}
class ExpenseReport extends ReportBuilder{

    public function getMainQuery(){
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

    public function getWhereQuery($request){

        $employeeList = array();
        if(!empty($request['employee'])){
            $employeeList = json_decode($request['employee'],true);
        }

        if(in_array("NULL", $employeeList) ){
            $employeeList = array();
        }


        if(!empty($employeeList) && ($request['status'] != "NULL" && !empty($request['status']))){
            $query = "where employee in (".implode(",", $employeeList).") and date(expense_date) >= ? and date(expense_date) <= ? and status = ?;";
            $params = array(
                $request['date_start'],
                $request['date_end'],
                $request['status']
            );
        }else if(!empty($employeeList)){
            $query = "where employee in (".implode(",", $employeeList).") and date(expense_date) >= ? and date(expense_date) <= ?;";
            $params = array(
                $request['date_start'],
                $request['date_end']
            );
        }else if(($request['status'] != "NULL" && !empty($request['status']))){
            $query = "where status = ? and date(expense_date) >= ? and date(expense_date) <= ?;";
            $params = array(
                $request['status'],
                $request['date_start'],
                $request['date_end']
            );
        }else{
            $query = "where date(expense_date) >= ? and date(expense_date) <= ?;";
            $params = array(
                $request['date_start'],
                $request['date_end']
            );
        }

        return array($query, $params);
    }
}