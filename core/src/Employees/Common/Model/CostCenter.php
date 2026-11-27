<?php
namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Cost centres employees are charged to (Company Structure → Cost Centers).
 */
class CostCenter extends BaseModel
{
    public $table = 'CostCenters';

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('company_structure', 'admin'),
            new ModuleAccess('employees', 'admin'),
        ];
    }

    public function fieldValueFields()
    {
        return array('id', 'name');
    }
}
