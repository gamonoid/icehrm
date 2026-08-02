<?php
namespace Advance_reportsUser\Reports;

class UserOvertimeSummaryReport extends BaseUserReport
{
    protected $name = 'Overtime Summary Report';
    protected $description = 'This report shows your attendance entries with overtime calculation summary';
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
            COUNT(*) as 'Total Days',
            SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time)))) as 'Total Work Time',
            SEC_TO_TIME(
                SUM(
                    CASE
                        WHEN TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time)) > 28800
                        THEN TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, at.in_time)) - 28800
                        ELSE 0
                    END
                )
            ) as 'Total Overtime'
        FROM Attendance at
        WHERE at.employee = ?
        AND DATE(at.in_time) >= ? AND DATE(at.in_time) <= ?
        AND at.out_time IS NOT NULL";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }
}
