<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 11/3/17
 * Time: 4:11 PM
 */

namespace Classes;

use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class PermissionManager
{
    const RESTRICTED_USER_LEVELS = ['Restricted Admin', 'Restricted Manager', 'Restricted Employee'];

    const ACCESS_LIST_DESCRIPTION = [
        'get' => 'List',
        'element' => 'View Details',
        'save' => 'Add/Edit',
        'delete' => 'Delete',
    ];

    public static function isRestrictedUserLevel($userLevel)
    {
        return in_array($userLevel, self::RESTRICTED_USER_LEVELS);
    }

    public static function manipulationAllowed($employeeId, BaseModel $object)
    {
        $subIds = self::getSubordinateIds($employeeId, $object->allowIndirectMapping());
        if ($object->table === 'Employees') {
            return in_array($object->id, $subIds);
        }

        return in_array($object->employee, $subIds);
    }

    private static function getSubordinateIds($employeeId, $addIndirect)
    {
        $subIds = [$employeeId];
        $employee = new Employee();
        $list = $employee->Find("supervisor = ?", array($employeeId));

        foreach ($list as $emp) {
            $subIds[] = $emp->id;
        }

        if ($addIndirect) {
            $list = $employee->Find("indirect_supervisors like ?", array('%\"'.$employeeId.'\"%'));
            foreach ($list as $emp) {
                $subIds[] = $emp->id;
            }
        }

        return $subIds;
    }

    public static function checkGeneralAccess($object)
    {
        $currentUser = BaseService::getInstance()->getCurrentUser();
        return $object->getRoleBasedAccess($currentUser->user_level, $currentUser->user_roles);
    }
}
