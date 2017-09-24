<?php
namespace Employees\Common\Model;

use Model\BaseModel;

class ArchivedEmployee extends BaseModel
{

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save");
    }

    public function getUserOnlyMeAccessField()
    {
        return "id";
    }

    public $table = 'ArchivedEmployees';
}
