<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:18 PM
 */

namespace TimeSheets\Common\Model;

use Attendance\Common\Model\Attendance;
use Classes\IceResponse;
use Classes\SettingsManager;
use Model\BaseModel;

class EmployeeTimeEntry extends BaseModel
{
    public $table = 'EmployeeTimeEntry';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function validateSave($obj)
    {
        if (SettingsManager::getInstance()->getSetting("Attendance: Time-sheet Cross Check") == "1") {
            $attendance = new Attendance();
            $list = $attendance->Find(
                "employee = ? and in_time <= ? and out_time >= ?",
                array($obj->employee,$obj->date_start,$obj->date_end)
            );
            if (empty($list) || count($list) == 0) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "The time entry can not be added since you have not marked attendance for selected period"
                );
            }
        }
        return new IceResponse(IceResponse::SUCCESS, "");
    }
}
