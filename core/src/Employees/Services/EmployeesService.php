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
}
