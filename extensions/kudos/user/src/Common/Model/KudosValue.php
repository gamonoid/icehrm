<?php

namespace KudosUser\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * A company value a kudos can be tagged with ("Teamwork", "Ownership", …). Seeded
 * by the migration; admins manage the list, everyone reads it for the composer.
 */
class KudosValue extends BaseModel
{
    public $table = 'KudosValues';

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element");
    }

    public function getUserAccess()
    {
        return array("get", "element");
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('kudos', 'user'),
        ];
    }
}
