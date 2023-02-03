<?php
namespace Charts\Admin\Api;

use Charts\Admin\Rest\ChartsRestEndpoint;
use Classes\AbstractModuleManager;
use Classes\Macaw;
use Employees\Rest\EmployeeRestEndPoint;

class ChartsAdminManager extends AbstractModuleManager
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
        Macaw::get(
            REST_API_PATH.'charts/company-leave-entitlement', function () {
                $empRestEndPoint = new ChartsRestEndpoint();
                $empRestEndPoint->process('getCompanyLeaveEntitlement');
            }
        );

        Macaw::get(
            REST_API_PATH.'charts/employee-check-ins', function () {
                $empRestEndPoint = new ChartsRestEndpoint();
                $empRestEndPoint->process('getCompanyEmployeeCheckIns');
            }
        );

        Macaw::get(
            REST_API_PATH.'charts/employees-distribution', function () {
                $empRestEndPoint = new ChartsRestEndpoint();
                $empRestEndPoint->process('getEmployeeDistribution');
            }
        );
    }
}
