<?php
namespace Advance_reportsAdmin\Reports;

use Company\Common\Model\CompanyStructure;

class ActiveEmployeeReport extends BaseReport
{
    protected $name = 'Active Employee Report';
    protected $description = 'This report lists employees who are currently active based on joined date and termination date';
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
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            e.id,
            e.employee_id as 'Employee ID',
            concat(e.first_name, ' ', e.last_name) as 'Name',
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
            (SELECT concat(first_name, ' ', last_name) from Employees e1 where e1.id = e.supervisor) as 'Manager',
            e.notes as 'Notes'
        FROM Employees e";

        $where = "";
        $queryParams = [];

        $departmentId = filter_var($params['department'] ?? null, FILTER_VALIDATE_INT);
        if ($departmentId !== false && $departmentId > 0) {
            // $departmentId is a validated integer; getChildCompanyStructures() seeds its
            // id list from it and only adds DB-derived ids, so the IN (...) list is safe.
            $depts = $this->getChildCompanyStructures($departmentId);
            $where = " WHERE e.department IN (" . implode(",", $depts) . ") AND ";
        } else {
            $where = " WHERE ";
        }

        $where .= "((e.termination_date IS NULL AND e.joined_date < NOW()) OR
                   (e.termination_date > NOW() AND e.joined_date < NOW()))";

        return $this->executeQuery($query . $where, $queryParams);
    }

    private function getChildCompanyStructures($companyStructId): array
    {
        $childIds = [$companyStructId];
        $nodeIdsAtLastLevel = $childIds;
        $count = 0;

        do {
            $count++;
            $companyStructTemp = new CompanyStructure();
            if (empty($nodeIdsAtLastLevel) || empty($childIds)) {
                break;
            }
            $idQuery = "parent in (" . implode(",", $nodeIdsAtLastLevel) . ") and id not in(" . implode(",", $childIds) . ")";
            $list = $companyStructTemp->Find($idQuery, []);

            $nodeIdsAtLastLevel = [];
            foreach ($list as $item) {
                $childIds[] = $item->id;
                $nodeIdsAtLastLevel[] = $item->id;
            }
        } while (count($list) > 0 && $count < 10);

        return $childIds;
    }
}
