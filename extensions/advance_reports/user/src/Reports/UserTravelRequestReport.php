<?php
namespace Advance_reportsUser\Reports;

class UserTravelRequestReport extends BaseUserReport
{
    protected $name = 'Travel Request Report';
    protected $description = 'View travel requests for a specified period';
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
            tr.travel_from as 'From',
            tr.travel_to as 'To',
            tr.travel_date as 'Travel Date',
            tr.return_date as 'Return Date',
            tr.type as 'Type',
            tr.purpose as 'Purpose',
            tr.currency as 'Currency',
            tr.funding as 'Funding',
            tr.status as 'Status'
        FROM EmployeeTravelRecords tr
        WHERE tr.employee = ?
        AND ((tr.travel_date >= ? AND tr.travel_date <= ?) OR (tr.return_date >= ? AND tr.return_date <= ?))";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
            $params['date_start'],
            $params['date_end'],
        ];

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND tr.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY tr.travel_date DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
