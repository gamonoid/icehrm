<?php
namespace Data\Common\Model;

use Classes\IceResponse;
use Model\BaseModel;

class DataImportFile extends BaseModel
{
    public $table = 'DataImportFiles';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function executePreSaveActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = "Not Processed";
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
