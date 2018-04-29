<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:17 PM
 */

namespace Overtime\Admin\Api;

use Classes\Approval\ApproveAdminActionManager;

class OvertimeActionManager extends ApproveAdminActionManager
{

    public function getModelClass()
    {
        return "EmployeeOvertime";
    }

    public function getItemName()
    {
        return "Overtime Request";
    }

    public function getModuleName()
    {
        return "Overtime Management";
    }

    public function getModuleTabUrl()
    {
        return "g=modules&n=overtime&m=module_Time_Management#tabEmployeeOvertime";
    }

    public function getModuleSubordinateTabUrl()
    {
        return "g=modules&n=overtime&m=module_Time_Management#tabSubordinateEmployeeOvertime";
    }

    public function getModuleApprovalTabUrl()
    {
        return "g=modules&n=overtime&m=module_Time_Management#tabEmployeeOvertimeApproval";
    }
}
