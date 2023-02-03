<?php
namespace Users\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\Macaw;
use Users\Common\Model\User;
use Users\Rest\UserRestEndPoint;

class UsersAdminManager extends AbstractModuleManager
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

        $this->addModelClass('User');
        $this->addModelClass('UserRole');
    }

    public function getDashboardItemData()
    {
        $data = array();
        $user = new User();
        $data['numberOfUsers'] = $user->Count("1 = 1");
        return $data;
    }

    public function setupRestEndPoints()
    {
        Macaw::post(
            REST_API_PATH.'oauth/token', function ($pathParams) {
                $userRestEndpoint = new UserRestEndPoint();
                $userRestEndpoint->process('post', $pathParams, false);
            }
        );
    }
}
