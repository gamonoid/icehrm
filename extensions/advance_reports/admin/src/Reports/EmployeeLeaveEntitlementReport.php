<?php
namespace Advance_reportsAdmin\Reports;

use Classes\BaseService;
use Employees\Common\Model\Employee;
use Leaves\User\Api\LeavesActionManager;

class EmployeeLeaveEntitlementReport extends BaseReport
{
    protected $name = 'Employee Leave Entitlement';
    protected $description = 'This report lists employees leave entitlement for current leave period by department or by employee';
    protected $group = 'Leave Management';
    protected $parameters = [
        [
            'name' => 'department',
            'label' => 'Department',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Departments',
            'remoteSource' => ['CompanyStructure', 'id', 'title'],
        ],
        [
            'name' => 'employee',
            'label' => 'Employee',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Employees',
            'remoteSource' => ['Employee', 'id', 'first_name+last_name'],
        ],
    ];

    public function getReportData(array $params): array
    {
        $leaveActionManager = new LeavesActionManager();
        $leaveActionManager->setBaseService(BaseService::getInstance());

        $department = $params['department'] ?? null;
        $employeeId = $params['employee'] ?? null;

        // Get employees based on filters
        $emp = new Employee();
        if (!empty($employeeId) && $employeeId !== 'NULL') {
            $employees = $emp->Find("id = ?", [$employeeId]);
        } elseif (!empty($department) && $department !== 'NULL') {
            $employees = $emp->Find("department = ? AND status = 'Active'", [$department]);
        } else {
            $employees = $emp->Find("status = 'Active'", []);
        }

        $reportData = [];
        $reportData[] = [
            "Employee ID",
            "Employee",
            "Leave Type",
            "Pending",
            "Approved",
            "Rejected",
            "Cancelled",
            "Available",
            "To be Accrued",
            "Carried Forward",
        ];

        foreach ($employees as $employee) {
            $leaveEntitlements = $leaveActionManager->getEntitlementByEmployee($employee)->getObject();
            foreach ($leaveEntitlements as $leaveEntitlement) {
                $reportData[] = [
                    $employee->employee_id,
                    $employee->first_name . " " . $employee->last_name,
                    $leaveEntitlement['name'],
                    $leaveEntitlement['pendingLeaves'] ?? 0,
                    $leaveEntitlement['approvedLeaves'] ?? 0,
                    $leaveEntitlement['rejectedLeaves'] ?? 0,
                    $leaveEntitlement['cancelRequestedLeaves'] ?? 0,
                    $leaveEntitlement['availableLeaves'] ?? 0,
                    $leaveEntitlement['tobeAccrued'] ?? 0,
                    $leaveEntitlement['carriedForward'] ?? 0,
                ];
            }
        }

        return $reportData;
    }
}
