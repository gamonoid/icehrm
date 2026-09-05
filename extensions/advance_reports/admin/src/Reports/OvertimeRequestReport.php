<?php
namespace Advance_reportsAdmin\Reports;

class OvertimeRequestReport extends BaseReport
{
    protected $name = 'Overtime Request Report';
    protected $description = 'This report lists employee overtime requests by employee, date range, overtime category and project';
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
        [
            'name' => 'category',
            'label' => 'Category',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Categories',
            'remoteSource' => ['OvertimeCategory', 'id', 'name'],
        ],
        [
            'name' => 'project',
            'label' => 'Project',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Projects',
            'remoteSource' => ['Project', 'id', 'name'],
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select',
            'source' => [
                ['value' => 'NULL', 'label' => 'All Statuses'],
                ['value' => 'Approved', 'label' => 'Approved'],
                ['value' => 'Pending', 'label' => 'Pending'],
                ['value' => 'Rejected', 'label' => 'Rejected'],
                ['value' => 'Cancellation Requested', 'label' => 'Cancellation Requested'],
                ['value' => 'Cancelled', 'label' => 'Cancelled'],
                ['value' => 'Processing', 'label' => 'Processing'],
            ],
        ],
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            (SELECT employee_id from Employees where id = o.employee) as 'Employee ID',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = o.employee) as 'Employee',
            (SELECT name from OvertimeCategories where id = o.category) as 'Category',
            (SELECT name from Projects where id = o.project) as 'Project',
            o.start_time as 'Start Time',
            o.end_time as 'End Time',
            ROUND(TIMESTAMPDIFF(SECOND, o.start_time, o.end_time) / 3600, 2) as 'Hours',
            o.status as 'Status',
            o.notes as 'Notes'
        FROM EmployeeOvertime o
        WHERE DATE(o.start_time) >= ? AND DATE(o.start_time) <= ?";

        $queryParams = [$params['date_start'], $params['date_end']];

        $employeeList = $this->parseEmployeeList($params['employee'] ?? '');
        if (!empty($employeeList)) {
            $query .= " AND o.employee IN (" . implode(",", $employeeList) . ")";
        }

        if (!empty($params['category']) && $params['category'] !== 'NULL') {
            $query .= " AND o.category = ?";
            $queryParams[] = $params['category'];
        }

        if (!empty($params['project']) && $params['project'] !== 'NULL') {
            $query .= " AND o.project = ?";
            $queryParams[] = $params['project'];
        }

        if (!empty($params['status']) && $params['status'] !== 'NULL') {
            $query .= " AND o.status = ?";
            $queryParams[] = $params['status'];
        }

        $query .= " ORDER BY o.start_time DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
