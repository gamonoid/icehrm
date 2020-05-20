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
        return array('get','element','save','delete');
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
}
