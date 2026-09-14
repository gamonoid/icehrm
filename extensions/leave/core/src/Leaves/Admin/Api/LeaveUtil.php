<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:25 PM
 */

namespace Leaves\Admin\Api;

use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\HoliDay;
use Leaves\Common\Model\LeaveGroupEmployee;
use Leaves\Common\Model\LeavePeriod;
use Leaves\Common\Model\LeaveRule;
use Leaves\Common\Model\LeaveStartingBalance;
use Leaves\Common\Model\LeaveType;
use Model\Setting;
use Utils\CalendarTools;
use Utils\LogManager;

class LeaveUtil
{

    public static function getLeaveTime($leaveTimeType)
    {
        $amount = 0;
        if ($leaveTimeType == 'Full Day') {
            $amount = 1;
        } elseif ($leaveTimeType == 'Half Day') {
            $amount = 0.5;
        } elseif ($leaveTimeType == 'Half Day - Morning') {
            $amount = 0.5;
        } elseif ($leaveTimeType == 'Half Day - Afternoon') {
            $amount = 0.5;
        } elseif ($leaveTimeType == '1 Hour - Morning') {
            $amount = 0.125;
        } elseif ($leaveTimeType == '2 Hours - Morning') {
            $amount = 0.25;
        } elseif ($leaveTimeType == '3 Hours - Morning') {
            $amount = 0.375;
        } elseif ($leaveTimeType == '1 Hour - Afternoon') {
            $amount = 0.125;
        } elseif ($leaveTimeType == '2 Hours - Afternoon') {
            $amount = 0.25;
        } elseif ($leaveTimeType == '3 Hours - Afternoon') {
            $amount = 0.375;
        }

        return $amount;
    }

    public static function getLeaveStartAndEndTimes($leaveTimeType)
    {
        if ($leaveTimeType == 'Full Day') {
            return ['00:00:00', '23:59:59'];
        } elseif ($leaveTimeType == 'Half Day') {
            return ['09:00:00', '13:30:00'];
        } elseif ($leaveTimeType == 'Half Day - Morning') {
            return ['09:00:00', '13:30:00'];
        } elseif ($leaveTimeType == 'Half Day - Afternoon') {
            return ['13:30:00', '18:00:00'];
        } elseif ($leaveTimeType == '1 Hour - Morning') {
            return ['09:00:00', '10:00:00'];
        } elseif ($leaveTimeType == '2 Hours - Morning') {
            return ['09:00:00', '11:00:00'];
        } elseif ($leaveTimeType == '3 Hours - Morning') {
            return ['09:00:00', '12:00:00'];
        } elseif ($leaveTimeType == '1 Hour - Afternoon') {
            return ['17:00:00', '18:00:00'];
        } elseif ($leaveTimeType == '2 Hours - Afternoon') {
            return ['16:00:00', '18:00:00'];
        } elseif ($leaveTimeType == '3 Hours - Afternoon') {
            return ['15:00:00', '18:00:00'];
        }

        return ['00:00:00', '23:59:59'];
    }

    public function getLeaveHours($employeeId, $startDate, $endDate, $leaveType = null)
    {

        if ($leaveType !== null) {
            $leaveTypeObj = new LeaveType();
            $leaveTypeObj->Load('name = ?', array($leaveType));
            if (empty($leaveTypeObj->id)) {
                return 0;
            }
        }

        $employeeLeave = new EmployeeLeave();
        if ($leaveType !== null) {
            $leaves = $employeeLeave->Find(
                "employee = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?)) and status = ? and leave_type = ?",
                array($employeeId, $startDate, $endDate, $startDate, $endDate, "Approved", $leaveTypeObj->id)
            );
        } else {
            $leaves = $employeeLeave->Find(
                "employee = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?)) and status = ?",
                array($employeeId, $startDate, $endDate, $startDate, $endDate, "Approved")
            );
        }

        $total = 0;
        foreach ($leaves as $leave) {
            $employeeLeaveDay = new EmployeeLeaveDay();
            $days = $employeeLeaveDay->Find("employee_leave = ?", array($leave->id));
            foreach ($days as $day) {
                if (strtotime($day->leave_date) >= strtotime($startDate)
                    && strtotime($day->leave_date) <= strtotime($endDate)
                ) {
                    $total += LeaveUtil::getLeaveTime($day->leave_type) * 8;
                }
            }
        }

        return $total;
    }

