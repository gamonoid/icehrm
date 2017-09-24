<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:53 PM
 */

namespace Projects\Common\Model;

use Model\BaseModel;

class Client extends BaseModel
{
    public $table = 'Clients';
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }
}
