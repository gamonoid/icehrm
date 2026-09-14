<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:14 PM
 */

namespace Overtime\Common\Model;

use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Model\ApproveModel;

class EmployeeOvertime extends ApproveModel
{
    public $table = 'EmployeeOvertime';

    public $notificationModuleName = "Overtime Management";
    public $notificationUnitName = "OvertimeRequest";
    public $notificationUnitPrefix = "An";
    public $notificationUnitAdminUrl = "g=modules&n=overtime&m=module_Time_Management#tabSubordinateEmployeeOvertime";
    public $preApproveSettingName = "Attendance: Pre-Approve Overtime Request";

    public function isMultiLevelApprovalsEnabled()
    {
        return (SettingsManager::getInstance()->getSetting('Overtime: Enable Multi Level Approvals') == '1');
    }

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element", "add","save", "delete");
    }

    /**
     * Mass-assignment guard: 'status' is set only by the approval workflow
     * (ApproveAdminActionManager::changeStatus, which Saves the model directly and does
     * NOT pass through addElement). Block it on the generic save/add path for non-admins
     * so the owner cannot self-approve by posting a=add&t=EmployeeOvertime with
     * status='Approved'. ApproveModel::executePreSaveActions only defaults status when it
     * is EMPTY, so without this a supplied value is written straight through.
     * Admins may still make manual corrections.
     */
    public function getProtectedFields($user)
    {
        if (!empty($user) && $user->user_level === 'Admin') {
            return array();
        }
        return array('status');
    }

    public function fieldsNeedToBeApproved()
    {
        return array(
            "start_time",
            "end_time"
        );
    }

    public function getType()
    {
        return 'EmployeeOvertime';
    }

    public function allowIndirectMapping()
    {
        if (SettingsManager::getInstance()->getSetting('Overtime: Allow Indirect Admins to Approve') == '1') {
            return true;
        }
        return false;
    }

    public function validateSave($obj)
    {
        if (strtotime($obj->start_time) >= strtotime($obj->end_time)) {
            return new IceResponse(IceResponse::ERROR, 'Incorrect start and end time');
        }
        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('overtime', 'admin'),
            new ModuleAccess('overtime', 'user'),
        ];
    }

    public function postProcessGetData($obj)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$obj->employee]);
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);
        $obj->image = $employee->image;

        return $obj;
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
