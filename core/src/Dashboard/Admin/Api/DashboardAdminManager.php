<?php
namespace Dashboard\Admin\Api;

use Charts\Admin\Rest\ChartsRestEndpoint;
use Classes\AbstractModuleManager;
use Classes\Macaw;
use Dashboard\Admin\Rest\DashboardRestEndpoint;

class DashboardAdminManager extends AbstractModuleManager
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
    }

    public function setupRestEndPoints()
    {
        Macaw::get(REST_API_PATH.'tasks', function () {
            $restEndPoint = new DashboardRestEndpoint();
            $restEndPoint->process('getTaskList');
        });
    }
}
