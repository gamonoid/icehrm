<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

namespace Users\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class UserRole extends BaseModel
{
    public function getAdminAccess()
    {
        return array('get','element','add','save','delete');
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('users', 'admin'),
        ];
    }

    public $table = 'UserRoles';
    /**
     * No module grants Manager access to this model (module meta.json user_levels),
     * so no manager-facing screen reads it. The inherited BaseModel default
     * would expose the whole table on the generic service.php path.
     */
    public function getManagerAccess()
    {
        return array();
    }

    /**
     * No module grants Employee access to this model (module meta.json user_levels),
     * so no employee-facing screen reads it. The inherited BaseModel default
     * would expose the whole table on the generic service.php path.
     */
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
        return array('id', 'name');
    }

}
