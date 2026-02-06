<?php

namespace Attendance\Admin\Api;

use Attendance\Common\Model\AttendanceLog;
use Classes\BaseService;

class AttendanceLogUtil
{
    public static function logChange($employeeId, $field, $oldValue, $newValue)
    {
        if ($oldValue === $newValue) {
            return;
        }

        $log = new AttendanceLog();
        $log->employee = $employeeId;
        $log->field_changed = $field;
        $log->old_value = $oldValue;
        $log->new_value = $newValue;

        $user = BaseService::getInstance()->getCurrentUser();
        $log->changed_by = $user->id ?? 0;
        $log->Save();
    }
}
