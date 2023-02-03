<?php
namespace Data\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class DataImport extends BaseModel
{
    public $table = 'DataImport';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('data', 'admin'),
        ];
    }

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreSaveActions($obj)
    {
        if (!empty($obj->details)) {
            $obj->details = base64_decode($obj->details);
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreUpdateActions($obj)
    {
        return $this->executePreSaveActions($obj);
    }
}
