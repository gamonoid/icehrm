<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:21 PM
 */

namespace TimeSheets\User\Api;

use Classes\AbstractInitialize;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Utils\LogManager;

class TimeSheetsInitialize extends AbstractInitialize
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

        $timeSheet = new EmployeeTimeSheet();
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
                    LogManager::getInstance()->info("Error creating time sheet : ".$timeSheet->ErrorMsg());
                }
            }
        }

        //Generate missing timesheets
    }
}
