<?php
namespace Employees\Services;

use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeCareer;

/**
 * Keeps Work History (EmployeeCareer) and the employee record in step.
 *
 * - Changing a tracked field on the employee closes the open Work History row and
 *   opens a new one starting today.
 * - Adding a Work History row that starts today or earlier, and is the latest one,
 *   updates the employee's current values.
 *
 * Both directions write with Save() directly, which does not run the generic save
 * hooks, so the two never trigger each other.
 */
class WorkHistoryService
{
    /** Employee fields mirrored on Work History rows, with their labels. */
    private static $trackedFields = array(
        'job_title' => 'Job Title',
        'department' => 'Department',
        'supervisor' => 'Manager',
        'employment_status' => 'Employment Status',
        'employment_type' => 'Employment Type',
        'pay_grade' => 'Pay Grade',
        'work_location' => 'Work Location',
    );

    public static function getTrackedFields()
    {
        return array_keys(self::$trackedFields);
    }

    /**
     * Called after a new employee is created.
     */
    public function recordHire(Employee $employee)
    {
        $existing = new EmployeeCareer();
        $rows = $existing->Find('employee = ?', array($employee->id));
        if (!empty($rows)) {
            return;
        }

        $start = $this->validDate($employee->joined_date) ? $employee->joined_date : date('Y-m-d');
        $this->insertRow($employee, $start, 'Hire', 'Created from the employee record');
    }

    /**
     * Called after an employee is updated, with the record as it was before the update.
     */
    public function recordChange($before, Employee $after)
    {
        if (empty($before) || empty($before->id)) {
            return;
        }

        $changed = array();
        foreach (self::$trackedFields as $field => $label) {
            if ((string) $before->$field !== (string) $after->$field) {
                $changed[] = $label;
            }
        }
        if (empty($changed)) {
            return;
        }

        $today = date('Y-m-d');
        $details = 'Updated from the employee record: ' . implode(', ', $changed);
        $open = $this->getOpenRow($after->id);

        if (!empty($open) && $open->date_start >= $today) {
            // The open row only started today: update it rather than adding a
            // zero-length row.
            $this->copyEmployeeFields($after, $open);
            $open->details = $details;
            $open->updated = date('Y-m-d H:i:s');
            $open->Save();
            return;
        }

        if (!empty($open)) {
            $open->date_end = date('Y-m-d', strtotime($today . ' -1 day'));
            $open->updated = date('Y-m-d H:i:s');
            $open->Save();
        }

        $this->insertRow($after, $today, $this->guessReason($before, $after), $details);
    }

    /**
     * Called after a Work History row is added or edited. If it is the current row
     * (no end date, starts today or earlier, latest start), close older open rows and
     * copy its values to the employee record.
     */
    public function applyToEmployee(EmployeeCareer $career)
    {
        if (!empty($career->date_end) || !$this->validDate($career->date_start)
            || $career->date_start > date('Y-m-d')
        ) {
            return;
        }

        $latest = new EmployeeCareer();
        $later = $latest->Find(
            'employee = ? AND id <> ? AND date_start > ?',
            array($career->employee, $career->id, $career->date_start)
        );
        if (!empty($later)) {
            return; // a later row exists, so this one is history, not the current job
        }

        $openRows = new EmployeeCareer();
        $others = $openRows->Find(
            'employee = ? AND id <> ? AND date_end IS NULL',
            array($career->employee, $career->id)
        );
        $endDate = date('Y-m-d', strtotime($career->date_start . ' -1 day'));
        foreach ($others as $other) {
            $other->date_end = $other->date_start > $endDate ? $other->date_start : $endDate;
            $other->updated = date('Y-m-d H:i:s');
            $other->Save();
        }

        $employee = new Employee();
        $employee->Load('id = ?', array($career->employee));
        if (empty($employee->id)) {
            return;
        }

        $changed = false;
        foreach (self::getTrackedFields() as $field) {
            if (!empty($career->$field) && (string) $employee->$field !== (string) $career->$field) {
                $employee->$field = $career->$field;
                $changed = true;
            }
        }
        if ($changed) {
            $employee->Save();
        }
    }

    private function getOpenRow($employeeId)
    {
        $career = new EmployeeCareer();
        $rows = $career->Find(
            'employee = ? AND date_end IS NULL ORDER BY date_start DESC, id DESC LIMIT 1',
            array($employeeId)
        );
        return empty($rows) ? null : $rows[0];
    }

    private function insertRow(Employee $employee, $start, $reason, $details)
    {
        $row = new EmployeeCareer();
        $row->employee = $employee->id;
        $this->copyEmployeeFields($employee, $row);
        $row->date_start = $start;
        $row->date_end = null;
        $row->change_reason = $reason;
        $row->details = $details;
        $row->created = date('Y-m-d H:i:s');
        $row->updated = date('Y-m-d H:i:s');
        $row->Save();
    }

    private function copyEmployeeFields($employee, $row)
    {
        foreach (self::getTrackedFields() as $field) {
            $row->$field = empty($employee->$field) ? null : $employee->$field;
        }
    }

    private function guessReason($before, $after)
    {
        if ((string) $before->department !== (string) $after->department
            || (string) $before->work_location !== (string) $after->work_location
        ) {
            return 'Transfer';
        }
        if ((string) $before->employment_status !== (string) $after->employment_status
            || (string) $before->employment_type !== (string) $after->employment_type
        ) {
            return 'Contract change';
        }
        return 'Other';
    }

    private function validDate($value)
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && $value !== '0000-00-00';
    }
}
