<?php
namespace TimeSheets\User\Api;

use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;

class TimeSheetsPayrollUtils
{
    public function getApprovedTimeInTimeSheets($employeeId, $startDate, $endDate)
    {
        $timeSheet = new EmployeeTimeSheet();
        $timeSheets = $timeSheet->Find(
            'employee = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?)) and status = ?',
            array(
                $employeeId,
                $startDate,
                $endDate,
                $startDate,
                $endDate,
                'Approved'
            )
        );

        $timeSheetIds = [];
        foreach ($timeSheets as $timeSheet) {
            $timeSheetIds[] = $timeSheet->id;
        }

        $start = $startDate . " 00:00:00";
        $end = $endDate . " 23:59:59";

        $timeEntry = new EmployeeTimeEntry();
        $list = $timeEntry->Find(
            "employee = ? and ((date_start >= ? and date_start <= ?) 
			or (date_end >= ? and date_end <= ?)) and timesheet in 
			(".implode(',', $timeSheetIds).")",
            array(
                $employeeId,
                $start,
                $end,
                $start,
                $end
            )
        );

        $seconds = 0;

        foreach ($list as $entry) {
            $secondsTemp = (strtotime($entry->date_end) - strtotime($entry->date_start));
            if ($secondsTemp < 0) {
                $secondsTemp = 0;
            }

            $seconds += $secondsTemp;
        }

        $totMinutes = round($seconds / 60);

        return round($totMinutes / 60, 2);
    }
}
