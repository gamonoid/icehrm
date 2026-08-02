<?php
namespace Advance_reportsAdmin\Reports;

class EmployeeDetailsReport extends BaseReport
{
    protected $name = 'Employee Details Report';
    protected $description = 'This report lists all employee details filtered by department, employment status or job title';
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
            'name' => 'employment_status',
            'label' => 'Employment Status',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Statuses',
            'remoteSource' => ['EmploymentStatus', 'id', 'name'],
        ],
        [
            'name' => 'job_title',
            'label' => 'Job Title',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Job Titles',
            'remoteSource' => ['JobTitle', 'id', 'name'],
        ],
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            e.id,
            e.employee_id as 'Employee ID',
            concat(e.first_name, ' ', IFNULL(e.middle_name, ''), ' ', e.last_name) as 'Name',
            (SELECT name from Nationality where id = e.nationality) as 'Nationality',
            e.birthday as 'Birthday',
            e.gender as 'Gender',
            e.marital_status as 'Marital Status',
            e.ssn_num as 'SSN Number',
            e.nic_num as 'NIC Number',
            e.other_id as 'Other IDs',
            e.driving_license as 'Driving License Number',
            (SELECT name from EmploymentStatus where id = e.employment_status) as 'Employment Status',
            (SELECT name from JobTitles where id = e.job_title) as 'Job Title',
            (SELECT name from PayGrades where id = e.pay_grade) as 'Pay Grade',
            e.work_station_id as 'Work Station ID',
            e.address1 as 'Address 1',
            e.address2 as 'Address 2',
            e.city as 'City',
            (SELECT name from Country where code = e.country) as 'Country',
            (SELECT name from Province where id = e.province) as 'Province',
            e.postal_code as 'Postal Code',
            e.home_phone as 'Home Phone',
            e.mobile_phone as 'Mobile Phone',
            e.work_phone as 'Work Phone',
            e.work_email as 'Work Email',
            e.private_email as 'Private Email',
            e.joined_date as 'Joined Date',
            e.confirmation_date as 'Confirmation Date',
            (SELECT title from CompanyStructures where id = e.department) as 'Department',
            (SELECT concat(first_name, ' ', last_name, ' [', employee_id, ']') from Employees e1 where e1.id = e.supervisor) as 'Manager'
        FROM Employees e
        WHERE e.status = 'Active'";

        $queryParams = [];

        if (!empty($params['department']) && $params['department'] !== 'NULL') {
            $query .= " AND e.department = ?";
            $queryParams[] = $params['department'];
        }

        if (!empty($params['employment_status']) && $params['employment_status'] !== 'NULL') {
            $query .= " AND e.employment_status = ?";
            $queryParams[] = $params['employment_status'];
        }

        if (!empty($params['job_title']) && $params['job_title'] !== 'NULL') {
            $query .= " AND e.job_title = ?";
            $queryParams[] = $params['job_title'];
        }

        $query .= " ORDER BY e.first_name, e.last_name";

        return $this->executeQuery($query, $queryParams);
    }
}
