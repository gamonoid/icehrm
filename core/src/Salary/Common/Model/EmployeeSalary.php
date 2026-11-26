<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 11:11 PM
 */

namespace Salary\Common\Model;

use Classes\FileService;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class EmployeeSalary extends BaseModel
{
    public $table = 'EmployeeSalary';

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

    public function getUserOnlyMeSwitchedAccess()
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
     * Effective date and reason for the salary history row written on save or delete.
     * They are not columns: the admin form and the payroll_config API set them, and
     * getObjectKeys() lets the generic save copy them from the request.
     */
    public $history_effective_date = null;
    public $history_reason = null;

    public function getObjectKeys()
    {
        $keys = parent::getObjectKeys();
        $keys['history_effective_date'] = 'history_effective_date';
        $keys['history_reason'] = 'history_reason';
        return $keys;
    }

    /**
     * Every route that changes a salary line (admin Salary tab, payroll_config REST,
     * demo data) goes through Save()/Delete(), so history is recorded here rather
     * than in the generic save hooks, which the REST API skips. Backup restore writes
     * rows with raw SQL and is intentionally not recorded.
     */
    public function Save()
    {
        $oldAmount = null;
        $isInsert = empty($this->id);
        if (!$isInsert) {
            $stored = new EmployeeSalary();
            $stored->Load('id = ?', array($this->id));
            if (!empty($stored->id)) {
                $oldAmount = $stored->amount;
            }
        }

        $ok = parent::Save();

        if ($ok && ($isInsert || (float) $oldAmount !== (float) $this->amount)) {
            EmployeeSalaryHistory::record(
                $this,
                $oldAmount,
                $this->amount,
                $this->history_effective_date,
                $this->history_reason ? $this->history_reason : ($isInsert ? 'Added' : 'Adjustment')
            );
        }

        return $ok;
    }

    public function Delete()
    {
        $snapshot = clone $this;
        $ok = parent::Delete();
        if ($ok && !empty($snapshot->id)) {
            EmployeeSalaryHistory::record(
                $snapshot,
                $snapshot->amount,
                null,
                $snapshot->history_effective_date,
                'Removed'
            );
        }
        return $ok;
    }

    public function postProcessGetData($obj)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$obj->employee]);
        if ($employee->id) {
            $employee = FileService::getInstance()->updateSmallProfileImage($employee);
            $obj->image = $employee->image;
        }

        return $obj;
    }
}
