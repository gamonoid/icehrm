<?php
namespace Employees\Admin\Api;

use Classes\BaseService;

class EmployeeUtil
{
    public function getEmployeeDataField($employeeId, $startDate, $endDate, $field)
    {
        $employee = BaseService::getInstance()->getElement(
            'Employee',
            $employeeId,
            $this->getMapping(),
            true
        );
        return [
            'string',
            $employee->$field
        ];
    }

    public function getMapping()
    {
        $mapping = <<<JSON
{
   "nationality":[
      "Nationality",
      "id",
      "name"
   ],
   "ethnicity":[
      "Ethnicity",
      "id",
      "name"
   ],
   "immigration_status":[
      "ImmigrationStatus",
      "id",
      "name"
   ],
   "employment_status":[
      "EmploymentStatus",
      "id",
      "name"
   ],
   "job_title":[
      "JobTitle",
      "id",
      "name"
   ],
   "pay_grade":[
      "PayGrade",
      "id",
      "name"
   ],
   "country":[
      "Country",
      "code",
      "name"
   ],
   "province":[
      "Province",
      "id",
      "name"
   ],
   "department":[
      "CompanyStructure",
      "id",
      "title"
   ],
   "supervisor":[
      "Employee",
      "id",
      "first_name+last_name"
   ],
   "indirect_supervisors":[
      "Employee",
      "id",
      "first_name+last_name"
   ],
   "approver1":[
      "Employee",
      "id",
      "first_name+last_name"
   ],
   "approver2":[
      "Employee",
      "id",
      "first_name+last_name"
   ],
   "approver3":[
      "Employee",
      "id",
      "first_name+last_name"
   ]
}
JSON;

        return $mapping;
    }
}
