<?php

namespace Company_assetsAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class CompanyAsset extends BaseModel
{
    public $table = 'CompanyAssets';
    protected $allowCustomFields = true;

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "add","save");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('company_assets', 'admin'),
        ];
    }

    public function executePreSaveActions($obj)
    {
        // Auto-set status to Assigned when employee is set
        if (!empty($obj->employee) && $obj->status === 'Available') {
            $obj->status = 'Assigned';
        }

        // Auto-set status to Available when employee is cleared
        if (empty($obj->employee) && $obj->status === 'Assigned') {
            $obj->status = 'Available';
        }

        return new \Classes\IceResponse(\Classes\IceResponse::SUCCESS, $obj);
    }
}
