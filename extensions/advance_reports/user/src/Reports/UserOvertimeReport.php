<?php
namespace Advance_reportsUser\Reports;

class UserOvertimeReport extends BaseUserReport
{
    protected $name = 'Overtime Report';
    protected $description = 'This report lists your attendance entries with overtime calculations';
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
            at.in_time as 'Punch In',
            at.out_time as 'Punch Out',
            SEC_TO_TIME(TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time))) as 'Total Time',
            CASE
                WHEN TIMESTAMPDIFF(HOUR, at.in_time, IFNULL(at.out_time, at.in_time)) > 8
                THEN SEC_TO_TIME((TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time)) - 28800))
                ELSE '00:00:00'
            END as 'Overtime'
        FROM Attendance at
        WHERE at.employee = ?
        AND DATE(at.in_time) >= ? AND DATE(at.in_time) <= ?
        AND at.out_time IS NOT NULL
        ORDER BY at.in_time DESC";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }
}
