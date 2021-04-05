<?php

namespace CustomField\Admin\Api;

use Classes\AbstractModuleManager;

class CustomFieldAdminManager extends AbstractModuleManager
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
        $this->addModelClass('CustomField');
    }
}
