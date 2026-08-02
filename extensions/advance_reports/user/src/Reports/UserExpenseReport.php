<?php
namespace Advance_reportsUser\Reports;

class UserExpenseReport extends BaseUserReport
{
    protected $name = 'Expense Report';
    protected $description = 'View expenses for a specified period';
    protected $group = 'Travel and Expense Management';
    protected $parameters = [
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
        $employeeId = $this->getCurrentEmployeeId();

        $query = "SELECT
            ex.expense_date as 'Date',
            (SELECT name from ExpensesCategories where id = ex.category) as 'Category',
            (SELECT name from ExpensesPaymentMethods where id = ex.payment_method) as 'Payment Method',
            (SELECT name from CurrencyTypes where id = ex.currency) as 'Currency',
            ex.amount as 'Amount',
            ex.notes as 'Notes',
            ex.status as 'Status'
        FROM EmployeeExpenses ex
        WHERE ex.employee = ?
        AND ex.expense_date >= ? AND ex.expense_date <= ?";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND ex.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY ex.expense_date DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
