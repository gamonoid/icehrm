<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
namespace Users\Common\Model;

use Classes\BaseService;
use Model\BaseModel;
use Classes\IceResponse;

class User extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function validateSave($obj)
    {
        $userTemp = new User();

        if (empty($obj->id)) {
            $users = $userTemp->Find("email = ?", array($obj->email));
            if (count($users) > 0) {
                return new IceResponse(IceResponse::ERROR, "A user with same authentication email already exist");
            }
        } else {
            $users = $userTemp->Find("email = ? and id <> ?", array($obj->email, $obj->id));
            if (count($users) > 0) {
                return new IceResponse(IceResponse::ERROR, "A user with same authentication email already exist");
            }

            //Check if you are trying to change user level
            $oldUser = new User();
            $oldUser->Load("id = ?", array($obj->id));
            if ($oldUser->user_level != $obj->user_level && $oldUser->user_level == 'Admin') {
                $adminUsers = $userTemp->Find("user_level = ?", array("Admin"));
                if (count($adminUsers) == 1 && $adminUsers[0]->id == $obj->id) {
                    return new IceResponse(
                        IceResponse::ERROR,
                        "You are the only admin user for the application.
                        You are not allowed to revoke your admin rights"
                    );
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public $table = 'Users';
}
