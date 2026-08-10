<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:40 AM
 */

namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmploymentStatus extends BaseModel
{

    public $table = 'EmploymentStatus';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
