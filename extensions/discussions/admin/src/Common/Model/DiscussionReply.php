<?php

namespace DiscussionsAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * A plain-text reply. parent is null for a reply to the post itself, or the id of a
 * top-level reply for the second (and last) level.
 */
class DiscussionReply extends BaseModel
{
    public $table = 'DiscussionReplies';

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
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
            new ModuleAccess('discussions', 'admin'),
        ];
    }
}
