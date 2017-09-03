<?php
namespace Data\Common\Model;

use Model\BaseModel;

class DataImport extends BaseModel
{
    var $_table = 'DataImport';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }
}
