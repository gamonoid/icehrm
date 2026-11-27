<?php

namespace KudosUser\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * One piece of recognition: sender gave recipient kudos, with a message, an optional
 * company value it exemplifies (value_tag names a KudosValue) and an audience that
 * decides who else sees it in the feed (all | department | team). Both sender and
 * recipient are Employees ids — a user without a profile cannot give kudos.
 *
 * Reads/writes go through the REST endpoints in ApiController, which scope the feed
 * by audience and let only the author or an admin delete. These ACLs cover the
 * generic service.php paths: everyone may read; a person may add and delete their
 * own (getUserOnlyMeAccessField = sender); admins may do anything.
 */
class Kudos extends BaseModel
{
    public $table = 'KudosPosts';

    const AUDIENCES = array('all', 'department', 'team');

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "add");
    }

    public function getUserAccess()
    {
        return array("get", "element", "add");
    }

    public function getUserOnlyMeAccess()
    {
        return array("get", "element", "add", "delete");
    }

    public function getUserOnlyMeAccessField()
    {
        return "sender";
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('kudos', 'user'),
        ];
    }
}
