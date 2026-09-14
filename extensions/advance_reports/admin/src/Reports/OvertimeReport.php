<?php
namespace Advance_reportsAdmin\Reports;

class OvertimeReport extends BaseReport
{
    protected $name = 'Overtime Report';
    protected $description = 'This report lists all employee attendance entries by employee with overtime calculations';
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
            DATE(at.in_time) as 'Date',
            at.in_time as 'Time In',
            at.out_time as 'Time Out',
            ROUND(TIMESTAMPDIFF(SECOND, at.in_time, at.out_time) / 3600, 2) as 'Total Hours',
            CASE
                WHEN TIMESTAMPDIFF(SECOND, at.in_time, at.out_time) / 3600 > 8
                THEN ROUND((TIMESTAMPDIFF(SECOND, at.in_time, at.out_time) / 3600) - 8, 2)
                ELSE 0
            END as 'Overtime Hours',
            at.note as 'Note'
        FROM Attendance at
        WHERE at.in_time >= ? AND at.out_time <= ?";

        $queryParams = [
            $params['date_start'] . " 00:00:00",
            $params['date_end'] . " 23:59:59",
        ];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND at.employee IN (" . implode(",", $employeeList) . ")";
        }

        $query .= " ORDER BY at.employee, at.in_time DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
