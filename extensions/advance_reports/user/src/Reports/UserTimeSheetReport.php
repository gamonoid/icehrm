<?php
namespace Advance_reportsUser\Reports;

class UserTimeSheetReport extends BaseUserReport
{
    protected $name = 'Time Sheet Report';
    protected $description = 'This report lists all your time sheets by date range';
    protected $group = 'Time Management';
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
            'allowNull' => true,
            'nullLabel' => 'All Status',
            'source' => [
                ['value' => 'NULL', 'label' => 'All Status'],
                ['value' => 'Approved', 'label' => 'Approved'],
                ['value' => 'Pending', 'label' => 'Pending'],
                ['value' => 'Rejected', 'label' => 'Rejected'],
            ],
        ],
    ];

    public function getReportData(array $params): array
    {
        $employeeId = $this->getCurrentEmployeeId();

        $query = "SELECT
            te.date_start as 'Date',
            (SELECT name from Projects where id = te.project) as 'Project',
            te.time_start as 'Start Time',
            te.time_end as 'End Time',
            te.details as 'Details',
            te.status as 'Status'
        FROM EmployeeTimeEntry te
        WHERE te.employee = ?
        AND te.date_start >= ? AND te.date_start <= ?";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND te.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY te.date_start DESC, te.time_start DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
