<?php
namespace Employees\Admin\Api;

use Classes\BaseService;
use Metadata\Common\Model\CustomFieldValue;

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

        // A direct field on the employee record (a DB column or a mapped field
        // such as job_title / pay_grade).
        if (isset($employee->$field) && $employee->$field !== null && $employee->$field !== '') {
            return ['string', $employee->$field];
        }

        // Otherwise fall back to an employee CUSTOM field value. Custom fields are
        // not columns on the Employees table — they live in CustomFieldValues keyed
        // by field name — so a payroll column can pull any employee custom field by
        // setting its calculation_function to the custom field's name.
        $customFieldValue = new CustomFieldValue();
        $customFieldValue->Load(
            'type = ? and name = ? and object_id = ?',
            ['Employee', $field, $employeeId]
        );
        if ($customFieldValue->object_id == $employeeId) {
            return ['string', $customFieldValue->value];
        }

        return ['string', $employee->$field];
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
