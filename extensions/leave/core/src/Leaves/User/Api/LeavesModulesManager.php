<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:38 PM
 */

namespace Leaves\User\Api;

use Classes\AbstractModuleManager;
use Classes\BaseService;
use Leaves\Common\Model\EmployeeLeave;

class LeavesModulesManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeLeave");
        }
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping("EmployeeLeave", "attachment", "name");
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping(
            "Duplicate entry|for key 'workdays_name_country'",
            "You have already defined this work day for selected country"
        );
        $this->addDatabaseErrorMapping(
            "Duplicate entry|for key 'holidays_dateh_country'",
            "You have already defined this holiday for selected country"
        );
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('EmployeeLeave');
        $this->addModelClass('EmployeeLeaveDay');
        $this->addModelClass('EmployeeLeaveLog');
        $this->addModelClass('EmployeeLeaveApprove');
    }

    public function getDashboardItemData()
    {
        $data = array();

        $leave = new EmployeeLeave();
        $pendingLeaves = $leave->Count(
            "status = ? and employee = ?",
            array("Pending", BaseService::getInstance()->getCurrentProfileId())
        );
        $data['pendingLeaveCount'] = $pendingLeaves;
        return $data;
    }
}
