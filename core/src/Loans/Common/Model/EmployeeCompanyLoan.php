<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:56 PM
 */

namespace Loans\Common\Model;

use Classes\ModuleAccess;
use Classes\IceResponse;
use Loans\Common\Model\EmployeeCompanyLoan;
use Model\BaseModel;
use Classes\BaseService;

class EmployeeCompanyLoan extends BaseModel
{
    public $table = 'EmployeeCompanyLoans';

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
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('loans', 'admin'),
            new ModuleAccess('loans', 'user'),
        ];
    }

    public function executePreSaveActions($obj){
            $start = $obj->start_date;
            $month = $obj->period_months;
            $calculation = date("Y-m-d", strtotime("+$month month", strtotime($start)));
            $obj->last_installment_date = $calculation;
            return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        return $this->executePreSaveActions();
    }
}
