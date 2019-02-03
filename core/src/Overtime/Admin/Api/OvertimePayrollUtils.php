<?php
namespace Overtime\Admin\Api;

use Overtime\Common\Model\EmployeeOvertime;

class OvertimePayrollUtils
{
    public function getApprovedTimeInRequests($employeeId, $startDate, $endDate)
    {
        $request = new EmployeeOvertime();
        $requests = $request->Find(
            'employee = ? 
            and ((date(start_time) >= ? and date(start_time) <= ?) 
                or (date(end_time) >= ? and date(end_time) <= ?) 
                or (date(start_time) < ? and date(end_time) > ?)) 
            and status = ?',
            array(
                $employeeId,
                $startDate,
                $endDate,
                $startDate,
                $endDate,
                $startDate,
                $endDate,
                'Approved'
            )
        );

        $seconds = 0;

        $startTime = strtotime($startDate.' 00:00:00');
        $endTime = strtotime($endDate.' 23:59:59');

        foreach ($requests as $entry) {
            $entryStartTime = strtotime($entry->start_time);
            $entryEndTime = strtotime($entry->end_time);

            if ($entryStartTime >= $startTime && $entryEndTime <= $endTime) {
                $secondsTemp = $entryEndTime - $entryStartTime;
            } elseif ($entryStartTime < $startTime && $entryEndTime <= $endTime) {
                $secondsTemp = $entryEndTime - $startTime;
            } elseif ($entryStartTime >= $startTime && $entryEndTime > $endTime) {
                $secondsTemp = $endTime - $entryStartTime;
            } else {
                $secondsTemp = $endTime - $startTime;
            }

            if ($secondsTemp < 0) {
                $secondsTemp = 0;
            }

            $seconds += $secondsTemp;
        }

        $totMinutes = round($seconds / 60);

        return round($totMinutes / 60, 2);
    }
}