    public function getHolidayHours($employeeId, $startDate, $endDate)
    {

        $employee = new Employee();
        $employee->Load("id = ?", array($employeeId));

        $holidays = $this->getHolidays($startDate, $endDate, $employee->country, $employeeId);
        $time = 0;
        foreach ($holidays as $hd) {
            $time += $this->getLeaveTime($hd->status);
        }

        return $time * 8;
    }

    public function getHolidays($startDate, $endDate, $countryId = null, $employeeId = null)
    {
        $hd = new HoliDay();
		// Holidays allowed for all employees
        $allHolidays = $hd->Find(
            "dateh >= ?  and dateh <= ? and country IS NULL",
            array($startDate, $endDate)
        );

		// Holidays allowed by the employees' country
		$countryHolidays = [];
		if (!empty($countryId)) {
			$countryHolidays = $hd->Find(
				"dateh >= ?  and dateh <= ? and country = ?",
				array($startDate, $endDate, $countryId)
			);
		}

		// Holidays allowed by leave groups of the employee
		$leave_group_holidays = [];
		$leave_group_ids = LeaveUtil::getEmployeeLeaveGroups($employeeId);
		if (!empty($leave_group_ids)) {
			$leave_group_holidays = $hd->Find(
				'dateh >= ?  and dateh <= ? and leave_group in ('.implode(',',$leave_group_ids).')',
				array($startDate, $endDate)
			);
		}

//        LogManager::getInstance()->info("All holiday count:".count($allHolidays));
//        LogManager::getInstance()->info("Country holiday count:".count($countryHolidays));

        //merge holidays (priority to country)
        $holidays = array();
        foreach ($allHolidays as $hd) {
            $holidays[$hd->dateh] = $hd;
        }
        foreach ($countryHolidays as $hd) {
            $holidays[$hd->dateh] = $hd;
        }
		foreach ($leave_group_holidays as $hd) {
			$holidays[$hd->dateh] = $hd;
		}

        return array_values($holidays);
    }

    public static function getAllLeavePeriodsByLatest()
    {
        $leavePeriod = new LeavePeriod();
        return $leavePeriod->Find('1 = 1 order by date_end desc', array());
    }

    public static function getLastLeavePeriod()
    {
        $leavePeriod = new LeavePeriod();
        $leavePeriod->Load('1 = 1 order by date_end desc limit 1', array());
        if (!empty($leavePeriod->id)) {
            return $leavePeriod;
        }

        return null;
    }

    public static function countLeaveAmounts($leaves, $endDate = null)
    {
        $amount = 0;
        foreach ($leaves as $leave) {
            $empLeaveDay = new EmployeeLeaveDay();
            if ($endDate === null) {
                $leaveDays = $empLeaveDay->Find("employee_leave = ?", array($leave->id));
            } else {
                $leaveDays = $empLeaveDay->Find("employee_leave = ? and leave_date < ?", array($leave->id, $endDate));
            }

            $amount += self::countLeaveAmountByDays($leaveDays);
        }
        return round(floatval($amount), 3);
    }

    public static function countLeaveAmountByDays($leaveDays)
    {
        $amount = 0;
        foreach ($leaveDays as $leaveDay) {
            if ($leaveDay->leave_type == 'Full Day') {
                $amount += 1;
            } elseif ($leaveDay->leave_type == 'Half Day - Morning') {
                $amount += 0.5;
            } elseif ($leaveDay->leave_type == 'Half Day - Afternoon') {
                $amount += 0.5;
            } elseif ($leaveDay->leave_type == '1 Hour - Morning') {
                $amount += 0.125;
            } elseif ($leaveDay->leave_type == '2 Hours - Morning') {
                $amount += 0.25;
            } elseif ($leaveDay->leave_type == '3 Hours - Morning') {
                $amount += 0.375;
            } elseif ($leaveDay->leave_type == '1 Hour - Afternoon') {
                $amount += 0.125;
            } elseif ($leaveDay->leave_type == '2 Hours - Afternoon') {
                $amount += 0.25;
            } elseif ($leaveDay->leave_type == '3 Hours - Afternoon') {
                $amount += 0.375;
            }
        }

        return $amount;
    }

