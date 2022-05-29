<?php

namespace Attendance\Admin\Api;

use Attendance\Rest\AttendanceRestEndPoint;
use Classes\BaseService;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;

class AttendanceTaskCreator implements TaskCreator
{
    public function getTasksCreators()
    {
        $taskCreators = [];

        $taskCreators[] = function () {
            if (!$this->isUserCheckedIn()) {
                return (new Task(Task::PRIORITY_ERROR, 'You are currently not checked-in'))
                    ->setLink(
                        CLIENT_BASE_URL.'?g=modules&n=attendance&m=module_Time_Management',
                        'Visit Attendance'
                    );
            }

            return null;
        };

        $taskCreators[] = function () {
            if ($this->userNeedToCheckOut()) {
                return (new Task(Task::PRIORITY_INFO, 'Remember to checkout after finishing work'))
                    ->setLink(
                        CLIENT_BASE_URL.'?g=modules&n=attendance&m=module_Time_Management',
                        'Visit Attendance'
                    )->setDetails('You have checked in for the day and, should checkout, after finishing work');
            }

            return null;
        };

        return $taskCreators;
    }

    protected function isUserCheckedIn()
    {
        $attendanceRest = new AttendanceRestEndPoint();
        $employeeId = BaseService::getInstance()->getCurrentUserProfileId();
        if (!$employeeId) {
            return true;
        }
        $attendance = $attendanceRest->findAttendance($employeeId, 'today');

        return $attendance->id !== null;
    }

    protected function userNeedToCheckOut()
    {
        $attendanceRest = new AttendanceRestEndPoint();
        $employeeId = BaseService::getInstance()->getCurrentUserProfileId();
        if (!$employeeId) {
            return true;
        }
        $attendance = $attendanceRest->findAttendance($employeeId, 'today');

        return $attendance->in_time && empty($attendance->out_time);
    }
}
