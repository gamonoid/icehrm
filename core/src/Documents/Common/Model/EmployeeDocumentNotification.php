<?php
namespace Documents\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeDocumentNotification extends BaseModel
{
    public $table = 'EmployeeDocumentNotifications';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }
}
