<?php

namespace Employees\Admin\Api;

use Classes\BaseService;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeStatus;
use Utils\CalendarTools;

class EmployeeTaskCreator implements TaskCreator
{

    public function getTasksCreators()
    {
        $taskCreators = [];

        $user = BaseService::getInstance()->getCurrentUser();

        if ('Admin' === $user->user_level) {
            $taskCreators[] = function () {
                $employee = new Employee();
                $employee->Load('employee_id = ?', ['EMP001']);
                if ($employee->first_name === 'IceHrm') {
                    return (new Task(Task::PRIORITY_ERROR, 'Default employee has not been removed or modified'))
                        ->setLink(
                            CLIENT_BASE_URL.'?g=admin&n=employees&m=admin_Employees',
                            'Edit Employees'
                        );
                }

                return null;
            };
        }

        $employeeId = $user->employee;
        if (!empty($employeeId)) {
            $taskCreators[] = function () use ($employeeId) {

                $employeeState = new EmployeeStatus();
                $employeeState->Load('employee = ? and status_date = ?', [ $employeeId, CalendarTools::getServerDate()]);
                if (empty($employeeState->id)) {
                    return (new Task(Task::PRIORITY_TOP, 'You haven\'t set your status for the day.'))
                        ->setLink(
                            CLIENT_BASE_URL.'?g=modules&n=dashboard&m=module_Personal_Information',
                            'Set your Status and Goals'
                        );
                }

                return null;
            };
        }




        return $taskCreators;
    }
}
