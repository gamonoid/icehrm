<?php

namespace Leaves\Admin\Api;

use Classes\BaseService;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;
use Employees\Common\Model\Employee;
use Employees\Services\EmployeesService;
use Leaves\Common\Model\HoliDay;

class LeaveTaskCreator implements TaskCreator
{

    public function getTasksCreators()
    {
        $taskCreators = [];

        $user = BaseService::getInstance()->getCurrentUser();

        if ('Admin' === $user->user_level) {
            $taskCreators[] = function () {
                $holiday = new HoliDay();
                $query = "select count(id) as c from HoliDays where dateh >= ? and dateh <= ?";
                $rs = $holiday->DB()->Execute($query, [date('Y-01-01'), date('Y-12-31')]);
                $count = $rs[0]['c'];

                if ($count == 0) {
                    return (new Task(Task::PRIORITY_ERROR, 'No holidays defined for the current year'))
                        ->setLink(
                            CLIENT_BASE_URL.'?g=admin&n=leaves&m=admin_Manage#tabHoliDay',
                            'Update Holidays'
                        );
                }

                return null;
            };
        }

        $taskCreators[] = function () {
            $pendingLeaveCount = $this->getSubordinatePendingLeaveCount();
            if ($pendingLeaveCount > 0) {
                return (new Task(
                    Task::PRIORITY_INFO,
                    sprintf(
                        '%s pending leave request%s to resolve',
                        $pendingLeaveCount,
                        $pendingLeaveCount > 0 ? 's' : ''
                    )
                ))->setLink(
                    CLIENT_BASE_URL.'?g=modules&n=leaves&m=module_Leave#tabSubEmployeeLeaveAll',
                    'Approve/Reject Requests'
                );
            }

            return null;
        };

        return $taskCreators;
    }

    protected function getSubordinatePendingLeaveCount()
    {

        $user = BaseService::getInstance()->getCurrentUser();
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        if (empty($employee->id)) {
            return 0;
        }

        $subordinates = array_map(
            function ($employee) {
                return $employee->id;
            }, EmployeesService::getDirectReports($user->employee)
        );

        if (count($subordinates) === 0) {
            return 0;
        }

        $query = "select count(id) as c from EmployeeLeaves where employee in (%s) and status = 'Pending'";
        $query = sprintf($query, implode(',', $subordinates));
        // $user->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        $rs = $user->DB()->Execute($query);
        $count = $rs[0]['c'];

        return $count;
    }
}
