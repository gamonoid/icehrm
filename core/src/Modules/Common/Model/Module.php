<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

namespace Modules\Common\Model;

use Classes\ModuleAccess;
use Classes\ModuleAccessService;
use Model\BaseModel;

class Module extends BaseModel
{
    public $table = 'Modules';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserModules()
    {
        $moduleList = [];
        $modules = ModuleAccessService::getInstance()->getModules();
        foreach ($modules as $md) {
            $md->name = sprintf('[%s] %s => %s', $md->mod_group, $md->menu, $md->label);
            $moduleList[] = $md;
        }

        return $moduleList;
    }

    public function fieldValueMethods()
    {
        return ['getUserModules'];
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('modules', 'admin'),
        ];
    }
}
