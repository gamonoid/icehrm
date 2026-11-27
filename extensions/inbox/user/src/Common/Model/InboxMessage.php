<?php

namespace InboxUser\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Private to the two participants. NO level — not even Admin — may reach these rows
 * through the generic service.php path; every read and write goes through the
 * extension's own REST endpoints, which check participation on each call.
 */
class InboxMessage extends BaseModel
{
    public $table = 'InboxMessages';

    public function getAdminAccess()
    {
        return array();
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('inbox', 'user'),
        ];
    }
}
