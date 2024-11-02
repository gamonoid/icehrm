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
        Macaw::get(
            REST_API_PATH.'tasks',
            function () {
                $restEndPoint = new DashboardRestEndpoint();
                $restEndPoint->process('getTaskList');
            }
        );

		Macaw::get(
			REST_API_PATH.'can-show-news/(:num)',
			function ($pathParams) {
				$restEndPoint = new DashboardRestEndpoint();
				$restEndPoint->process('canShowNews', $pathParams);
			}
		);

		Macaw::post(
			REST_API_PATH.'dismiss-news',
			function () {
				$restEndPoint = new DashboardRestEndpoint();
				$restEndPoint->process('dismissNews');
			}
		);
    }
}
