<?php
namespace StaffDirectory\User\Api;

use Classes\AbstractModuleManager;
use StaffDirectory\Rest\StaffDirectoryRestEndPoint;

class StaffDirectoryModulesManager extends AbstractModuleManager
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
        $this->addModelClass('StaffDirectory');
    }

    public function setupRestEndPoints()
    {
        \Classes\Macaw::get(REST_API_PATH.'staff', function () {
            $empRestEndPoint = new StaffDirectoryRestEndPoint();
            $empRestEndPoint->process('listAll');
        });
    }
}
