<?php
namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Reasons recorded when an employee leaves (Job Details Setup → Termination Reasons).
 */
class TerminationReason extends BaseModel
{
    public $table = 'TerminationReasons';

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('jobs', 'admin'),
            new ModuleAccess('employees', 'admin'),
        ];
    }

    public function fieldValueFields()
    {
        return array('id', 'name');
    }
}
