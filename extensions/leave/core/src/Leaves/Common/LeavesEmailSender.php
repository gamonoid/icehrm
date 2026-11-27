<?php
/**
 * Leave emails: a new leave request or cancellation request (to the supervisor and,
 * when enabled, the indirect supervisors), the "request submitted" receipt (to the
 * employee) and the status-change email (to the employee).
 *
 * Bodies are built with EmailParts and wrapped in the shared layout by EmailSender.
 */

namespace Leaves\Common;

use Classes\Email\EmailParts;
use Classes\Email\EmailSender;
use Classes\SettingsManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\LeaveType;
use Utils\LogManager;

class LeavesEmailSender
{
    /** Leave requests of the supervisor's team (pending approval). */
    const ROUTE_REQUESTS = 'g=modules&n=leaves#tabSubEmployeeLeaveAll';
    /** Cancellation requests of the supervisor's team. */
    const ROUTE_CANCELLATIONS = 'g=modules&n=leaves#tabSubEmployeeLeaveCancel';
    /** The employee's own leave. */
    const ROUTE_MY_LEAVE = 'g=modules&n=leaves';

    /**
     * @var EmailSender $emailSender
     */
    protected $emailSender = null;
    protected $subActionManager = null;
    protected $modulePath = null;

    public function __construct($emailSender, $subActionManager, $modulePath = null)
    {
        $this->emailSender = $emailSender;
        $this->subActionManager = $subActionManager;
        $this->modulePath = $modulePath;
    }

    // --- Sentences shared with the in-app notifications ------------------------------

    /** "from 2 Dec to 4 Dec" / "on 2 Dec" (the year is added when it is not this year). */
    public static function shortDateRange($start, $end)
    {
        $from = self::shortDate($start);
        $to = self::shortDate($end);
        if (empty($end) || $from === $to) {
            return 'on ' . $from;
        }
        return 'from ' . $from . ' to ' . $to;
    }

    /** "Mon, 2 Dec 2026 to Wed, 4 Dec 2026" / "Mon, 2 Dec 2026". */
    public static function dateRange($start, $end)
    {
        if (empty($end) || $start === $end) {
            return EmailParts::date($start);
        }
        return EmailParts::date($start) . ' to ' . EmailParts::date($end);
    }

    /**
     * One sentence telling an employee what happened to their leave, e.g.
     * "Your leave request was approved by Jane Doe." Used for the email and the
     * in-app notification. $reviewerName may be empty.
     */
    public static function statusSentence($status, $reviewerName, $previousStatus = null)
    {
        $by = trim((string) $reviewerName) === '' ? '' : ' by ' . trim($reviewerName);
        if ($previousStatus === 'Cancellation Requested' && $status === 'Approved') {
            return 'Your request to cancel your leave was declined' . $by . '. The leave is still approved.';
        }
        switch ($status) {
            case 'Approved':
                return 'Your leave request was approved' . $by . '.';
            case 'Rejected':
                return 'Your leave request was rejected' . $by . '.';
            case 'Cancelled':
                return 'Your leave was cancelled' . $by . '.';
            case 'Processing':
                return 'Your leave request was approved' . $by . ' and is waiting for the next approval.';
            case 'Cancellation Requested':
                return 'A request to cancel your leave was sent for approval.';
            default:
                return 'Your leave request is now ' . strtolower((string) $status) . '.';
        }
    }

    /** Email subject for a status change. */
    public static function statusSubject($status, $previousStatus = null)
    {
        if ($previousStatus === 'Cancellation Requested' && $status === 'Approved') {
            return 'Your leave cancellation request was declined';
        }
        switch ($status) {
            case 'Approved':
                return 'Your leave request was approved';
            case 'Rejected':
                return 'Your leave request was rejected';
            case 'Cancelled':
                return 'Your leave was cancelled';
            case 'Processing':
                return 'Your leave request is waiting for the next approval';
            default:
                return 'Your leave request is now ' . strtolower((string) $status);
        }
    }

