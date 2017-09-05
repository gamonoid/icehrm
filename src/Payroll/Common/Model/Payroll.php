<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:14 PM
 */

namespace Payroll\Common\Model;

use Classes\BaseService;
use Model\BaseModel;

class Payroll extends BaseModel
{
    var $_table = 'Payroll';
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

        $payrollDataTemp = new PayrollData();
        $payrollData = $payrollDataTemp->Find("employee = ? group by payroll", array($currentEmp));
        $payrollIds = array();
        foreach ($payrollData as $pd) {
            $payrollIds[] = $pd->payroll;
        }

        $payroll = new Payroll();
        $payrolls = $payroll->Find("id in (".implode(",", $payrollIds).") and status = 'Completed'");
        return $payrolls;
    }
}
