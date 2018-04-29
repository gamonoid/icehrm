<?php
namespace Data\Admin\Api;

use Classes\AbstractModuleManager;

class DataAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping('DataImportFile', 'file', 'name');
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('DataImport');
        $this->addModelClass('DataImportFile');
    }
}
