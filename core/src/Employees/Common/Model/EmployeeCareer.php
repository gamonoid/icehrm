<?php
namespace Employees\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Services\WorkHistoryService;
use Model\BaseModel;

class EmployeeCareer extends BaseModel
{
    public $table = 'EmployeeCareer';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array();
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
        // Optional lookups (integer foreign keys): a blank value means "not set".
        foreach (array('work_location', 'pay_grade') as $field) {
            if (isset($obj->$field) && (trim((string) $obj->$field) === '' || $obj->$field === 'NULL')) {
                $obj->$field = null;
            }
        }

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

    public function executePostSaveActions($obj)
    {
        (new WorkHistoryService())->applyToEmployee($obj);
    }

    public function executePostUpdateActions($obj)
    {
        (new WorkHistoryService())->applyToEmployee($obj);
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