    // --- Emails --------------------------------------------------------------------

    /**
     * Tell the employee's supervisor (and indirect supervisors, when they may approve)
     * about a new leave request, or a request to cancel approved leave.
     *
     * @param Employee      $employee
     * @param EmployeeLeave $employeeLeave
     * @param bool          $cancellation
     * @return bool false when nobody could be emailed
     */
    public function sendLeaveApplicationEmail($employee, $employeeLeave, $cancellation = false)
    {
        if (empty($this->emailSender)) {
            return false;
        }

        $dep = new CompanyStructure();
        $dep->Load("id = ?", array($employee->department));

        $type = new LeaveType();
        $type->Load("id = ?", array($employeeLeave->leave_type));

        $days = $this->getLeaveDays($employeeLeave);
        $info = array(
            'name' => trim($employee->first_name . ' ' . $employee->last_name),
            'department' => empty($dep->id) ? '' : $dep->title,
            'leaveType' => $type->name,
            'days' => $days,
        );

        $sent = false;

        // The direct supervisor (an employee without one is still reported to the
        // indirect supervisors below).
        $sup = $this->getEmployeeSupervisor($employee);
        if (!empty($sup)) {
            $sent = $this->sendRequestToSupervisor(
                $sup,
                false,
                $employeeLeave,
                $info,
                $cancellation,
                $this->emailListSetting('Leave: CC Emails', 10),
                $this->emailListSetting('Leave: BCC Emails', 4)
            ) || $sent;
        }

        // Indirect supervisors, when they are allowed to approve.
        if ($employeeLeave->allowIndirectMapping() && !empty($employee->indirect_supervisors)) {
            $indirectSupervisors = json_decode($employee->indirect_supervisors, true);
            if (is_array($indirectSupervisors)) {
                foreach (array_unique($indirectSupervisors) as $is) {
                    if (!empty($sup) && (int) $is === (int) $sup->id) {
                        continue;
                    }
                    $supervisor = $this->getEmployeeById($is);
                    if (empty($supervisor)) {
                        continue;
                    }
                    $sent = $this->sendRequestToSupervisor(
                        $supervisor,
                        true,
                        $employeeLeave,
                        $info,
                        $cancellation,
                        array(),
                        array()
                    ) || $sent;
                }
            }
        }

        return $sent;
    }

    /** The "your leave request was submitted" receipt to the employee. */
    public function sendLeaveApplicationSubmittedEmail($employee, $employeeLeave)
    {
        if (empty($this->emailSender)) {
            return false;
        }
        $emailTo = $this->emailOfEmployee($employee->id);
        if (empty($emailTo)) {
            LogManager::getInstance()->info("[sendLeaveApplicationSubmittedEmail] email is empty");
            return false;
        }

        $type = new LeaveType();
        $type->Load("id = ?", array($employeeLeave->leave_type));
        $days = $this->getLeaveDays($employeeLeave);

        $url = EmailParts::appUrl(self::ROUTE_MY_LEAVE);
        $sentence = 'Your leave request has been sent for approval. '
            . 'You will get an email when it is approved or rejected.';

        $body = EmailParts::greeting($employee->first_name)
            . EmailParts::text($sentence)
            . EmailParts::details(array(
                'Leave type' => $type->name,
                'Dates' => self::dateRange($employeeLeave->date_start, $employeeLeave->date_end),
                'Days' => $this->formatDays(LeaveUtil::countLeaveAmountByDays($days)),
            ))
            . EmailParts::note($employeeLeave->details, 'Your reason')
            . $this->daysTable($days)
            . EmailParts::button($url, 'View my leave')
            . EmailParts::fallbackLink($url);

        $this->emailSender->sendEmail(
            'Your leave request was submitted',
            $emailTo,
            $body,
            array(
                'url' => $url,
                'preheader' => EmailParts::esc(
                    'Your leave request ' . self::shortDateRange($employeeLeave->date_start, $employeeLeave->date_end)
                    . ' was sent for approval.'
                ),
            )
        );
        return true;
    }

