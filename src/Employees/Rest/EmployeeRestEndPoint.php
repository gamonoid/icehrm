<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:42 AM
 */

namespace Employees\Rest;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;

class EmployeeRestEndPoint extends RestEndPoint
{
    public function get($parameter)
    {

        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, "Employee ID not provided");
        }

        if ($parameter === 'list') {
            $emp = new Employee();
            $emps = $emp->Find("1=1");
            $newEmps = array();
            foreach ($emps as $emp) {
                $emp = BaseService::getInstance()->cleanUpAdoDB($emp);
                $emp = Employee::cleanEmployeeData($emp);
                $newEmps[] = $emp;
            }
            return new IceResponse(IceResponse::SUCCESS, $newEmps);
        } else {
            $mapping = '{"nationality":["Nationality","id","name"],"ethnicity":["Ethnicity","id","name"],'
                .'"immigration_status":["ImmigrationStatus","id","name"],'
                .'"employment_status":["EmploymentStatus","id","name"],'
                .'"job_title":["JobTitle","id","name"],"pay_grade":["PayGrade","id","name"],'
                .'"country":["Country","code","name"],"province":["Province","id","name"],'
                .'"department":["CompanyStructure","id","title"],'
                .'"supervisor":["Employee","id","first_name+last_name"]}';
            $emp = BaseService::getInstance()->getElement('Employee', $parameter, $mapping, true);
            if (!empty($emp)) {
                $emp = Employee::cleanEmployeeData($emp);
                return new IceResponse(IceResponse::SUCCESS, $emp);
            }
            return new IceResponse(IceResponse::ERROR, "Employee not found", 404);
        }
    }
}
