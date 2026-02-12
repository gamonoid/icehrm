<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 11:11 PM
 */

namespace Salary\Common\Model;

use Classes\FileService;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class EmployeeSalary extends BaseModel
{
    public $table = 'EmployeeSalary';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array("get", "element");
    }

    public function getUserOnlyMeSwitchedAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('salary', 'admin'),
        ];
    }

    public function postProcessGetData($obj)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$obj->employee]);
        if ($employee->id) {
            $employee = FileService::getInstance()->updateSmallProfileImage($employee);
            $obj->image = $employee->image;
        }

        return $obj;
    }
}
