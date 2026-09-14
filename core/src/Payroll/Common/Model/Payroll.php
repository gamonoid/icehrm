<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:14 PM
 */

namespace Payroll\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class Payroll extends BaseModel
{
    public $table = 'Payroll';
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    // Payroll is Admin-only. All three must be empty: getUserOnlyMeAccess() is what
    // checkSecureAccess()'s request-field path tests, so leaving it non-empty lets a
    // caller authorise themselves by passing their own employee id. Employees still get
    // their own payslips - PayslipDocument is a separate model and PayslipReport reads
    // these tables through the ORM directly, which never calls checkSecureAccess().
    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
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
        if (empty($payrollIds)) {
            return [];
        }
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

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreSaveActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = 'Draft';
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
