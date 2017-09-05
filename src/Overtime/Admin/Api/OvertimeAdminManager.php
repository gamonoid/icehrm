<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:16 PM
 */

namespace Overtime\Admin\Api;

use Classes\AbstractModuleManager;

class OvertimeAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeOvertime");
        }
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('OvertimeCategory');
        $this->addModelClass('EmployeeOvertime');
        $this->addModelClass('EmployeeOvertimeApproval');
    }
}
