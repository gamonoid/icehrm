<?php

namespace Company_assetsAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class AssetType extends BaseModel
{
    public $table = 'AssetTypes';

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('company_assets', 'admin'),
        ];
    }
}
