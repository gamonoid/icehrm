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
        return array("get","element","add","save","delete");
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

    /**
     * No module grants Manager access to this model (module meta.json user_levels),
     * so no manager-facing screen reads it. The inherited BaseModel default would
     * expose the whole table on the generic service.php path.
     */
    public function getManagerAccess()
    {
        return array();
    }

    /**
     * Only-me is meaningless here: Modules rows are not owned by an employee.
     */
    public function getUserOnlyMeAccess()
    {
        return array();
    }

}
