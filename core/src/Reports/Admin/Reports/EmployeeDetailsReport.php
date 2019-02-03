<?php
namespace Reports\Admin\Reports;

use Classes\BaseService;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;

class EmployeeDetailsReport extends ClassBasedReportBuilder implements ReportBuilderInterface
{

    public function getData($report, $request)
    {
        $filters = [];

        if (!empty($request['department']) && $request['department'] !== "NULL") {
            $filters['department'] = $request['department'];
        }

        if (!empty($request['employment_status']) && $request['employment_status'] !== "NULL") {
            $filters['employment_status'] = $request['employment_status'];
        }

        if (!empty($request['job_title']) && $request['job_title'] !== "NULL") {
            $filters['job_title'] = $request['job_title'];
        }


        $mapping = [
            "job_title" => ["JobTitle","id","name"],
            "nationality" => ["Nationality","id","name"],
            "ethnicity" => ["Ethnicity","id","name"],
            "immigration_status" => ["ImmigrationStatus","id","name"],
            "employment_status" => ["EmploymentStatus","id","name"],
            "pay_grade" => ["PayGrade","id","name"],
            "country" => ["Country","code","name"],
            "province" => ["Province","id","name"],
            "department" => ["CompanyStructure","id","title"],
            "supervisor" => ["Employee","id","first_name+last_name"],
            "approver1" => ["Employee","id","first_name+last_name"],
            "approver2" => ["Employee","id","first_name+last_name"],
            "approver3" => ["Employee","id","first_name+last_name"],
            "indirect_supervisors" => ["Employee","id","first_name+last_name", true],
        ];


        $reportColumns = [
            ['label' => 'Employee ID', 'column' => 'employee_id'],
            ['label' => 'First Name', 'column' => 'first_name'],
            ['label' => 'Middle Name', 'column' => 'middle_name'],
            ['label' => 'Last Name', 'column' => 'last_name'],
            ['label' => 'Nationality', 'column' => 'nationality'],
            ['label' => 'Date of Birth', 'column' => 'birthday'],
            ['label' => 'Gender', 'column' => 'gender'],
            ['label' => 'Marital Status', 'column' => 'marital_status'],
            ['label' => 'Ethnicity', 'column' => 'ethnicity'],
            ['label' => 'Immigration Status', 'column' => 'immigration_status'],
            ['label' => 'SSN Number', 'column' => 'ssn_num'],
            ['label' => 'NIC', 'column' => 'nic_num'],
            ['label' => 'Other ID', 'column' => 'other_id'],
            ['label' => 'Driving License No', 'column' => 'driving_license'],
            ['label' => 'Employment Status', 'column' => 'employment_status'],
            ['label' => 'Job Title', 'column' => 'job_title'],
            ['label' => 'Pay Grade', 'column' => 'pay_grade'],
            ['label' => 'Work Station Id', 'column' => 'work_station_id'],
            ['label' => 'Address Line 1', 'column' => 'address1'],
            ['label' => 'Address Line 2', 'column' => 'address2'],
            ['label' => 'City', 'column' => 'city'],
            ['label' => 'Country', 'column' => 'country'],
            ['label' => 'Province', 'column' => 'province'],
            ['label' => 'Postal Code', 'column' => 'postal_code'],
            ['label' => 'Home Phone', 'column' => 'home_phone'],
            ['label' => 'Mobile Phone', 'column' => 'mobile_phone'],
            ['label' => 'Work Phone', 'column' => 'work_phone'],
            ['label' => 'Work Email', 'column' => 'work_email'],
            ['label' => 'Private Email', 'column' => 'private_email'],
            ['label' => 'Joined Date', 'column' => 'joined_date'],
            ['label' => 'Confirmation Date', 'column' => 'confirmation_date'],
            ['label' => 'Termination Date', 'column' => 'termination_date'],
            ['label' => 'Department', 'column' => 'department'],
            ['label' => 'Supervisor', 'column' => 'supervisor'],
            ['label' => 'Indirect Supervisors', 'column' => 'indirect_supervisors'],
            ['label' => 'First Level Approver', 'column' => 'approver1'],
            ['label' => 'Second Level Approver', 'column' => 'approver2'],
            ['label' => 'Third Level Approver', 'column' => 'approver3'],
        ];

        $customFieldsList = BaseService::getInstance()->getCustomFields('Employee');

        foreach ($customFieldsList as $customField) {
            $reportColumns[] = [
                'label' => $customField->field_label,
                'column' => $customField->name,
            ];
        }

        $entries = BaseService::getInstance()->get('Employee', null, $filters);
        $data = [];
        foreach ($entries as $item) {
            $item =  BaseService::getInstance()->enrichObjectMappings($mapping, $item);
            $item =  BaseService::getInstance()->enrichObjectCustomFields('Employee', $item);
            $data[] = $item;
        }

        $mappedColumns = array_keys($mapping);


        $reportData = [];
        $reportData[] = array_column($reportColumns, 'label');

        foreach ($data as $item) {
            $row = [];
            foreach ($reportColumns as $column) {
                if (in_array($column['column'], $mappedColumns)) {
                    $row[] = $item->{$column['column'].'_Name'};
                } else {
                    $row[] = $item->{$column['column']};
                }
            }
            $reportData[] = $row;
        }

        return $reportData;
    }
}
