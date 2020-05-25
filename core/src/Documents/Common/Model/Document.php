<?php
namespace Documents\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Document extends BaseModel
{
    public $table = 'Documents';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }
}
