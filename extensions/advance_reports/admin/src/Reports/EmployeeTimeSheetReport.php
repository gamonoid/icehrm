<?php
namespace Advance_reportsAdmin\Reports;

class EmployeeTimeSheetReport extends BaseReport
{
    protected $name = 'Employee Time Sheet Report';
    protected $description = 'This report lists all employee time sheets by employee and date range';
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
            (SELECT employee_id from Employees where id = te.employee) as 'Employee ID',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = te.employee) as 'Employee',
            (SELECT name from Projects where id = te.project) as 'Project',
            te.date_start as 'Start Time',
            te.date_end as 'End Time',
            SEC_TO_TIME(TIMESTAMPDIFF(SECOND, te.date_start, te.date_end)) as 'Duration',
            te.details as 'Details'
        FROM EmployeeTimeEntry te
        WHERE te.date_start >= ? AND te.date_end <= ?";

        $queryParams = [
            $params['date_start'] . ' 00:00:00',
            $params['date_end'] . ' 23:59:59'
        ];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND te.employee IN (" . implode(",", $employeeList) . ")";
        }

        $query .= " ORDER BY te.date_start DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
