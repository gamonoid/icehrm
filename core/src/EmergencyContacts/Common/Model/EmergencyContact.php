<?php
namespace EmergencyContacts\Common\Model;

use Model\BaseModel;

class EmergencyContact extends BaseModel
{
    public $table = 'EmergencyContacts';

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
}
