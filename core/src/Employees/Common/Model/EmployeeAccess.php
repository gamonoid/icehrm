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

    /**

     * No module grants Employee access to this model (module meta.json user_levels),

     * so no employee-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getUserOnlyMeAccess()

    {

        return array();

    }

}
