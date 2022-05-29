<?php

namespace Classes;

use Employees\Common\Model\Employee;
use Users\Common\Model\User;

class StatsHelper
{
    public static function getEmployeeCount()
    {
        $employee = new Employee();
        $employeeCount = $employee->DB()->Execute("select count(id) from Employees");
        if ($employeeCount) {
            $employeeCount = intval($employeeCount[0]['count(id)']);
            return $employeeCount;
        }

        return 0;
    }

    public static function getActiveEmployeeCount()
    {
        $employee = new Employee();
        $employeeCount = $employee->DB()->Execute("select count(id) from Employees where  status = ?", ['Active']);
        if ($employeeCount) {
            $employeeCount = intval($employeeCount[0]['count(id)']);
            return $employeeCount;
        }

        return 0;
    }

    public static function getUserCount()
    {
        $user = new User();
        $userCount = $user->DB()->Execute("select count(id) from Users");
        if ($userCount) {
            $userCount = intval($userCount[0]['count(id)']);
            return $userCount;
        }

        return 0;
    }
}
