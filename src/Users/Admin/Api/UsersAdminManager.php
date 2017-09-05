<?php
namespace Users\Admin\Api;

use Classes\AbstractModuleManager;
use Users\Common\Model\User;

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
}
