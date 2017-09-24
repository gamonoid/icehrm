<?php

namespace Dependents\Common\Model;

use Model\BaseModel;

class EmployeeDependent extends BaseModel
{
    public $table = 'EmployeeDependents';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }
}