    /**
     * Tell the employee that the status of their leave changed.
     *
     * @param Employee|null $reviewer       who changed it (null for automatic changes)
     * @param EmployeeLeave $leave
     * @param string        $note           the reviewer's note, if any
     * @param string|null   $previousStatus
     */
    public function sendLeaveStatusChangedEmail($reviewer, $leave, $note = '', $previousStatus = null)
    {
        if (empty($this->emailSender)) {
            return false;
        }
        $emp = $this->getEmployeeById($leave->employee);
        if (empty($emp)) {
            return false;
        }
        $emailTo = $this->emailOfEmployee($emp->id);
        if (empty($emailTo)) {
            LogManager::getInstance()->info("[sendLeaveStatusChangedEmail] email is empty");
            return false;
        }

        $reviewerName = '';
        if (!empty($reviewer) && !empty($reviewer->id) && (int) $reviewer->id !== (int) $emp->id) {
            $reviewerName = trim($reviewer->first_name . ' ' . $reviewer->last_name);
        }

        $type = new LeaveType();
        $type->Load("id = ?", array($leave->leave_type));
        $days = $this->getLeaveDays($leave);

        $url = EmailParts::appUrl(self::ROUTE_MY_LEAVE);
        $sentence = self::statusSentence($leave->status, $reviewerName, $previousStatus);
        $noteLabel = $reviewerName === '' ? 'Note' : 'Note from ' . $reviewerName;

        $body = EmailParts::greeting($emp->first_name)
            . EmailParts::text($sentence)
            . EmailParts::details(array(
                'Leave type' => $type->name,
                'Dates' => self::dateRange($leave->date_start, $leave->date_end),
                'Days' => $this->formatDays(LeaveUtil::countLeaveAmountByDays($days)),
                'Status' => $leave->status,
            ))
            . EmailParts::note($note, $noteLabel)
            . EmailParts::button($url, 'View my leave')
            . EmailParts::fallbackLink($url);

        $this->emailSender->sendEmail(
            self::statusSubject($leave->status, $previousStatus),
            $emailTo,
            $body,
            array(
                'url' => $url,
                'preheader' => EmailParts::esc($sentence),
            )
        );
        return true;
    }

    // --- Helpers -------------------------------------------------------------------

