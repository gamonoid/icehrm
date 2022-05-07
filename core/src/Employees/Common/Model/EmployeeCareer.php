<?php
namespace Employees\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeCareer extends BaseModel
{
    public $table = 'EmployeeCareer';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    protected function validate($obj)
    {
        if (!empty($obj->date_end) && strtotime($obj->date_end) < strtotime($obj->date_start)) {
            return new IceResponse(IceResponse::ERROR, 'Start date should be earlier than end date');
        }

        return new IceResponse(IceResponse::SUCCESS);
    }

    public function executePreUpdateActions($obj)
    {
        $validation = $this->validate($obj);

        if ($validation->getStatus() !== IceResponse::SUCCESS) {
            return $validation;
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreSaveActions($obj)
    {
        $validation = $this->validate($obj);

        if ($validation->getStatus() !== IceResponse::SUCCESS) {
            return $validation;
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
