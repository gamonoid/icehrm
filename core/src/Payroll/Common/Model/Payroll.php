<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:14 PM
 */

namespace Payroll\Common\Model;

use Classes\BaseService;
use Classes\ModuleAccess;
use Model\BaseModel;

class Payroll extends BaseModel
{
    public $table = 'Payroll';
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
        return array("get","element");
    }

    public function getEmployeePayrolls()
    {
        $currentEmp = BaseService::getInstance()->getCurrentProfileId();
        $payrollIds = array();
        $payrollDataTemp = new PayrollData();

        // $payrollDataTemp->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        $rs = $payrollDataTemp->DB()->Execute(
            'select payroll from PayrollData where employee = ? group by payroll',
            array($currentEmp)
        );
        foreach ($rs as $rowId => $row) {
            $payrollIds[] = $row['payroll'];
        }
        $payroll = new Payroll();
        $payrolls = $payroll->Find("id in (".implode(",", $payrollIds).") and status = 'Completed'");
        return $payrolls;
    }

    public function fieldValueMethods()
    {
        return ['getEmployeePayrolls'];
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('payroll', 'admin'),
        ];
    }
}
