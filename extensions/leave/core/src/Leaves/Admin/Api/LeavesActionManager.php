<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 1:39 PM
 */

namespace Leaves\Admin\Api;

use Classes\GoogleCalendarApiManager;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeAccess;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\EmployeeLeaveLog;
use Leaves\Common\Model\HoliDay;
use Leaves\Common\Model\LeaveGroupEmployee;
use Leaves\Common\Model\LeavePeriod;
use Leaves\Common\Model\LeaveRule;
use Leaves\Common\Model\LeaveStartingBalance;
use Leaves\Common\Model\LeaveType;
use Users\Common\Model\User;
use Utils\LogManager;

class LeavesActionManager extends SubActionManager
{

    const FULLDAY = 1;
    const HALFDAY = 0;
    const NOTWORKINGDAY = 2;

    /* @var \Leaves\User\Api\LeavesActionManager $userLeaveActionManager */
    protected $userLeaveActionManager = null;

    private function isLeaveNotificationsEnabled($leaveTypeId)
    {
        $leaveType = new LeaveType();
        $leaveType->Load("id = ?", array($leaveTypeId));
        if (!empty($leaveType->id) && $leaveType->send_notification_emails == "No") {
            return false;
        }

        return true;
    }

    /**
     * @return \Leaves\User\Api\LeavesActionManager
     */
    protected function getUserLeaveActionManager()
    {
        if ($this->userLeaveActionManager === null) {
            $this->userLeaveActionManager = new \Leaves\User\Api\LeavesActionManager();
            $this->userLeaveActionManager->setBaseService($this->baseService);
            $this->userLeaveActionManager->setUser($this->user);
        }

        return $this->userLeaveActionManager;
    }

    public function getLeaveDaysReadonly($req)
    {
        $leaveId = $req->leave_id;
        $leaveLogs = array();

        $employeeLeave = new EmployeeLeave();
        $employeeLeave->Load("id = ?", array($leaveId));

        // Ownership gate, for parity with the guarded user-side twin. This module is
        // Admin-only (dispatcher-gated), so this is defense-in-depth: scope the returned
        // leave detail/matrix to the leave's owner rather than trusting $req->leave_id.
        if (!empty($employeeLeave->id)
            && !$this->baseService->currentUserCanAccessEmployeeData($employeeLeave->employee)
        ) {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }

        $employee = $this->baseService->getElement('Employee', $employeeLeave->employee, null, true);

        $currentLeavePeriodResp = LeaveUtil::getCurrentLeavePeriod(
            $employeeLeave->date_start,
            $employeeLeave->date_end
        );
        if ($currentLeavePeriodResp->getStatus() != IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $currentLeavePeriodResp->getData());
        } else {
            $currentLeavePeriod = $currentLeavePeriodResp->getData();
        }

        $rule = LeaveUtil::getLeaveRule($employee, $employeeLeave->leave_type, $currentLeavePeriod);

        $adjustedLeavePeriodResponse = LeaveUtil::adjustLeavePeriodDatesBasedOnLeaveType(
            $currentLeavePeriod,
            $rule,
            $employee,
            $employeeLeave->date_start
        );

