<?php
namespace Advance_reportsAdmin\Reports;

class OvertimeSummaryReport extends BaseReport
{
    protected $name = 'Overtime Summary Report';
    protected $description = 'This report lists all employee attendance entries by employee with overtime calculation summary';
    protected $group = 'Time Management';
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
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            (SELECT employee_id from Employees where id = at.employee) as 'Employee ID',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = at.employee) as 'Employee',
            COUNT(*) as 'Attendance Count',
            ROUND(SUM(TIMESTAMPDIFF(SECOND, at.in_time, at.out_time)) / 3600, 2) as 'Total Hours',
            ROUND(COUNT(*) * 8, 2) as 'Expected Hours (8h/day)',
            ROUND(
                CASE
                    WHEN SUM(TIMESTAMPDIFF(SECOND, at.in_time, at.out_time)) / 3600 > COUNT(*) * 8
                    THEN (SUM(TIMESTAMPDIFF(SECOND, at.in_time, at.out_time)) / 3600) - (COUNT(*) * 8)
                    ELSE 0
                END, 2
            ) as 'Total Overtime Hours'
        FROM Attendance at
        WHERE at.in_time >= ? AND at.out_time <= ? AND at.out_time IS NOT NULL";

        $queryParams = [
            $params['date_start'] . " 00:00:00",
            $params['date_end'] . " 23:59:59",
        ];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND at.employee IN (" . implode(",", $employeeList) . ")";
        }

        $query .= " GROUP BY at.employee ORDER BY 6 DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
