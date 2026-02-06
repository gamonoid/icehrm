<?php
namespace Employees\Services;

use Classes\FileService;
use Employees\Common\Model\Employee;

class EmployeesService
{
    public static function getDirectReports($employeeId)
    {
        $sub = new Employee();
        $subordinates = $sub->Find("supervisor = ?", array($employeeId));

        return $subordinates;
    }

    public static function getRandomEmployees($count)
    {
        $employee = new Employee();
        $employees = $employee->Find("active = ? ORDER BY RAND() LIMIT ?", array(1, $count));

        return $employees;
    }

	public static function getEmployeePublicDataWithImage($employeeId) {
		$employee = new Employee();
		$employee->Load('id = ?', [$employeeId]);
		if (empty($employee->id)) {
			return null;
		}

		$employee = FileService::getInstance()->updateSmallProfileImage($employee);

		$result = new \stdClass();
		$result->name = $employee->first_name.' '.$employee->last_name;
		$result->image = $employee->image;

		return $result;
	}
}
