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
        'add' => 'Add',
        'save' => 'Edit',
        'delete' => 'Delete',
    ];

    public static function isRestrictedUserLevel($userLevel)
    {
        return in_array($userLevel, self::RESTRICTED_USER_LEVELS);
    }

    public static function manipulationAllowed($employeeId, BaseModel $object)
    {
        $subIds = self::getSubordinateIds($employeeId, $object->allowIndirectMapping());

        // Identify an employee record by CLASS, not by $object->table. The table property
        // is public and BaseService::cleanUpAdoDB() unsets it before objects are returned
        // (so the schema is not leaked to the client) — an Employee that has been through
        // the service layer therefore has no ->table, would fall through to the branch
        // below, and be tested on a ->employee property it does not have. That denies a
        // genuine subordinate silently, with no error and nothing logged.
        //
        // Employee is the only class declaring table = 'Employees', so this matches exactly
        // the same objects as before — it just cannot be defeated by an unset().
        if ($object instanceof Employee) {
            return in_array($object->id, $subIds);
        }

        return in_array($object->employee, $subIds);
    }

    /**
     * The employee ids $employeeId may act on: themselves plus their subordinates.
     *
     * Public accessor for the same set manipulationAllowed() uses, so callers that need
     * the set ONCE for a whole result page (BaseService::projectForViewer) don't have to
     * call manipulationAllowed() per row — that would re-run the subordinate lookups for
     * every record. Keeping the rule itself here means there is still one definition of
     * "who reports to me".
     */
    public static function getAccessibleEmployeeIds($employeeId, $addIndirect = false)
    {
        return self::getSubordinateIds($employeeId, $addIndirect);
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

    public static function checkGeneralAccess($object, $user = null )
    {
        $currentUser = $user ?? BaseService::getInstance()->getCurrentUser();
        return $object->getRoleBasedAccess($currentUser->user_level, $currentUser->user_roles);
    }
}
