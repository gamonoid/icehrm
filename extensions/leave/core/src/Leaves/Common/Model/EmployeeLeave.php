<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:34 PM
 */

namespace Leaves\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\GoogleCalendarApiManager;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeavesActionManager;
use Leaves\Admin\Api\LeaveUtil;
use Model\BaseModel;

class EmployeeLeave extends BaseModel
{
    public $table = 'EmployeeLeaves';
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        // "save" lets an employee apply for / edit their OWN leave (the Apply Leave button).
        // Scoped to own records via the me-only access mechanism (same pattern as
        // EmployeeExpense / Attendance), so it does not grant access to other employees' leave.
        return array("element","add","save","delete");
    }

    /**
     * Mass-assignment guard: 'status' is set only by the approval workflow
     * (ApproveAdminActionManager::changeStatus, which Saves the model directly and does
     * NOT pass through addElement). Block it on the generic save/add path for non-admins
     * so the owner cannot self-approve by posting a=save&t=EmployeeLeave with
     * status='Approved'. Admins may still make manual corrections.
     */
    public function getProtectedFields($user)
    {
        if (!empty($user) && $user->user_level === 'Admin') {
            return array();
        }
        return array('status');
    }

    public function executePreDeleteActions($obj)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if ($obj->status !== 'Pending' && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, 'You are not allowed to delete this leave');
        }
        return new IceResponse(IceResponse::SUCCESS, null);
    }

    public function allowIndirectMapping()
    {
        if (SettingsManager::getInstance()->getSetting("Leave: Allow Indirect Admins to Approve") == "1") {
            return true;
        }
        return false;
    }

    public function getDisplayName()
    {
        return "Leave Request";
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('leaves', 'admin'),
            new ModuleAccess('leaves', 'user'),
        ];
    }

    public function getTotalLeaves()
    {
        $leaveActionManager = new LeavesActionManager();
        $leaveActionManager->setBaseService(BaseService::getInstance());
        $req = new \stdClass();
        $req->leave_id = $this->id;
        $num= $leaveActionManager->getLeaveDaysReadonly($req);
        return LeaveUtil::countLeaveAmountByDays($num->data[0]);
    }

    public function postProcessGetData($entry)
    {
        $entry->total_leaves = $this->getTotalLeaves();
        
        // Add employee profile image
        $employee = new Employee();
        $employee->Load('id = ?', [$entry->employee]);
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);
        $entry->image = $employee->image;
        
        return $entry;
    }

    public function executePostDeleteActions($obj)
    {
        try {
            GoogleCalendarApiManager::deleteLeaveEvents($obj);
        } catch (\Exception $e) {
        }
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
