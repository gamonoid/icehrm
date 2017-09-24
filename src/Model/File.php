<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:32 AM
 */

namespace Model;

class File extends BaseModel
{
    public $table = 'Files';
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getAnonymousAccess()
    {
        return array("save");
    }
}
