<?php
namespace Attendance\Admin\Api;

use Classes\AbstractModuleManager;

class AttendanceDashboardManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('Attendance');
    }
}