    public static function getEmployeeLeaves($employeeId, $leavePeriod, $leaveType, $status, $lastDate = null)
    {
        $employeeLeave = new EmployeeLeave();
        if ($lastDate === null) {
            $employeeLeaves = $employeeLeave->Find(
                "employee = ? and leave_period = ? and leave_type = ? and status = ?",
                array($employeeId,$leavePeriod,$leaveType,$status)
            );
        } else {
            $employeeLeaves = $employeeLeave->Find(
                "employee = ? and leave_period = ? and leave_type = ? and status = ? and date_start < ?",
                array($employeeId,$leavePeriod,$leaveType,$status, $lastDate)
            );
        }

        if (!$employeeLeaves) {
            LogManager::getInstance()->info($employeeLeave->ErrorMsg(), true);
        }

        return $employeeLeaves;
    }

    public static function getCurrentLeavePeriod($startDate, $endDate)
    {
        if (LeaveUtil::isCountryBasedLeavePeriodsEnabled()) {
            $countryId = Employee::getCurrentEmployeeCompanyStructureCountry();
            $leavePeriod = new LeavePeriod();
            $leavePeriod->Load(
                "date_start <= ? and date_end >= ? and country = ?",
                array($startDate,$endDate, $countryId)
            );
            if (empty($leavePeriod->id)) {
                return new IceResponse(IceResponse::ERROR, "Error in leave period");
            }
            return new IceResponse(IceResponse::SUCCESS, $leavePeriod);
        }

        $leavePeriod = new LeavePeriod();
        $leavePeriod->Load("date_start <= ? and date_end >= ?", array($startDate,$endDate));
        if (empty($leavePeriod->id)) {
            return new IceResponse(IceResponse::ERROR, "Error in leave period");
        }
        return new IceResponse(IceResponse::SUCCESS, $leavePeriod);
    }

    public static function getAdditionalLeaveBalance($employeeId, $leaveType, $leavePeriodId)
    {
        //Get additional leaves for this leave type and period
        $statingLeaveBalance = new LeaveStartingBalance();
        $list = $statingLeaveBalance->Find(
            "employee = ? and leave_type = ? and leave_period = ?",
            array($employeeId, $leaveType, $leavePeriodId)
        );

        $total = 0;
        foreach ($list as $obj) {
            $total += floatval($obj->amount);
        }

        return $total;
    }

    private static function filterLeaveRulesByExperience($rules, $experience)
    {
        $selectedRule = null;
        $experienceDifference = INF;
        /**
 * @var LeaveRule $rule 
*/
        foreach ($rules as $rule) {
            if ($rule->exp_days === null || $rule->exp_days == 0) {
                $selectedRule = ($selectedRule === null) ? $rule : $selectedRule;
            } elseif ($experience > $rule->exp_days) {
                if ($experienceDifference > $experience - $rule->exp_days) {
                    $selectedRule = $rule;
                    $experienceDifference = $experience - $rule->exp_days;
                }
            }
        }

        return $selectedRule;
    }

