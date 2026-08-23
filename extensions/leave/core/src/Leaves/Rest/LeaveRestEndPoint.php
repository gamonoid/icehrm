<?php
namespace Leaves\Rest;

use Attendance\Common\Model\Attendance;
use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\LanguageManager;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\LeavesEmailSender;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\LeaveType;
use Leaves\Common\Model\LeavePeriod;
use Leaves\User\Api\LeavesActionManager;
use Users\Common\Model\User;
use Utils\LogManager;

class LeaveRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'EmployeeLeave';

    protected $userLeaveActionManager;

    public function __construct()
    {
        $this->userLeaveActionManager = new LeavesActionManager();
        $this->userLeaveActionManager->setBaseService(BaseService::getInstance());
        $this->userLeaveActionManager->setEmailSender(BaseService::getInstance()->getEmailSender());
    }

    public function getLeaveDetails(User $user, $leaveId)
    {
        $this->userLeaveActionManager->setUser($user);
        $req = new \stdClass();
        $req->leave_id = $leaveId;
        $response = $this->userLeaveActionManager->getLeaveDaysReadonly($req);

        if ($response->getStatus() === IceResponse::SUCCESS) {
            $data = $response->getData();
            $leaveDays = [];
            foreach ($data[0] as $leaveDay) {
                $leaveDays[] = BaseService::getInstance()->cleanUpAll($leaveDay);
            }

            $leave = $data[3];
            $leaveData = BaseService::getInstance()->cleanUpAll($leave);

            // Load leave type details
            $leaveTypeData = null;
            if (!empty($leave->leave_type)) {
                $leaveType = new LeaveType();
                $leaveType->Load('id = ?', [$leave->leave_type]);
                if ($leaveType->id) {
                    $leaveTypeData = BaseService::getInstance()->cleanUpAll($leaveType);
                }
            }

            // Load leave period details
            $leavePeriodData = null;
            if (!empty($leave->leave_period)) {
                $leavePeriod = new LeavePeriod();
                $leavePeriod->Load('id = ?', [$leave->leave_period]);
                if ($leavePeriod->id) {
                    $leavePeriodData = BaseService::getInstance()->cleanUpAll($leavePeriod);
                }
            }

            return new IceResponse(
                IceResponse::SUCCESS,
                [
                    'id' => $data[2],
                    'currentBalance' => $data[1],
                    'leave' => $leaveData,
                    'leaveType' => $leaveTypeData,
                    'leavePeriod' => $leavePeriodData,
                    'days' => $leaveDays,
                    'logs' => $data[4]
                ]
            );
        }

        return $response;
    }

    public function checkLeave(User $user, $leaveTypeId, $startDate, $endDate)
    {
        $this->userLeaveActionManager->setUser($user);
        $req = new \stdClass();
        $req->leave_type = $leaveTypeId;
        $req->start_date = $startDate;
        $req->end_date = $endDate;
        $response = $this->userLeaveActionManager->getLeaveDays($req);

        if ($response->getStatus() === IceResponse::SUCCESS) {
            $data = $response->getData();

            return new IceResponse(
                IceResponse::SUCCESS,
                [
                    'currentBalance' => $data[1],
                    'leave' => BaseService::getInstance()->cleanUpAll($data[2]),
                    'dayMap' => $data[0],
                ]
            );
        }

        return $response;
    }

    public function getEntitlement(User $user)
    {
        $this->userLeaveActionManager->setUser($user);
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);
        $response = $this->userLeaveActionManager->getEntitlementByEmployee($employee);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, ['data' => $response->getData()]);
        }

        return $response;
    }

    public function getAllMyLeave(User $user)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        $employeeLeave = new EmployeeLeave();
        $employeeLeaveList = $employeeLeave->Find(
            'employee = ? order by date_start desc limit 100',
            [$employee->id]
        );

        $leaveList = [];
        $employeeLeaveList = BaseService::getInstance()->populateMapping(
            $employeeLeaveList,
            [
                "leave_type" => [
                    "LeaveType",
                    "id",
                    "name",
                    "getUserLeaveTypes"
                ],
                "leave_period" => [
                    "LeavePeriod",
                    "id",
                    "name"
                ]]
        );
        foreach ($employeeLeaveList as $leave) {
            $leave = LeaveUtil::enrichEmployeeLeave($leave);
            $leave = BaseService::getInstance()->cleanUpAll($leave);
            $leaveList[] = $leave;
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $leaveList]);
    }

    public function apply(User $user)
    {
        $this->userLeaveActionManager->setUser($user);
        $leaveData = $this->getRequestBody();

        $req = new \stdClass();
        $req->leave_type = $leaveData['leave_type'];
        $req->date_start = $leaveData['start'];
        $req->date_end = $leaveData['end'];
        $req->details = $leaveData['details'];
        $req->attachment = $leaveData['attachment'];
        $req->days = json_encode($leaveData['days']);
        $response = $this->userLeaveActionManager->addLeave($req);

        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(
                IceResponse::SUCCESS,
                BaseService::getInstance()->cleanUpAll($response->getData()),
                201
            );
        }

        return $response;
    }

    /**
     * Admin: apply for leave ON BEHALF of another employee. Same body as apply() plus an
     * `employeeId` (or `employee`). The target employee is set via the current profile id, so
     * addLeave() creates the leave for them; the admin's user_level lets it bypass balance /
     * lock-period checks.
     */
    public function applyFor(User $user)
    {
        if (empty($user) || ($user->user_level !== 'Admin'
            && !\Employees\Common\Model\EmployeeAccess::hasAccessToAllEmployeeData())
        ) {
            return new IceResponse(IceResponse::ERROR, 'Permission denied (admin only)', 403);
        }

        $leaveData = $this->getRequestBody();
        $employeeId = $leaveData['employeeId'] ?? ($leaveData['employee'] ?? null);
        if (empty($employeeId)) {
            return new IceResponse(IceResponse::ERROR, 'employeeId is required', 400);
        }

        $employee = new Employee();
        $employee->Load('id = ?', [$employeeId]);
        if (empty($employee->id) || $employee->id != $employeeId) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        $this->userLeaveActionManager->setUser($user);
        // Point the leave-creation logic at the target employee. getCurrentProfileId()
        // returns this value, so addLeave() builds the leave for that employee.
        $previousProfileId = BaseService::getInstance()->getCurrentProfileId();
        BaseService::getInstance()->setCurrentProfileId($employeeId);

        try {
            $req = new \stdClass();
            $req->leave_type = $leaveData['leave_type'] ?? null;
            $req->date_start = $leaveData['start'] ?? null;
            $req->date_end = $leaveData['end'] ?? null;
            $req->details = $leaveData['details'] ?? '';
            $req->attachment = $leaveData['attachment'] ?? '';
            $req->days = json_encode($leaveData['days'] ?? []);
            $response = $this->userLeaveActionManager->addLeave($req);
        } finally {
            // Restore the admin's own profile context.
            BaseService::getInstance()->setCurrentProfileId($previousProfileId);
        }

        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(
                IceResponse::SUCCESS,
                BaseService::getInstance()->cleanUpAll($response->getData()),
                201
            );
        }
        return $response;
    }

    /**
     * Get all leave requests from direct reports (employees who have current user as supervisor)
     */
    public function getDirectReportsLeave(User $user)
    {
        return $this->fetchDirectReportsLeave($user, null);
    }

    /**
     * Get pending leave requests from direct reports (employees who have current user as supervisor)
     */
    public function getDirectReportsPendingLeave(User $user)
    {
        return $this->fetchDirectReportsLeave($user, 'Pending');
    }

    /**
     * Helper method to fetch leave requests from direct reports with optional status filter
     */
    private function fetchDirectReportsLeave(User $user, ?string $status): IceResponse
    {
        // Get the current user's employee record
        $currentEmployee = new Employee();
        $currentEmployee->Load('id = ?', [$user->employee]);

        if (!$currentEmployee->id) {
            return new IceResponse(IceResponse::ERROR, 'No employee record found for current user');
        }

        // Find all employees who have the current employee as their supervisor
        $employee = new Employee();
        $directReports = $employee->Find('supervisor = ? AND status = ?', [$currentEmployee->id, 'Active']);

        if (empty($directReports)) {
            return new IceResponse(IceResponse::SUCCESS, ['data' => []]);
        }

        // Get employee IDs of direct reports
        $directReportIds = array_map(function ($emp) {
            return $emp->id;
        }, $directReports);

        // Build the IN clause for the query
        $placeholders = implode(',', array_fill(0, count($directReportIds), '?'));

        // Build query based on status filter
        if ($status !== null) {
            $query = "employee IN ($placeholders) AND status = ? ORDER BY date_start DESC";
            $params = array_merge($directReportIds, [$status]);
        } else {
            $query = "employee IN ($placeholders) ORDER BY date_start DESC LIMIT 200";
            $params = $directReportIds;
        }

        // Find leave requests for these employees
        $employeeLeave = new EmployeeLeave();
        $employeeLeaveList = $employeeLeave->Find($query, $params);

        // Populate mappings for leave type, leave period, and employee
        $employeeLeaveList = BaseService::getInstance()->populateMapping(
            $employeeLeaveList,
            [
                "leave_type" => [
                    "LeaveType",
                    "id",
                    "name",
                    "getUserLeaveTypes"
                ],
                "leave_period" => [
                    "LeavePeriod",
                    "id",
                    "name"
                ],
                "employee" => [
                    "Employee",
                    "id",
                    "first_name+last_name"
                ]
            ]
        );

        $leaveList = [];
        foreach ($employeeLeaveList as $leave) {
            $leave = LeaveUtil::enrichEmployeeLeave($leave);
            $leave = BaseService::getInstance()->cleanUpAll($leave);
            $leaveList[] = $leave;
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $leaveList]);
    }

    /**
     * Approve a leave request
     */
    public function approveLeave(User $user, $leaveId)
    {
        return $this->changeLeaveStatus($user, $leaveId, 'Approved');
    }

    /**
     * Reject a leave request
     */
    public function rejectLeave(User $user, $leaveId)
    {
        return $this->changeLeaveStatus($user, $leaveId, 'Rejected');
    }

    /**
     * Helper method to change leave status (approve/reject)
     * Accepts 'reason' or 'note' field in request body (same as web interface)
     */
    private function changeLeaveStatus(User $user, $leaveId, string $status): IceResponse
    {
        $this->userLeaveActionManager->setUser($user);

        // Get optional reason/note from request body (accept both field names)
        $body = $this->getRequestBody();
        $reason = '';
        if (!empty($body['reason'])) {
            $reason = $body['reason'];
        } elseif (!empty($body['note'])) {
            $reason = $body['note'];
        }

        $req = new \stdClass();
        $req->id = $leaveId;
        $req->status = $status;
        $req->reason = $reason;

        $response = $this->userLeaveActionManager->changeLeaveStatus($req);

        if ($response->getStatus() === IceResponse::SUCCESS) {
            // Load the updated leave to return
            $employeeLeave = new EmployeeLeave();
            $employeeLeave->Load('id = ?', [$leaveId]);

            if ($employeeLeave->id) {
                return new IceResponse(
                    IceResponse::SUCCESS,
                    [
                        'message' => "Leave request {$status}",
                        'leave' => BaseService::getInstance()->cleanUpAll($employeeLeave)
                    ]
                );
            }
        }

        return $response;
    }
}
