<?php

namespace Model;

use Classes\ModuleAccess;

/**
 * One emoji reaction by one person on one target row (see the Reactions migration).
 *
 * Writes go through Reactions\Rest\ReactionRestEndPoint, which only ever inserts or
 * deletes the CALLER's own rows. These ACLs are the defence in depth for the generic
 * service.php paths: everyone may read, and a person may add/delete only their own
 * (getUserOnlyMeAccessField = employee).
 */
class Reaction extends BaseModel
{
    public $table = 'Reactions';

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
        return array("get", "element", "add", "delete");
    }

    public function getUserOnlyMeAccessField()
    {
        return "employee";
    }

    /**
     * Reactions belong to whatever they decorate, not to a module of their own. The
     * always-present employees module is declared so the module-role grant has a
     * home for this model without inventing a module.
     */
    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'user'),
            new ModuleAccess('employees', 'admin'),
        ];
    }
}
