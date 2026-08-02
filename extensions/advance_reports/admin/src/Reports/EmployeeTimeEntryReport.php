<?php
namespace Advance_reportsAdmin\Reports;

class EmployeeTimeEntryReport extends BaseReport
{
    protected $name = 'Employee Time Entry Report';
    protected $description = 'View employee time entries by date range and project';
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
            'name' => 'client',
            'label' => 'Select Client',
            'type' => 'select',
            'allowNull' => true,
            'nullLabel' => 'Not Selected',
            'remoteSource' => ['Client', 'id', 'name'],
        ],
        [
            'name' => 'project',
            'label' => 'Or Project',
            'type' => 'select',
            'allowNull' => true,
            'nullLabel' => 'All Projects',
            'remoteSource' => ['Project', 'id', 'name'],
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
            (SELECT concat(first_name, ' ', last_name) from Employees where id = te.employee) as 'Employee',
            (SELECT name from Projects where id = te.project) as 'Project',
            te.date_start as 'Date',
            te.time_start as 'Start Time',
            te.time_end as 'End Time',
            te.details as 'Details'
        FROM EmployeeTimeEntry te
        WHERE te.date_start >= ? AND te.date_start <= ?";

        $queryParams = [$params['date_start'], $params['date_end']];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND te.employee IN (" . implode(",", $employeeList) . ")";
        }

        if (!empty($params['project']) && $params['project'] !== 'NULL') {
            $query .= " AND te.project = ?";
            $queryParams[] = $params['project'];
        }

        $query .= " ORDER BY te.date_start DESC, te.time_start DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
