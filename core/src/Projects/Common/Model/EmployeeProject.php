<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:58 PM
 */

namespace Projects\Common\Model;

use Classes\IceResponse;
use Model\BaseModel;

class EmployeeProject extends BaseModel
{
    public $table = 'EmployeeProjects';

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
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function executePreSaveActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = "Current";
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = "Current";
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
