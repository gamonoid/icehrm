<?php
namespace Advance_reportsAdmin\Reports;

class ExpenseReport extends BaseReport
{
    protected $name = 'Expense Report';
    protected $description = 'This report lists employees expenses for a specified period';
    protected $group = 'Travel and Expense Management';
    protected $parameters = [
        [
            'name' => 'employee',
            'label' => 'Employee',
            'type' => 'select2multi',
            'allowNull' => true,
            'nullLabel' => 'All Employees',
            'remoteSource' => ['Employee', 'id', 'first_name+last_name'],
        ],
        [
            'name' => 'date_start',
            'label' => 'Start Date',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'date_end',
            'label' => 'End Date',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select',
            'source' => [
                ['value' => 'NULL', 'label' => 'All Statuses'],
                ['value' => 'Approved', 'label' => 'Approved'],
                ['value' => 'Pending', 'label' => 'Pending'],
                ['value' => 'Rejected', 'label' => 'Rejected'],
                ['value' => 'Cancellation Requested', 'label' => 'Cancellation Requested'],
                ['value' => 'Cancelled', 'label' => 'Cancelled'],
            ],
        ],
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            (SELECT employee_id from Employees where id = ex.employee) as 'Employee ID',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = ex.employee) as 'Employee',
            (SELECT name from ExpensesCategories where id = ex.category) as 'Category',
            (SELECT name from ExpensesPaymentMethods where id = ex.payment_method) as 'Payment Method',
            ex.expense_date as 'Date',
            ex.currency as 'Currency',
            ex.amount as 'Amount',
            ex.status as 'Status',
            ex.notes as 'Notes'
        FROM EmployeeExpenses ex
        WHERE ex.expense_date >= ? AND ex.expense_date <= ?";

        $queryParams = [$params['date_start'], $params['date_end']];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND ex.employee IN (" . implode(",", $employeeList) . ")";
        }

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND ex.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY ex.expense_date DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
