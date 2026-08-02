<?php
namespace Advance_reportsAdmin\Reports;

class NewHiresEmployeeReport extends BaseReport
{
    protected $name = 'New Hires Employee Report';
    protected $description = 'This report lists employees who joined between given two dates';
    protected $group = 'Employee Information';
    protected $parameters = [
        [
            'name' => 'department',
            'label' => 'Department',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Departments',
            'remoteSource' => ['CompanyStructure', 'id', 'title'],
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
            e.employee_id as 'Employee ID',
            concat(e.first_name, ' ', e.last_name) as 'Name',
            e.gender as 'Gender',
            (SELECT name from EmploymentStatus where id = e.employment_status) as 'Employment Status',
            (SELECT name from JobTitles where id = e.job_title) as 'Job Title',
            (SELECT title from CompanyStructures where id = e.department) as 'Department',
            e.joined_date as 'Joined Date',
            e.work_email as 'Work Email',
            e.mobile_phone as 'Mobile Phone'
        FROM Employees e
        WHERE e.joined_date >= ? AND e.joined_date <= ?";

        $queryParams = [$params['date_start'], $params['date_end']];

        if (!empty($params['department']) && $params['department'] !== 'NULL') {
            $query .= " AND e.department = ?";
            $queryParams[] = $params['department'];
        }

        $query .= " ORDER BY e.joined_date DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
