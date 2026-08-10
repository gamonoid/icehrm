<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:03 AM
 */

namespace Travel\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class ImmigrationDocument extends BaseModel
{
    public $table = 'ImmigrationDocuments';

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
        return array("get","element");
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
