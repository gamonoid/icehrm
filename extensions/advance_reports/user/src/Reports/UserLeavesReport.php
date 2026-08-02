<?php
namespace Advance_reportsUser\Reports;

class UserLeavesReport extends BaseUserReport
{
    protected $name = 'Leaves Report';
    protected $description = 'This report lists your leave applications, date range and leave status';
    protected $group = 'Leave Management';
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
            'label' => 'Leave Status',
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
            (SELECT name from LeaveTypes where id = lv.leave_type) as 'Leave Type',
            (SELECT name from LeavePeriods where id = lv.leave_period) as 'Leave Period',
            lv.date_start as 'Start Date',
            lv.date_end as 'End Date',
            lv.details as 'Reason',
            lv.status as 'Leave Status',
            (SELECT count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id and d.leave_type = 'Full Day') as 'Full Day Count',
            (SELECT count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id and d.leave_type = 'Half Day - Morning') as 'Half Day (Morning) Count',
            (SELECT count(*) from EmployeeLeaveDays d where d.employee_leave = lv.id and d.leave_type = 'Half Day - Afternoon') as 'Half Day (Afternoon) Count'
        FROM EmployeeLeaves lv
        WHERE lv.employee = ?
        AND ((lv.date_start >= ? AND lv.date_start <= ?) OR (lv.date_end >= ? AND lv.date_end <= ?))";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
            $params['date_start'],
            $params['date_end'],
        ];

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND lv.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY lv.date_start DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
