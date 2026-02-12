<?php
namespace AttendanceSheets\User\Api;

use Classes\AbstractModuleManager;

class AttendanceSheetsModulesManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        $this->addUserClass("EmployeeAttendanceSheet");
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('EmployeeAttendanceSheet');
    }

    public function getInitializer()
    {
        return new AttendanceSheetsInitialize();
    }
}
