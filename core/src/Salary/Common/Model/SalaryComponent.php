<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 11:07 PM
 */

namespace Salary\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class SalaryComponent extends BaseModel
{
    public $table = 'SalaryComponent';

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
            new ModuleAccess('salary', 'admin'),
        ];
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
