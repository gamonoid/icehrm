<?php
namespace Advance_reportsUser\Reports;

class UserAttendanceReport extends BaseUserReport
{
    protected $name = 'Attendance Report';
    protected $description = 'View your attendance entries by date range';
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
    ];

    public function getReportData(array $params): array
    {
        $employeeId = $this->getCurrentEmployeeId();

        $query = "SELECT
            at.in_time as 'Punch In',
            at.out_time as 'Punch Out',
            at.note as 'Note'
        FROM Attendance at
        WHERE at.employee = ?
        AND DATE(at.in_time) >= ? AND DATE(at.in_time) <= ?
        ORDER BY at.in_time DESC";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }
}
