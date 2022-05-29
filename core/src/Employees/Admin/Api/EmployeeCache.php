<?php


namespace Employees\Admin\Api;

use Classes\BaseService;
use Employees\Common\Model\Employee;

class EmployeeCache
{
    private static $subordinateCache = [];

    public static function getSubordinateIds()
    {
        $employeeId = BaseService::getInstance()->getCurrentProfileId();
        if (empty($employeeId)) {
            return [];
        }

        if (isset(self::$subordinateCache[$employeeId])) {
            return self::$subordinateCache[$employeeId];
        }

        $employee = new Employee();
        $subs = $employee->Find('supervisor = ?', [$employeeId]);
        $subIds = array_map(function ($item) {
            return $item->id;
        }, $subs);

        self::$subordinateCache[$employeeId] = $subIds;

        return $subIds;
    }
}
