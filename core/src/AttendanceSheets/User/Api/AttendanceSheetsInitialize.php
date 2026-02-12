<?php
namespace AttendanceSheets\User\Api;

use AttendanceSheets\Common\Model\EmployeeAttendanceSheet;
use Classes\AbstractInitialize;
use Utils\LogManager;

class AttendanceSheetsInitialize extends AbstractInitialize
{

    public function init()
    {
        //Add Employee time sheets if it is not already created for current week
        $empId = $this->getCurrentProfileId();
        if (date('w', strtotime("now")) == 0) {
            $start = date("Y-m-d", strtotime("now"));
        } else {
            $start = date("Y-m-d", strtotime("last Sunday"));
        }

        if (date('w', strtotime("now")) == 6) {
            $end = date("Y-m-d", strtotime("now"));
        } else {
            $end = date("Y-m-d", strtotime("next Saturday"));
        }

        $timeSheet = new EmployeeAttendanceSheet();
        $timeSheet->Load("employee = ? and date_start = ? and date_end = ?", array($empId,$start,$end));
        if ($timeSheet->date_start == $start && $timeSheet->employee == $empId) {
        } else {
            if (!empty($empId)) {
                $timeSheet->employee = $empId;
                $timeSheet->date_start = $start;
                $timeSheet->date_end = $end;
                $timeSheet->status = "Pending";
                $ok = $timeSheet->Save();
                if (!$ok) {
                    LogManager::getInstance()->info("Error creating attendance sheet : ".$timeSheet->ErrorMsg());
                }
            }
        }
    }
}
