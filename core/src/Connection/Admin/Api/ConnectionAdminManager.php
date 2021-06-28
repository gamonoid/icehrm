<?php

namespace Connection\Admin\Api;

use Classes\AbstractModuleManager;
use Connection\Common\ConnectionService;

class ConnectionAdminManager extends AbstractModuleManager
{

    public function initialize()
    {
        $iceConnect = new ConnectionService();
        if ($iceConnect->dispatchInstallationRequest()) {
            $iceConnect->reportInstallationData();
        }
    }

    public function initializeUserClasses()
    {
        // TODO: Implement initializeUserClasses() method.
    }

    public function initializeFieldMappings()
    {
        // TODO: Implement initializeFieldMappings() method.
    }

    public function initializeDatabaseErrorMappings()
    {
        // TODO: Implement initializeDatabaseErrorMappings() method.
    }

    public function setupModuleClassDefinitions()
    {
        // TODO: Implement setupModuleClassDefinitions() method.
    }
}
