<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 1:43 PM
 */

namespace Leaves\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Leaves\Admin\Api\LeaveUtil;
use Model\BaseModel;

class LeaveType extends BaseModel
{
    public $table = 'LeaveTypes';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function isProcessMappings()
    {
        return true;
    }

    public function getUserLeaveTypes()
    {
        $ele = new LeaveType();
        $employeeId = BaseService::getInstance()->getCurrentProfileId();

        $leaveGroupIds = LeaveUtil::getEmployeeLeaveGroups($employeeId);


        if (empty($leaveGroupIds)) {
            $list = $ele->Find('leave_group IS NULL', array());
        } else {
            $list = $ele->Find("leave_group IS NULL or leave_group in (".implode(',', $leaveGroupIds).")", array());
        }

        return $list;
    }

    public function fieldValueMethods()
    {
        return ['getUserLeaveTypes'];
    }

    public function executePreSaveActions($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        $leaveType = new LeaveType();
        $leaveType->Load('id = ?', [$obj->id]);
        $obj->employee_leave_period = $leaveType->employee_leave_period;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('leaves', 'admin'),
        ];
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
