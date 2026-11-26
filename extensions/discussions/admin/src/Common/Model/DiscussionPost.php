<?php

namespace DiscussionsAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * A top-level message in a channel. content is the EditorJS document (JSON);
 * attachments is a JSON list of {name, filename, original, size_text} pointing at
 * Files rows. author_user is the Users id; author_employee the Employees id when the
 * user has a profile (admins may not).
 */
class DiscussionPost extends BaseModel
{
    public $table = 'DiscussionPosts';

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
