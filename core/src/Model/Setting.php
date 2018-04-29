<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:33 AM
 */

namespace Model;

use Classes\BaseService;
use Classes\RestApiManager;
use Users\Common\Model\User;

class Setting extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function postProcessGetElement($obj)
    {
        if ($obj->name == 'Api: REST Api Token') {
            $user = BaseService::getInstance()->getCurrentUser();
            $dbUser = new User();
            $dbUser->Load("id = ?", array($user->id));
            $resp = RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
            $obj->value = $resp->getData();
        }
        return $obj;
    }

    public $table = 'Settings';
}
