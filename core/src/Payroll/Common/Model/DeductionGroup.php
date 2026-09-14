<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:16 PM
 */

namespace Payroll\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class DeductionGroup extends BaseModel
{
    public $table = 'DeductionGroup';

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


    public function getModuleAccess()
    {
        return [
            new ModuleAccess('payroll', 'admin'),
        ];
    }
}