    /**
     * @param Employee $supervisor
     * @param bool     $indirect
     */
    protected function sendRequestToSupervisor(
        $supervisor,
        $indirect,
        $employeeLeave,
        array $info,
        $cancellation,
        array $ccList,
        array $bccList
    ) {
        $emailTo = $this->emailOfEmployee($supervisor->id);
        if (empty($emailTo)) {
            LogManager::getInstance()->info(
                "[sendLeaveApplicationEmail] no email for " . ($indirect ? 'indirect ' : '') . "supervisor " . $supervisor->id
            );
            return false;
        }

        $name = $info['name'];
        $range = self::shortDateRange($employeeLeave->date_start, $employeeLeave->date_end);
        $who = $indirect ? $name . ' (indirect report)' : $name;

        if ($cancellation) {
            $subject = $who . ' asked to cancel approved leave';
            $sentence = $indirect
                ? $name . ', one of your indirect reports, asked to cancel leave that was already approved. '
                    . 'Please approve or reject the cancellation.'
                : $name . ' asked to cancel leave that was already approved. Please approve or reject the cancellation.';
            $route = self::ROUTE_CANCELLATIONS;
            $buttonLabel = 'Review cancellation';
            $preheader = $name . ' asked to cancel approved leave ' . $range . '.';
        } else {
            $subject = 'Leave request from ' . $who;
            $sentence = $indirect
                ? $name . ', one of your indirect reports, requested leave. Please approve or reject the request.'
                : $name . ' requested leave. Please approve or reject the request.';
            $route = self::ROUTE_REQUESTS;
            $buttonLabel = 'Review request';
            $preheader = $name . ' applied for leave ' . $range . '.';
        }

        $url = EmailParts::appUrl($route);
        $body = EmailParts::greeting($supervisor->first_name)
            . EmailParts::text($sentence)
            . EmailParts::details(array(
                'Employee' => $name,
                'Department' => $info['department'],
                'Leave type' => $info['leaveType'],
                'Dates' => self::dateRange($employeeLeave->date_start, $employeeLeave->date_end),
                'Days' => $this->formatDays(LeaveUtil::countLeaveAmountByDays($info['days'])),
            ))
            . EmailParts::note($employeeLeave->details, 'Reason')
            . $this->daysTable($info['days'])
            . EmailParts::button($url, $buttonLabel)
            . EmailParts::fallbackLink($url);

        $reason = 'You received this email because you are '
            . ($indirect ? 'an indirect supervisor of ' : 'the supervisor of ')
            . EmailParts::esc($name) . ' at ' . EmailParts::esc(EmailParts::companyName()) . '.';

        $this->emailSender->sendEmail(
            $subject,
            $emailTo,
            $body,
            array(
                'url' => $url,
                'preheader' => EmailParts::esc($preheader),
                'emailReason' => $reason,
            ),
            $ccList,
            $bccList
        );
        return true;
    }

    /** Up to $max valid addresses from a comma-separated setting. */
    protected function emailListSetting($setting, $max)
    {
        $list = array();
        $value = SettingsManager::getInstance()->getSetting($setting);
        if (empty($value)) {
            return $list;
        }
        foreach (array_slice(explode(',', $value), 0, $max) as $address) {
            $address = trim($address);
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $list[] = $address;
            }
        }
        return $list;
    }

    protected function emailOfEmployee($employeeId)
    {
        $user = $this->subActionManager->getUserFromProfileId($employeeId);
        return empty($user) || empty($user->email) ? null : $user->email;
    }

    /**
     * The days of a leave request, in date order.
     *
     * @return EmployeeLeaveDay[]
     */
    protected function getLeaveDays($employeeLeave)
    {
        if (empty($employeeLeave->id)) {
            return array();
        }
        $day = new EmployeeLeaveDay();
        $days = $day->Find("employee_leave = ? ORDER BY leave_date", array($employeeLeave->id));
        return is_array($days) ? $days : array();
    }

    /** The per-day table (date and day type, e.g. "Half Day - Morning"). */
    protected function daysTable(array $days)
    {
        $rows = array();
        foreach ($days as $day) {
            $rows[] = array(EmailParts::date($day->leave_date), $day->leave_type);
        }
        return EmailParts::table(array('Date', 'Day type'), $rows);
    }

    protected function formatDays($amount)
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return '';
        }
        $text = rtrim(rtrim(number_format($amount, 3, '.', ''), '0'), '.');
        return $text . ($amount == 1 ? ' day' : ' days');
    }

    private static function shortDate($value)
    {
        $time = empty($value) ? false : strtotime((string) $value);
        if ($time === false) {
            return (string) $value;
        }
        return date('Y', $time) === date('Y') ? date('j M', $time) : date('j M Y', $time);
    }

    private function getEmployeeSupervisor($employee)
    {
        if (empty($employee->supervisor)) {
            LogManager::getInstance()->info("Employee supervisor is empty");
            return null;
        }
        return $this->getEmployeeById($employee->supervisor);
    }

    private function getEmployeeById($id)
    {
        if (empty($id)) {
            return null;
        }
        $emp = new Employee();
        $emp->Load("id = ?", array($id));
        if ($emp->id != $id) {
            LogManager::getInstance()->info("Employee not found");
            return null;
        }
        return $emp;
    }
}
