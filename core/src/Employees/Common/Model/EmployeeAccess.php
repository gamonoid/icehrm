<?php


namespace Employees\Common\Model;

use Classes\PermissionManager;
use Model\BaseModel;

class EmployeeAccess extends BaseModel
{

    public static function hasAccessToAllEmployeeData()
    {
        $access = PermissionManager::checkGeneralAccess(new EmployeeAccess());

        return in_array('get', $access);
    }

    public function getAdminAccess()
    {
        return [];
    }

    public function getManagerAccess()
    {
        return [];
    }

    public function getUserAccess()
    {
        return [];
    }

    public function getEmployeeAccess()
    {
        return [];
    }

    public function getDefaultAccessLevel()
    {
        return array();
    }

    public function getMatchingUserRoles($userRoles)
    {
        return json_decode($userRoles, true);
    }
}
