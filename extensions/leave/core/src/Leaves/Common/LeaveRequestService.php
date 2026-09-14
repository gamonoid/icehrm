<?php

namespace Leaves\Common;

use Classes\FileService;
use Employees\Common\Model\Employee;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;

class LeaveRequestService
{

    protected $employeeDataList = [];
    public function getLeaveDays($start, $end)
    {

        [$leaveDays, $leavesById] = $this->findLeavesAndLeaveDays($start, $end);

        $enrichedLeaveDays = [];
        foreach ($leaveDays as $leaveDay) {
            $enrichedLeaveDay = $this->enrichLeaveDay($leaveDay, $leavesById);
            if (empty($enrichedLeaveDay) || empty($enrichedLeaveDay['employee'])) {
                continue;
            }
            if (isset($enrichedLeaveDays[$enrichedLeaveDay['date']])) {
                $enrichedLeaveDays[$enrichedLeaveDay['date']][] = $enrichedLeaveDay;
            } else {
                $enrichedLeaveDays[$enrichedLeaveDay['date']] = [$enrichedLeaveDay];
            }
        }

        return $enrichedLeaveDays;
    }

    public function getYearlyLeaveDays($year)
    {
        $start = $year.'-01-01';
        $end = $year.'-12-31';
        [$leaveDays, $leavesById] = $this->findLeavesAndLeaveDays($start, $end);

        $months = [];
        foreach ($leaveDays as $leaveDay) {
            $employeeId = $leavesById[$leaveDay->employee_leave]->employee;
            $employee = $this->getEmployeeData($employeeId);
            if (null === $employee) {
                continue;
            }

            $month = date('Y-m', strtotime($leaveDay->leave_date));
            if (!isset($months[$month])) {
                $months[$month] = [];
            }

            if (!isset($months[$month][$employeeId])) {
                $months[$month][$employeeId] = [
                    'employee' => $employee,
                    'count' => 1
                ];
            } else {
                $months[$month][$employeeId]['count']++;
            }
        }

        foreach ($months as $month => $employees) {
            $months[$month] = array_values($employees);
        }

        return $months;
    }

    protected function findLeavesAndLeaveDays($start, $end)
    {
        $employeeLeave = new EmployeeLeave();

        $leaves = $employeeLeave->Find(
            "status in ('Pending', 'Approved') and ((date_start >= ? and date_start <= ? ) or (date_end >= ? and date_end <= ? ))",
            array($start, $end, $start, $end)
        );

        if (empty($leaves)) {
            return [];
        }

        $leavesById = [];
        foreach ($leaves as $leave) {
            $leavesById[$leave->id] = $leave;
        }

        $leaveDay = new EmployeeLeaveDay();
        $leaveDays = $leaveDay->Find(
            "employee_leave in (".implode(',', array_map(
                function ($leave) {
                    return $leave->id;
                },
                $leaves
            )).")"
        );

        return [$leaveDays, $leavesById];
    }

    protected function enrichLeaveDay($leaveDay, $leavesById)
    {
        return [
           'id' => $leaveDay->id,
           'leave_type' => $leavesById[$leaveDay->employee_leave]->leave_type,
           'status' => $leavesById[$leaveDay->employee_leave]->status,
           'employee' => $this->getEmployeeData($leavesById[$leaveDay->employee_leave]->employee),
           'date' => $leaveDay->leave_date,
           'hours' => $leaveDay->leave_type,
        ];
    }

    protected function getEmployeeData($employeeId)
    {
        if (isset($this->employeeDataList[$employeeId])) {
            return $this->employeeDataList[$employeeId];
        }

        $employee = new Employee();
        $employee->Load('id = ?', [$employeeId]);
        if (empty($employee->id) || $employee->status !== 'Active') {
            return null;
        }

        $fs = FileService::getInstance();
        $employee = $fs->updateSmallProfileImage($employee);

        $this->employeeDataList[$employeeId] = [
            'id' => $employee->id,
            'name' => $employee->first_name.' '.$employee->last_name,
            'image' => $employee->image,
        ];

        return $this->employeeDataList[$employeeId];
    }
}