        if ($adjustedLeavePeriodResponse->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $adjustedLeavePeriodResponse->getData());
        }

        $currentLeavePeriod = $adjustedLeavePeriodResponse->getData();

        $leaveMatrix = $this->getAvailableLeaveMatrixForEmployeeLeaveType(
            $employee,
            $currentLeavePeriod,
            $employeeLeave->leave_type,
            $rule
        );

        $leaves = array();
        $leaves['totalLeaves'] = floatval($leaveMatrix[0]);
        $leaves['pendingLeaves'] = floatval($leaveMatrix[1]);
        $leaves['approvedLeaves'] = floatval($leaveMatrix[2]);
        $leaves['rejectedLeaves'] = floatval($leaveMatrix[3]);
        $leaves['cancelRequestedLeaves'] = floatval($leaveMatrix[4]);
        $leaves['cancelledLeaves'] = floatval($leaveMatrix[5]);
        $leaves['availableLeaves'] = $leaves['totalLeaves'] - $leaves['pendingLeaves']
            -  $leaves['approvedLeaves'] - $leaves['cancelRequestedLeaves'];
        $leaves['attachment'] = $employeeLeave->attachment;

        $employeeLeaveDay = new EmployeeLeaveDay();
        $days = $employeeLeaveDay->Find("employee_leave = ?", array($leaveId));

        $employeeLeaveLog = new EmployeeLeaveLog();
        $logsTemp = $employeeLeaveLog->Find("employee_leave = ? order by created", array($leaveId));
        foreach ($logsTemp as $empLeaveLog) {
            $t = array();
            $t['time'] = $empLeaveLog->created;
            $t['status_from'] = $empLeaveLog->status_from;
            $t['status_to'] = $empLeaveLog->status_to;
            $t['time'] = $empLeaveLog->created;
            $userName = null;
            if (!empty($empLeaveLog->user_id)) {
                $lgUser = new User();
                $lgUser->Load("id = ?", array($empLeaveLog->user_id));
                if ($lgUser->id == $empLeaveLog->user_id) {
                    if (!empty($lgUser->employee)) {
                        $lgEmployee = new Employee();
                        $lgEmployee->Load("id = ?", array($lgUser->employee));
                        $userName = $lgEmployee->first_name." ".$lgEmployee->last_name;
                    } else {
                        $userName = $lgUser->userName;
                    }
                }
            }

            if (!empty($userName)) {
                $t['note'] = $empLeaveLog->data." (by: ".$userName.")";
            } else {
                $t['note'] = $empLeaveLog->data;
            }

            $leaveLogs[] = $t;
        }

        return new IceResponse(IceResponse::SUCCESS, array($days,$leaves,$leaveId,$employeeLeave,$leaveLogs));
    }

    private function getAvailableLeaveMatrixForEmployee($employee, $currentLeavePeriod)
    {

        //Iterate all leave types and create leave matrix
        /**
         * [[Leave Type],[Total Available],[Pending],[Approved],[Rejected]]
         */

        $leaveGroupIds = LeaveUtil::getEmployeeLeaveGroups($employee->id);

        $leaveType = new LeaveType();
        if (empty($leaveGroupId)) {
            $leaveTypes = $leaveType->Find("leave_group IS NULL", array());
        } else {
            $leaveTypes = $leaveType->Find(
                "leave_group IS NULL or leave_group in (".implode(',', $leaveGroupIds).")",
                array()
            );
        }

        foreach ($leaveTypes as $leaveType) {
            $employeeLeaveQuota = new \stdClass();

            $rule = LeaveUtil::getLeaveRule($employee, $leaveType->id, $currentLeavePeriod);
            $employeeLeaveQuota->avalilable = floatval($rule->default_per_year) + floatval($rule->pto);
            $pending = LeaveUtil::countLeaveAmounts(
                LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveType->id, 'Pending')
            );
            $approved = LeaveUtil::countLeaveAmounts(
                LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveType->id, 'Approved')
            );
            $rejected = LeaveUtil::countLeaveAmounts(
                LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveType->id, 'Rejected')
            );

            $leaveCounts[$leaveType->name] = array(0,$pending,$approved,$rejected);
        }

        return $leaveCounts;
    }

    private function getAvailableLeaveMatrixForEmployeeLeaveType($employee, $currentLeavePeriod, $leaveTypeId, $rule)
    {

        /**
         * [Total Available],[Pending],[Approved],[Rejected],[Cancellation Requested],[Cancelled]
         */

        $avalilableLeaves = $this->getAvailableLeaveCount($employee, $rule, $currentLeavePeriod, $leaveTypeId);
        $avalilable = $avalilableLeaves[0];
        $pending = LeaveUtil::countLeaveAmounts(
            LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveTypeId, 'Pending')
        );
        $approved = LeaveUtil::countLeaveAmounts(
            LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveTypeId, 'Approved')
        );
        $rejected = LeaveUtil::countLeaveAmounts(
            LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveTypeId, 'Rejected')
        );
        $cancelRequested = LeaveUtil::countLeaveAmounts(
            LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveTypeId, 'Cancellation Requested')
        );
        $cancelled = LeaveUtil::countLeaveAmounts(
            LeaveUtil::getEmployeeLeaves($employee->id, $currentLeavePeriod->id, $leaveTypeId, 'Cancelled')
        );

        return array($avalilable,$pending,$approved,$rejected,$cancelRequested,$cancelled);
    }

    /*
     * Find available leave counts considering Leaves Accrued and Carried Forward
    */
    private function getAvailableLeaveCount($employee, $rule, $currentLeavePeriod, $leaveTypeId)
    {
        return $this->getUserLeaveActionManager()->getAvailableLeaveCount(
            $employee,
            $rule,
            $currentLeavePeriod,
            $leaveTypeId
        );
    }

    public function getSubEmployeeLeaves($req)
    {

        $mappingStr = $req->sm;
        $map = json_decode($mappingStr);
        $employeeLeave = new EmployeeLeave();
        $list = $employeeLeave->Find("1=1");
        if (!$list) {
            LogManager::getInstance()->info($employeeLeave->ErrorMsg());
        }
        if (!empty($mappingStr)) {
            $list = $this->baseService->populateMapping($list, $map);
        }

        return new IceResponse(IceResponse::SUCCESS, $list);
    }

    public function changeLeaveStatus($req)
    {
        $employeeLeave = new EmployeeLeave();
        $employeeLeave->Load("id = ?", array($req->id));
        if ($employeeLeave->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Leave not found");
        }


        if ($this->user->user_level != 'Admin' && EmployeeAccess::hasAccessToAllEmployeeData() === false ) {
            return new IceResponse(IceResponse::ERROR, "Only an admin can do this");
        }

        $oldLeaveStatus = $employeeLeave->status;
        $employeeLeave->status = $req->status;
        $ok = $employeeLeave->Save();
        if (!$ok) {
            LogManager::getInstance()->info($employeeLeave->ErrorMsg());
            return new IceResponse(
                IceResponse::ERROR,
                "Error occurred while saving leave information. Please contact admin"
            );
        }

        $employeeLeaveLog = new EmployeeLeaveLog();
        $employeeLeaveLog->employee_leave = $employeeLeave->id;
        $employeeLeaveLog->user_id = $this->baseService->getCurrentUser()->id;
        $employeeLeaveLog->status_from = $oldLeaveStatus;
        $employeeLeaveLog->status_to = $employeeLeave->status;
        $employeeLeaveLog->created = date("Y-m-d H:i:s");
        $employeeLeaveLog->data = isset($req->reason)?$req->reason:"";
        $ok = $employeeLeaveLog->Save();
        if (!$ok) {
            LogManager::getInstance()->info($employeeLeaveLog->ErrorMsg());
        }

        $employee = $this->getEmployeeById($employeeLeave->employee);

        if ($oldLeaveStatus != $employeeLeave->status
            && $this->isLeaveNotificationsEnabled($employeeLeave->leave_type) == true
        ) {
            $this->sendLeaveStatusChangedEmail($employee, $employeeLeave);
        }

        $this->baseService->audit(
            IceConstants::AUDIT_ACTION,
            "Leave status changed \ from:".$oldLeaveStatus."\ to:".$employeeLeave->status." \ id:".$employeeLeave->id
        );

        $currentEmpId = $this->getCurrentProfileId();

        if (!empty($currentEmpId)) {
            $employee = $this->baseService->getElement('Employee', $currentEmpId, null, true);

            if ($employeeLeave->status != "Pending") {
                $notificationMsg
                    = "Your leave has been $employeeLeave->status by ".$employee->first_name." ".$employee->last_name;
                if (!empty($req->reason)) {
                    $notificationMsg.=" (Note:".$req->reason.")";
                }
            }


            if ($employeeLeave->status === 'Approved') {
                GoogleCalendarApiManager::approveLeaveEvents($employeeLeave);
            } elseif ($employeeLeave->status === 'Cancelled' || $employeeLeave->status === 'Rejected') {
                GoogleCalendarApiManager::deleteLeaveEvents($employeeLeave);
            }

            $this->baseService->notificationManager->addNotification(
                $employeeLeave->employee,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=leaves&m=module_Leaves#tabEmployeeLeaveApproved"}',
                IceConstants::NOTIFICATION_LEAVE
            );
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function sendLeaveStatusChangedEmail($employee, $leave)
    {

        $emp = $this->getEmployeeById($leave->employee);

        $params = array();
        $params['name'] = $emp->first_name." ".$emp->last_name;
        $params['startdate'] = $leave->date_start;
        $params['enddate'] = $leave->date_end;
        $params['status'] = $leave->status;

        $user = $this->getUserFromProfileId($employee->id);

        if (!empty($user)) {
            // Resolve relative to this file: the templates ship with the leave
            // package (<package>/core/modules/leaves), not with core/.
            $email = file_get_contents(
                __DIR__.'/../../../../modules/leaves/emailTemplates/leaveStatusChanged.html'
            );
            if (!empty($this->emailSender)) {
                $this->emailSender->sendEmail("Leave Application ".$leave->status, $user->email, $email, $params);
            }
        }
    }

    private function getEmployeeById($id)
    {
        $sup = new Employee();
        $sup->Load("id = ?", array($id));
        if ($sup->id != $id) {
            LogManager::getInstance()->info("Employee not found");
            return null;
        }

        return $sup;
    }

    public function deleteLeavePeriod($req)
    {
        $employeeLeave = new EmployeeLeave();
        $leavePeriod = new LeavePeriod();

        $leavePeriod->Load("id = ?", array($req->id));
        if (empty($leavePeriod->id)) {
            return new IceResponse(IceResponse::ERROR);
        }

        $this->baseService->checkSecureAccess('delete', $leavePeriod, 'LeavePeriod', $_POST);

        $employeeLeave = $employeeLeave->Find("leave_period = ? limit 1", $leavePeriod->id);

        if (!empty($employeeLeave) && !empty($employeeLeave[0]->id)) {
            return new IceResponse(
                IceResponse::ERROR,
                'A leave period with active employee leave days can not be deleted'
            );
        }
        $ok = $leavePeriod->Delete();
        if (!$ok) {
            return new IceResponse(
                IceResponse::ERROR,
                'Error occurred while deleting leave period'
            );
        }

        return new IceResponse(IceResponse::SUCCESS);
    }

    /**
     * Return usage statistics for a leave period:
     *  - hasLeaves: whether any employee leave is linked (evaluated fresh, so a
     *               period that just received its first leave locks immediately)
     *  - days / requests: the number of leave days and leave requests recorded
     *               against the period. This (heavier) count is cached per
     *               leave period in the DB cache for one day (86400s).
     */
    public function getLeavePeriodStats($req)
    {
        $id = isset($req->id) ? intval($req->id) : 0;
        if ($id <= 0) {
            return new IceResponse(IceResponse::ERROR, 'Invalid leave period');
        }

        $connection = \Classes\BaseService::getInstance()->getDB();

        // Editability gate — always fresh and cheap (LIMIT 1).
        $existing = $connection->Execute(
            'SELECT 1 FROM EmployeeLeaves WHERE leave_period = ? LIMIT 1',
            [$id]
        );
        $hasLeaves = !empty($existing);

        // Counts — cached per period for one day.
        $cacheKey = 'leave_period_stats_' . $id;
        $cached = \Classes\DatabaseCache::getInstance()->get($cacheKey);
        if (is_array($cached) && isset($cached['days'])) {
            $days = intval($cached['days']);
            $requests = intval($cached['requests']);
        } else {
            $reqRs = $connection->Execute(
                'SELECT COUNT(*) AS c FROM EmployeeLeaves WHERE leave_period = ?',
                [$id]
            );
            $requests = !empty($reqRs) ? intval($reqRs[0]['c']) : 0;

            $dayRs = $connection->Execute(
                'SELECT COUNT(eld.id) AS c FROM EmployeeLeaveDays eld '
                . 'INNER JOIN EmployeeLeaves el ON eld.employee_leave = el.id '
                . 'WHERE el.leave_period = ?',
                [$id]
            );
            $days = !empty($dayRs) ? intval($dayRs[0]['c']) : 0;

            \Classes\DatabaseCache::getInstance()->set(
                $cacheKey,
                ['days' => $days, 'requests' => $requests],
                86400
            );
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'hasLeaves' => $hasLeaves,
            'days' => $days,
            'requests' => $requests,
        ]);
    }

    /**
     * Countries we have bundled public-holiday data for, mapped to the matching
     * IceHRM Country rows (so the UI gets a selectable id + name).
     *
     * @return IceResponse list of [ 'id' => .., 'name' => '..', 'code' => 'DE' ]
     */
    public function getHolidayCountries($req)
    {
        $available = \Leaves\Common\PublicHolidayProvider::getInstance()->getAvailableCountries();

        $connection = \Classes\BaseService::getInstance()->getDB();
        $rows = $connection->Execute('SELECT id, name, code FROM Country');
        $byCode = [];
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $byCode[strtoupper($r['code'])] = $r;
            }
        }

        $out = [];
        foreach ($available as $c) {
            $code = strtoupper($c['code']);
            if (isset($byCode[$code])) {
                $out[] = [
                    'id' => intval($byCode[$code]['id']),
                    'name' => $byCode[$code]['name'],
                    'code' => $code,
                ];
            }
        }
        usort($out, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return new IceResponse(IceResponse::SUCCESS, [
            'countries' => $out,
            'minYear' => \Leaves\Common\PublicHolidayProvider::MIN_YEAR,
            'maxYear' => \Leaves\Common\PublicHolidayProvider::MAX_YEAR,
        ]);
    }

    /**
     * Bundled public holidays for a country + year, each flagged with whether
     * it is already on the calendar for that country.
     *
     * $req->country : IceHRM Country id
     * $req->year    : year (2022–2035)
     */
    public function getStoredHolidays($req)
    {
        $countryId = isset($req->country) ? intval($req->country) : 0;
        $year = isset($req->year) ? intval($req->year) : 0;
        if ($countryId <= 0 || $year <= 0) {
            return new IceResponse(IceResponse::ERROR, 'Please choose a country and year');
        }

        $connection = \Classes\BaseService::getInstance()->getDB();
        $countryRs = $connection->Execute('SELECT code FROM Country WHERE id = ?', [$countryId]);
        $code = (!empty($countryRs) && isset($countryRs[0]['code'])) ? $countryRs[0]['code'] : '';
        if ($code === '') {
            return new IceResponse(IceResponse::ERROR, 'Unknown country');
        }

        $holidays = \Leaves\Common\PublicHolidayProvider::getInstance()->getHolidays($code, $year);

        // Flag holidays already saved for this country/year.
        $existing = [];
        $existRs = $connection->Execute(
            'SELECT dateh FROM HoliDays WHERE country = ? AND dateh >= ? AND dateh <= ?',
            [$countryId, $year . '-01-01', $year . '-12-31']
        );
        if (!empty($existRs)) {
            foreach ($existRs as $r) {
                $existing[substr($r['dateh'], 0, 10)] = true;
            }
        }

        $out = [];
        foreach ($holidays as $h) {
            $date = isset($h['date']) ? $h['date'] : '';
            if ($date === '') {
                continue;
            }
            $out[] = [
                'date' => $date,
                'name' => isset($h['name']) ? $h['name'] : '',
                'exists' => isset($existing[$date]),
            ];
        }

        return new IceResponse(IceResponse::SUCCESS, ['holidays' => $out]);
    }

    /**
     * Bulk-insert the holidays the admin selected. Each holiday already on the
     * same date for the same country is skipped, so re-importing is safe.
     *
     * $req->country  : Country id the holidays belong to (0 / empty = all)
     * $req->holidays : [{ date: 'YYYY-MM-DD', name: '...' }, ...]
     */
    public function importHolidays($req)
    {
        if (!isset($req->holidays) || !is_array($req->holidays)) {
            return new IceResponse(IceResponse::ERROR, 'No holidays were provided to import');
        }

        if ($this->user->user_level != 'Admin' && EmployeeAccess::hasAccessToAllEmployeeData() === false) {
            return new IceResponse(IceResponse::ERROR, 'Only an admin can import holidays');
        }

        $country = isset($req->country) ? intval($req->country) : 0;
        $inserted = 0;
        $skipped = 0;

        foreach ($req->holidays as $h) {
            $date = isset($h->date) ? trim($h->date) : '';
            $name = isset($h->name) ? trim($h->name) : '';
            if ($date === '' || $name === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            $existing = new HoliDay();
            if ($country > 0) {
                $rows = $existing->Find('dateh = ? and country = ?', [$date, $country]);
            } else {
                $rows = $existing->Find('dateh = ? and (country is null or country = 0)', [$date]);
            }
            if (!empty($rows) && !empty($rows[0]->id)) {
                $skipped++;
                continue;
            }

            $holiday = new HoliDay();
            $holiday->name = mb_substr($name, 0, 100);
            $holiday->dateh = $date;
            $holiday->status = 'Full Day';
            $holiday->country = $country > 0 ? $country : null;
            $ok = $holiday->Save();
            if ($ok) {
                $inserted++;
            } else {
                LogManager::getInstance()->info($holiday->ErrorMsg());
            }
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'inserted' => $inserted,
            'skipped' => $skipped,
        ]);
    }
}
