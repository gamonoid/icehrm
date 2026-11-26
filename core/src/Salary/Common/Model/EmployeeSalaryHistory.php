<?php
namespace Salary\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

/**
 * Append-only log of salary changes (Payroll → Salary → Salary History).
 *
 * Rows are written by EmployeeSalary::Save()/Delete(); they are never edited through
 * the UI or API. Payroll keeps reading EmployeeSalary only, so this table can never
 * be double-counted.
 */
class EmployeeSalaryHistory extends BaseModel
{
    public $table = 'EmployeeSalaryHistory';

    public function getAdminAccess()
    {
        return array("get", "element");
    }

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

    /**
     * Record one salary change.
     *
     * @param EmployeeSalary      $salary   the row after the change (or before, for a delete)
     * @param float|null          $oldAmount
     * @param float|null          $newAmount
     * @param string|null         $effectiveDate Y-m-d, defaults to today
     * @param string|null         $reason
     */
    public static function record($salary, $oldAmount, $newAmount, $effectiveDate, $reason)
    {
        $history = new EmployeeSalaryHistory();
        $history->employee = $salary->employee;
        $history->salary = $salary->id;
        $history->component = $salary->component;
        $history->old_amount = $oldAmount;
        $history->new_amount = $newAmount;
        $history->currency = empty($salary->currency) ? null : $salary->currency;
        $history->pay_frequency = empty($salary->pay_frequency) ? null : $salary->pay_frequency;
        $history->effective_date = self::validDate($effectiveDate) ? $effectiveDate : date('Y-m-d');
        $history->change_reason = empty($reason) ? 'Adjustment' : substr((string) $reason, 0, 30);

        $user = BaseService::getInstance()->getCurrentUser();
        $history->changed_by = empty($user) || empty($user->id) ? null : $user->id;
        $history->created = date('Y-m-d H:i:s');

        return $history->Save();
    }

    private static function validDate($value)
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
