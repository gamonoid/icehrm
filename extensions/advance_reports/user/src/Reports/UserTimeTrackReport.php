<?php
namespace Advance_reportsUser\Reports;

class UserTimeTrackReport extends BaseUserReport
{
    protected $name = 'Time Tracking Report';
    protected $description = 'View your working hours and attendance details for each day for a given period';
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
            DATE(at.in_time) as 'Date',
            MIN(at.in_time) as 'First Punch In',
            MAX(at.out_time) as 'Last Punch Out',
            COUNT(*) as 'Punch Count',
            SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time)))) as 'Total Time'
        FROM Attendance at
        WHERE at.employee = ?
        AND DATE(at.in_time) >= ? AND DATE(at.in_time) <= ?
        GROUP BY DATE(at.in_time), at.employee
        ORDER BY DATE(at.in_time) DESC";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }
}
