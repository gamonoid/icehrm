<?php
namespace Advance_reportsAdmin\Reports;

class EmployeeAttendanceReport extends BaseReport
{
    protected $name = 'Employee Attendance Report';
    protected $description = 'This report lists all employee attendance entries by employee and date range';
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
            at.in_time as 'Time In',
            at.out_time as 'Time Out',
            ROUND(TIMESTAMPDIFF(SECOND, at.in_time, at.out_time) / 3600, 2) as 'Hours',
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

        $query .= " ORDER BY at.in_time DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
