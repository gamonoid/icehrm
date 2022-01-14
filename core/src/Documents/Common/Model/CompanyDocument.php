<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:25 AM
 */

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class CompanyDocument extends BaseModel
{
    public $table = 'CompanyDocuments';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }

    public function getFinder()
    {
        return new CompanyDocumentFinderProxy();
    }

    public function executePreSaveActions($obj)
    {
        $obj->expire_notification_last = -1;
        if (empty($obj->visible_to)) {
            $obj->visible_to = 'Owner';
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        $obj->expire_notification_last = -1;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
