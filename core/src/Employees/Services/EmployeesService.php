<?php
namespace Employees\Services;

use Employees\Common\Model\Employee;

class EmployeesService
{
    public function getDirectReports($employeeId)
    {
        $sub = new Employee();
        $subordinates = $sub->Find("supervisor = ?", array($employeeId));

        return $subordinates;
    }

    public function getRandomEmployees($count)
    {
        $employee = new Employee();
        $employees = $employee->Find("active = ? ORDER BY RAND() LIMIT ?", array(1, $count));

        return $employees;
    }
}
