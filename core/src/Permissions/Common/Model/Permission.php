<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:27 PM
 */

namespace Permissions\Common\Model;

use Model\BaseModel;

class Permission extends BaseModel
{
    public $table = 'Permissions';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }
}
