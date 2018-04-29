<?php
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */
namespace Users\Common\Model;

use Classes\BaseService;
use Model\BaseModel;
use Classes\IceResponse;
use Modules\Common\Model\Module;

class User extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
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

        //Check if the user have rights to the default module
        if (!empty($obj->default_module)) {
            $module = new Module();
            $module->Load("id = ?", array($obj->default_module));
            if ($module->mod_group == "user") {
                $module->mod_group = "modules";
            }
            $moduleManager = BaseService::getInstance()->getModuleManager($module->mod_group, $module->name);
            if (!BaseService::getInstance()->isModuleAllowedForGivenUser($moduleManager, $obj)) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "This module can not be set as the default module for 
                    the user since the user do not have access to this module"
                );
            }
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public $table = 'Users';
}
