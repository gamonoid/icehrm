<?php
namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Offices and sites an employee can be based at (Company Structure → Work Locations).
 */
class WorkLocation extends BaseModel
{
    public $table = 'WorkLocations';

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
        return array("get", "element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('company_structure', 'admin'),
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    public function fieldValueFields()
    {
        return array('id', 'name');
    }
}
