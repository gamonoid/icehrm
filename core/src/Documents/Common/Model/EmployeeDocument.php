<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:26 AM
 */

namespace Documents\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeDocument extends BaseModel
{
    public $table = 'EmployeeDocuments';

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
    // @codingStandardsIgnoreStart
    public function Insert()
    {
        // @codingStandardsIgnoreEnd
        if (empty($this->date_added)) {
            $this->date_added = date("Y-m-d H:i:s");
        }
        return parent::Insert();
    }

    public function executePreSaveActions($obj)
    {
        $obj->expire_notification_last = -1;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        $obj->expire_notification_last = -1;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }
}
