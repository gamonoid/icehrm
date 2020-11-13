<?php

namespace Tasks\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Task extends BaseModel
{
    public $table = 'Tasks';

    public function getAdminAccess()
    {
        return ["get","element","save","delete"];
    }

    public function getManagerAccess()
    {
        return ["get","element"];
    }

    public function getUserAccess()
    {
        return [];
    }

    public function getAnonymousAccess()
    {
        return [];
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('tasks'),
        ];
    }
}