    public static function getLeaveRuleOnly($employee, $leaveType, $currentLeavePeriod, $withLeavePeriod)
    {
        $rule = null;
        $leaveRule = new LeaveRule();
        $leaveTypeObj = new LeaveType();
        $leaveTypeObj->Load("id = ?", array($leaveType));

        $joinedDate = LeaveUtil::getJoinedOrConfirmationDate($employee);

        $preQuery = 'leave_period is null and ';
        if ($withLeavePeriod) {
            $preQuery = sprintf('leave_period = %s and ', $currentLeavePeriod->id);
        }

        // Get years of experience
        $experience = 0;
        if ($joinedDate) {
            if ($leaveTypeObj->employee_leave_period === 'Yes') {
                $currentDayInLeavePeriod = date('Y-m-d');
                /**
                 * When this function is used to calculate leave amounts for current leave period
                 * experience days can be calculated based on today's date
                 *
                 * But when calculating this for the past leave periods the experience days should
                 * be based on the last day of that leave period
                 */
                if (strtotime($currentDayInLeavePeriod) > strtotime($currentLeavePeriod->date_end)) {
                    // For calculating experience days for past leave periods
                    $currentDayInLeavePeriod = $currentLeavePeriod->date_end;
                }
                $experience = CalendarTools::getNumberOfDaysBetweenDates(
                    $joinedDate,
                    $currentLeavePeriod->date_start
                );
            } else {
                $experience = CalendarTools::getNumberOfDaysBetweenDates(
                    $joinedDate,
                    $currentLeavePeriod->date_start
                );
            }
        }

        // Check whether this employee has a leave group
        $leaveGroupIds = self::getEmployeeLeaveGroups($employee->id);
        if (!empty($leaveGroupIds)) {
            // Target: Group | Type | Employee
            $rules = $leaveRule->Find(
                $preQuery."leave_group in (".implode(',', $leaveGroupIds).") and employee = ? and leave_type = ?",
                array($employee->id, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            // Target: Group | Type | JobTitle | Department
            $rules = $leaveRule->Find(
                $preQuery."leave_group in (".implode(',', $leaveGroupIds).") and job_title = ? 
                and department = ? and leave_type = ? and employee is null",
                array($employee->job_title, $employee->department, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            // Target: Group | Type | Department
            $rules = $leaveRule->Find(
                $preQuery.
                "leave_group in ("
                .implode(',', $leaveGroupIds)
                .") and department = ? and leave_type = ? and employee is null",
                array($employee->department, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            // Target: Group | Type | JobTitle | EmploymentStatus
            $rules = $leaveRule->Find(
                $preQuery."leave_group in (".implode(',', $leaveGroupIds).") and job_title = ? 
                and employment_status = ? and leave_type = ? and employee is null",
                array($employee->job_title, $employee->employment_status, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            // Target: Group | Type | JobTitle
            $rules = $leaveRule->Find(
                $preQuery."leave_group in (".implode(',', $leaveGroupIds).") 
                and job_title = ? and employment_status is null and leave_type = ? 
                and employee is null",
                array($employee->job_title, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }


            // Target: Group | Type | EmploymentStatus
            $rules = $leaveRule->Find(
                $preQuery.
                "leave_group in (".implode(',', $leaveGroupIds).") and job_title is null and employment_status = ? 
            and leave_type = ? and employee is null",
                array($employee->employment_status, $leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            // Target: Group | Type
            $rules = $leaveRule->Find(
                $preQuery."leave_group in (".implode(',', $leaveGroupIds).") and leave_type = ? and job_title is null 
            and employment_status is null and department is null and employee is null",
                array($leaveType)
            );
            $rule = self::filterLeaveRulesByExperience($rules, $experience);
            if ($rule !== null) {
                return $rule;
            }

            if (!$withLeavePeriod) {
                // Target: Select leave type itself
                $rules = $leaveTypeObj->Find(
                    "leave_group in (".implode(',', $leaveGroupIds).") and id = ?",
                    array($leaveType)
                );

                if (count($rules) > 0) {
                    return $rules[0];
                }
            }
        }

        // Target: Type | Employee
        // @codingStandardsIgnoreStart
        $rules = $leaveRule->Find($preQuery."leave_group is null and employee = ? and leave_type = ?", array($employee->id, $leaveType));
        // @codingStandardsIgnoreEnd
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        // Target: Type | JobTitle | Department
        // @codingStandardsIgnoreStart
        $rules = $leaveRule->Find(
            $preQuery."leave_group is null and job_title = ? and department = ? and leave_type = ? and employee is null",
            array($employee->job_title, $employee->department, $leaveType)
        );
        // @codingStandardsIgnoreEnd
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        // Target: Type | Department
        $rules = $leaveRule->Find(
            $preQuery."leave_group is null and department = ? and leave_type = ? and employee is null",
            array($employee->department, $leaveType)
        );
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        // Target: Type | JobTitle | EmploymentStatus
        // @codingStandardsIgnoreStart
        $rules = $leaveRule->Find(
            $preQuery."leave_group is null and job_title = ? and employment_status = ? and leave_type = ? and employee is null",
            array($employee->job_title, $employee->employment_status, $leaveType)
        );
        // @codingStandardsIgnoreEnd
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        // Target: Type | JobTitle
        // @codingStandardsIgnoreStart
        $rules = $leaveRule->Find(
            $preQuery."leave_group is null and job_title = ? and employment_status is null and leave_type = ? and employee is null",
            array($employee->job_title, $leaveType)
        );
        // @codingStandardsIgnoreEnd
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        // Target: Type | EmploymentStatus
        // @codingStandardsIgnoreStart
        $rules = $leaveRule->Find(
            $preQuery."leave_group is null and job_title is null and employment_status = ? and leave_type = ? and employee is null",
            array($employee->employment_status, $leaveType)
        );
        // @codingStandardsIgnoreEnd
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        $query = <<<QUERY
leave_type = ? 
and employee is null 
and job_title is null
and employment_status is null
and department is null
and leave_group is null
QUERY;
        $rules = $leaveRule->Find(
            $preQuery.$query,
            array($leaveType)
        );
        $rule = self::filterLeaveRulesByExperience($rules, $experience);
        if ($rule !== null) {
            return $rule;
        }

        if ($withLeavePeriod === false) {
            $rules = $leaveTypeObj->Find("id = ?", array($leaveType));

            if (count($rules) > 0) {
                return $rules[0];
            }
        }

        return null;
    }

    public static function getEmployeeLeaveGroups($employeeId)
    {
        $leaveGroupIds = [];
        $empLeaveGroup = new LeaveGroupEmployee();
        $empLeaveGroups = $empLeaveGroup->Find("employee = ?", array($employeeId));
        foreach ($empLeaveGroups as $empLeaveGroup) {
            $leaveGroupIds[] =  $empLeaveGroup->leave_group;
        }

        return $leaveGroupIds;
    }

    public static function getLeaveRule($employee, $leaveType, $leavePeriod)
    {
        $additionalLB = LeaveUtil::getAdditionalLeaveBalance($employee->id, $leaveType, $leavePeriod->id);
        // Check if any leave rule defined targeting current leave period and give priority to it
        $rule = LeaveUtil::getLeaveRuleOnly(
            $employee,
            $leaveType,
            $leavePeriod,
            true
        );
        if ($rule === null) {
            $rule = LeaveUtil::getLeaveRuleOnly($employee, $leaveType, $leavePeriod, false);
        }

        $rule->default_per_year = floatval($rule->default_per_year);
        $rule->pto = floatval($additionalLB);

        return $rule;
    }

    public static function getNumberOfLeaveForFromLeavePeriod(
        $employee,
        $currentLeavePeriod,
        $rule
    ) {
        $avalilable = $rule->default_per_year;
        $joinedDate = LeaveUtil::getJoinedOrConfirmationDate($employee);

        if ($rule->carried_forward === 'No') {
            return 0;
        }

        if ($rule->propotionate_on_joined_date == "Yes") {
            $joinedDate = LeaveUtil::getJoinedOrConfirmationDate($employee);
            //If the employee joined in this leave period, his leaves should be calculated proportionally
            if ($joinedDate != "0000-00-00 00:00:00" && !empty($joinedDate)) {
                if (strtotime($currentLeavePeriod->date_start) < strtotime($joinedDate)) {
                    $avalilable = floatval(
                        $avalilable
                        * CalendarTools::getNumberOfDaysBetweenDates(
                            $currentLeavePeriod->date_end,
                            $joinedDate
                        ) / CalendarTools::getNumberOfDaysBetweenDates(
                            $currentLeavePeriod->date_end,
                            $currentLeavePeriod->date_start
                        )
                    );
                }
            }
        }

        LogManager::getInstance()->collectLogs(
            sprintf(
                'Number of allocated [Period %s - %s]: leave(%s) + PTO(%s) = %s',
                $currentLeavePeriod->date_start,
                $currentLeavePeriod->date_end,
                round(floatval($avalilable), 3),
                round(floatval($rule->pto), 3),
                round(floatval($avalilable) + floatval($rule->pto), 3)
            )
        );

        return floatval($avalilable) + floatval($rule->pto);
    }

    public static function adjustMaxAndPercentageLeaveCarryForwardAmount(
        $employee,
        $currentLeavePeriod,
        $leaveTypeId,
        $leavesCarriedForward
    ) {
        $rule = self::getLeaveRule($employee, $leaveTypeId, $currentLeavePeriod);
        if ($rule->carried_forward_percentage . '' == "100") {
            //do nothing
        } elseif ($rule->carried_forward_percentage . '' == '0' || empty($rule->carried_forward_percentage)) {
            $leavesCarriedForward = 0;
        } else {
            $leavesCarriedForward = floatval($leavesCarriedForward * floatval($rule->carried_forward_percentage) / 100);
        }

        //If max for carried forward is defined, apply it
        if (is_numeric($rule->max_carried_forward_amount) && floatval($rule->max_carried_forward_amount) > 0) {
            if ($leavesCarriedForward > floatval($rule->max_carried_forward_amount)) {
                $leavesCarriedForward = floatval($rule->max_carried_forward_amount);
            }
        }

        return $leavesCarriedForward;
    }

    public static function getCurrentLeavePeriodWithError($startDate, $endDate)
    {
        $leavePeriod = new LeavePeriod();
        $countryId = null;
        if (LeaveUtil::isCountryBasedLeavePeriodsEnabled()) {
            $countryId = Employee::getCurrentEmployeeCompanyStructureCountry();
            $leavePeriod->Load(
                "date_start <= ? and date_end >= ? and country = ?",
                array($startDate,$endDate, $countryId)
            );
        } else {
            $leavePeriod->Load("date_start <= ? and date_end >= ?", array($startDate,$endDate));
        }

        if (empty($leavePeriod->id) && !LeaveUtil::isCountryBasedLeavePeriodsEnabled()) {
            $leavePeriod1 = new LeavePeriod();
            $leavePeriod1->Load("date_start <= ? and date_end >= ?", array($startDate,$startDate));

            $leavePeriod2 = new LeavePeriod();
            $leavePeriod2->Load("date_start <= ? and date_end >= ?", array($endDate,$endDate));

            if (!empty($leavePeriod1->id) && !empty($leavePeriod2->id)) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "You are trying to apply leaves in two 
                    leave periods. You may apply leaves until $leavePeriod1->date_end.
                    Rest you have to apply separately"
                );
            } else {
                return new IceResponse(
                    IceResponse::ERROR,
                    "The leave period for your leave application is not defined. Please inform administrator"
                );
            }
        } elseif (empty($leavePeriod->id) && LeaveUtil::isCountryBasedLeavePeriodsEnabled()) {
            $leavePeriod1 = new LeavePeriod();
            $leavePeriod1->Load(
                "date_start <= ? and date_end >= ? and country = ?",
                array($startDate,$endDate, $countryId)
            );

            $leavePeriod2 = new LeavePeriod();
            $leavePeriod2->Load(
                "date_start <= ? and date_end >= ? and country = ?",
                array($startDate,$endDate, $countryId)
            );

            if (!empty($leavePeriod1->id) && !empty($leavePeriod2->id)) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "You are trying to apply leaves in two 
                    leave periods. You may apply leaves until $leavePeriod1->date_end. 
                    Rest you have to apply separately"
                );
            } else {
                return new IceResponse(
                    IceResponse::ERROR,
                    "The leave period for your leave application is not defined. Please inform administrator"
                );
            }
        } else {
            return new IceResponse(IceResponse::SUCCESS, $leavePeriod);
        }
    }

    public static function leaveTypeToTime($date, $type)
    {
        //'Full Day','Half Day - Morning','Half Day - Afternoon','1 Hour - Morning',
        //'2 Hours - Morning','3 Hours - Morning','1 Hour - Afternoon','2 Hours - Afternoon',
        //'3 Hours - Afternoon'
        $start = $date;
        $end = $date;
        $timeZone = "+00:00";
        switch ($type) {
        case 'Full Day':
            break;
        case 'Half Day - Morning':
            $start = $start."T"."08:00:00".$timeZone;
            $end = $end."T"."12:30:00".$timeZone;
            break;
        case 'Half Day - Afternoon':
            $start = $start."T"."13:30:00".$timeZone;
            $end = $end."T"."18:00:00".$timeZone;
            break;
        case '1 Hour - Morning':
            $start = $start."T"."08:00:00".$timeZone;
            $end = $end."T"."09:00:00".$timeZone;
            break;
        case '2 Hours - Morning':
            $start = $start."T"."08:00:00".$timeZone;
            $end = $end."T"."10:00:00".$timeZone;
            break;
        case '3 Hours - Morning':
            $start = $start."T"."08:00:00".$timeZone;
            $end = $end."T"."11:00:00".$timeZone;
            break;
        case '1 Hour - Afternoon':
            $start = $start."T"."13:30:00".$timeZone;
            $end = $end."T"."14:30:00".$timeZone;
            break;
        case '2 Hours - Afternoon':
            $start = $start."T"."13:30:00".$timeZone;
            $end = $end."T"."15:30:00".$timeZone;
            break;
        case '3 Hours - Afternoon':
            $start = $start."T"."13:30:00".$timeZone;
            $end = $end."T"."16:30:00".$timeZone;
            break;
        }
        return array($start, $end);
    }

    public static function enrichEmployeeLeave(EmployeeLeave $employeeLeave)
    {
        $leaveDay = new EmployeeLeaveDay();
        $leaveDays = $leaveDay->Find('employee_leave = ?', [$employeeLeave->id]);
        $employeeLeave->total = self::countLeaveAmountByDays($leaveDays);

        return $employeeLeave;
    }

    public static function adjustLeavePeriodDatesBasedOnLeaveType(
        $leavePeriodOrig,
        $leaveRule,
        $employee,
        $leaveStartDate = null
    ) {
        $joinedDate = LeaveUtil::getJoinedOrConfirmationDate($employee);
        // TODO - do not allow apply for leaves which spans into two leave periods based on joined date
        // TODO - if employee_leave_period yes
        if ($leaveRule->employee_leave_period !== 'Yes') {
            $leavePeriodOrig->isEmployee = false;
            return new IceResponse(
                IceResponse::SUCCESS,
                $leavePeriodOrig
            );
        }

        if ($joinedDate === "0000-00-00 00:00:00"
            || empty($joinedDate)
            || ($leaveStartDate !== null && strtotime($joinedDate) > strtotime($leaveStartDate))
        ) {
            //if the user tried to take a leave before his joining date
            return new IceResponse(
                IceResponse::ERROR,
                null
            );
        }


        $leavePeriod = clone $leavePeriodOrig;

        $leavePeriodStartYear = CalendarTools::getYearFromDate($leavePeriod->date_start);
        $joinedDateAdjustedToOnLeaveStartYear
            = CalendarTools::assignYearToDate($joinedDate, $leavePeriodStartYear);

        $employeeLeavePeriodStartDate = $joinedDateAdjustedToOnLeaveStartYear;

        if (strtotime($leavePeriod->date_start) > strtotime($joinedDateAdjustedToOnLeaveStartYear)) {
            $leavePeriodStartYear = intval($leavePeriodStartYear) + 1;
            $employeeLeavePeriodStartDate
                = CalendarTools::assignYearToDate($joinedDate, $leavePeriodStartYear);
        }

        $employeeLeavePeriodEndDate
            = CalendarTools::addMonthsToDateTime($employeeLeavePeriodStartDate, 12, 'Y-m-d');


        // If the adjusted leave period do not contain the leave start date
        if ($leaveStartDate != null && strtotime($employeeLeavePeriodStartDate) > strtotime($leaveStartDate)) {
            $dateInPreviousLeavePeriod = CalendarTools::subtractMonthsFromDateTime(
                $employeeLeavePeriodStartDate,
                12,
                'Y-m-d'
            );
            $leavePeriodResponse = self::getCurrentLeavePeriod($dateInPreviousLeavePeriod, $dateInPreviousLeavePeriod);

            if ($leavePeriodResponse->getStatus() != IceResponse::SUCCESS) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "Could not find a matching leave period"
                );
            }
            $leavePeriod = $leavePeriodResponse->getData();
            $leavePeriodStartYear = CalendarTools::getYearFromDate($leavePeriod->date_start);
            $joinedDateAdjustedToOnLeaveStartYear
                = CalendarTools::assignYearToDate($joinedDate, $leavePeriodStartYear);

            $employeeLeavePeriodStartDate = $joinedDateAdjustedToOnLeaveStartYear;

            if (strtotime($leavePeriod->date_start) > strtotime($joinedDateAdjustedToOnLeaveStartYear)) {
                $leavePeriodStartYear = intval($leavePeriodStartYear) + 1;
                $employeeLeavePeriodStartDate
                    = CalendarTools::assignYearToDate($joinedDate, $leavePeriodStartYear);
            }

            $employeeLeavePeriodEndDate
                = CalendarTools::addMonthsToDateTime($employeeLeavePeriodStartDate, 12, 'Y-m-d');

            if (!(strtotime($employeeLeavePeriodStartDate) <= strtotime($leaveStartDate))
                || !(strtotime($leaveStartDate) <= strtotime($employeeLeavePeriodEndDate))
            ) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "Could not find a matching leave period"
                );
            }
        }

        $leavePeriod->date_start = $employeeLeavePeriodStartDate;
        $leavePeriod->date_end = $employeeLeavePeriodEndDate;

        $leavePeriod->isEmployee = true;

        return new IceResponse(
            IceResponse::SUCCESS,
            $leavePeriod
        );
    }

    public static function get(EmployeeLeave $employeeLeave)
    {
        $leaveDay = new EmployeeLeaveDay();
        $leaveDays = $leaveDay->Find('employee_leave = ?', [$employeeLeave->id]);
        $employeeLeave->total = self::countLeaveAmountByDays($leaveDays);

        return $employeeLeave;
    }

    public static function getJoinedOrConfirmationDate($employee)
    {
        if (empty($employee->joined_date)) {
            return null;
        }
        $useConfirmationDate = SettingsManager::getInstance()->getSetting(
            'Leave: Use Confirmation Date Instead of Joined Date'
        );

        if ($useConfirmationDate == '1' && !empty($employee->confirmation_date)) {
            return $employee->confirmation_date;
        }

        return $employee->joined_date;
    }

    public static function isCountryBasedLeavePeriodsEnabled()
    {
        $setting = new Setting();
        $setting->Load('name = ?', ['Leave: Select Leave Period from Employee Department Country']);
        // return $setting->value === '1';
		return false;
    }

    public static function getEmployeeLeaveDaysBetweenDays($employeeId, $start, $end)
    {
        $employeeLeave = new EmployeeLeave();
        $leaves = $employeeLeave->Find(
            'employee = ? and ((date_start >= ? AND date_start <= ?) OR (date_end >= ? AND date_end <= ?))',
            [
                $employeeId,
                $start,
                $end,
                $start,
                $end,
            ]
        );

        $leaveIds = [];
        $leaveList = [];
        foreach ($leaves as $leave) {
            $leaveIds[] = $leave->id;
            $leaveList[$leave->id] = $leave;
        }

		if (empty($leaveIds)) {
			return [];
		}

        $leaveDay = new EmployeeLeaveDay();
        $days = $leaveDay->Find(
            'employee_leave IN ('.implode(',', $leaveIds).') and leave_date >= ? and leave_date <= ?',
            [
                $start,
                $end,
            ]
        );

        return array_map( function ($day) use ($leaveList) {
           $day->leave = $leaveList[$day->employee_leave];
           return $day;
        }, $days);
    }


}
