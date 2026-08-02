<?php
namespace Advance_reportsAdmin\Reports;

class TravelRequestReport extends BaseReport
{
    protected $name = 'Travel Request Report';
    protected $description = 'This report lists employees travel requests for a specified period';
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
            (SELECT employee_id from Employees where id = tr.employee) as 'Employee ID',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = tr.employee) as 'Employee',
            tr.travel_from as 'Travel From',
            tr.travel_to as 'Travel To',
            tr.travel_date as 'Travel Date',
            tr.return_date as 'Return Date',
            tr.type as 'Type',
            tr.purpose as 'Purpose',
            tr.status as 'Status',
            tr.currency as 'Currency',
            tr.funding as 'Funding'
        FROM EmployeeTravelRecords tr
        WHERE tr.travel_date >= ? AND tr.travel_date <= ?";

        $queryParams = [$params['date_start'], $params['date_end']];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND tr.employee IN (" . implode(",", $employeeList) . ")";
        }

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND tr.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY tr.travel_date DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
