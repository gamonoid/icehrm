<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:02 PM
 */

namespace Metadata\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Province extends BaseModel
{
    public $table = 'Province';

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getAnonymousAccess()
    {
        return array("get", "element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('metadata', 'admin'),
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
