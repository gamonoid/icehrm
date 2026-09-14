<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 12/20/24
 * Time: 10:00 AM
 */

namespace Travel\Common\Model;

use Model\BaseModel;

class TravelProject extends BaseModel
{
    public $table = 'TravelProjects';

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('code', 'name');
    }

}
