<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:40 AM
 */

namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeStatus extends BaseModel
{

    public $table = 'EmployeeStatus';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }
}
