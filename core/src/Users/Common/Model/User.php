<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
namespace Users\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\ModuleAccess;
use Classes\ModuleAccessService;
use Classes\PermissionManager;
use Employees\Common\Model\Employee;
use Model\BaseModel;
use Classes\IceResponse;

class User extends BaseModel
{
    public $table = 'Users';

    // `image` is computed (profile picture, resolved from the linked employee) —
    // not a Users column. Declaring it virtual keeps it out of search WHERE
    // clauses (searching the users list 500'd with "Unknown column 'image'").
    public function getVirtualFields()
    {
        return array(
            "image"
        );
    }

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
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

        // Username is UNIQUE in the DB (unique index on Users.username). Catch a
        // duplicate here with a clear message instead of letting the INSERT/UPDATE
        // fail with a generic "Duplicate entry" error. Covers both create and edit.
        if (empty($obj->id)) {
            $sameUsername = $userTemp->Find("username = ?", array($obj->username));
        } else {
            $sameUsername = $userTemp->Find("username = ? and id <> ?", array($obj->username, $obj->id));
        }
        if (count($sameUsername) > 0) {
            return new IceResponse(IceResponse::ERROR, "A user with the same username already exists");
        }

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
                $adminUsers = $userTemp->Find("user_level = ?", array('Admin'));
                if (count($adminUsers) == 1 && $adminUsers[0]->id == $obj->id) {
                    return new IceResponse(
                        IceResponse::ERROR,
                        'You are not allowed to revoke your admin rights'
                    );
                }
            }
        }

        if (PermissionManager::isRestrictedUserLevel($obj->user_level) && empty($obj->default_module)) {
            return new IceResponse(
                IceResponse::ERROR,
                'Restricted users must always have a default module'
            );
        }

        if (!empty($obj->default_module)
            && !ModuleAccessService::getInstance()->isModuleEnabledForUser($obj->default_module, $obj)
        ) {
            return new IceResponse(
                IceResponse::ERROR,
                'Selected default module is not allowed for the user'
            );
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('users', 'admin'),
        ];
    }

    public function postProcessGetData($obj)
    {
        $obj = BaseService::getInstance()->cleanUpUser($obj);
        
        // Add employee profile image if user has an employee associated
        if (!empty($obj->employee)) {
            $employee = new Employee();
            $employee->Load('id = ?', [$obj->employee]);
            if ($employee->id) {
                $employee = FileService::getInstance()->updateSmallProfileImage($employee);
                $obj->image = $employee->image;
            }
        }
        
        return $obj;
    }

    public function postProcessGetElement($obj)
    {
        return BaseService::getInstance()->cleanUpUser($obj);
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'username');
    }

}
