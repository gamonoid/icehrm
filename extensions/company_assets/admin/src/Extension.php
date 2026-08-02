<?php

namespace Company_assetsAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use Company_assetsAdmin\Migrations\CreateTables;

class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('AssetType', '\\Company_assetsAdmin\\Common\\Model\\AssetType');
        $this->addModelClass('CompanyAsset', '\\Company_assetsAdmin\\Common\\Model\\CompanyAsset');
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
