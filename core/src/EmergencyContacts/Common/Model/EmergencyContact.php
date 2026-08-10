<?php
namespace EmergencyContacts\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmergencyContact extends BaseModel
{
    public $table = 'EmergencyContacts';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
