<?php
namespace Reports\Admin\Reports;

use Employees\Common\Model\Employee;
use Leaves\User\Api\LeavesActionManager;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;

class EmployeeLeaveEntitlementReport extends ClassBasedReportBuilder implements ReportBuilderInterface
{
    public function getData($report, $req)
    {

        $leaveActionManager = new LeavesActionManager();

        $department = $req['department'];
        $employeeId = $req['employee'];

        if (($employeeId == "NULL" || empty($employeeId)) &&  ($department == "NULL" || empty($department))) {
            $emp = new Employee();
            $employees = $emp->Find("status = 'Active'", array());
        } elseif ($employeeId != "NULL" && !empty($employeeId)) {
            $emp = new Employee();
            $employees = $emp->Find("id = ?", array($employeeId));
        } else {
            $emp = new Employee();
            $employees = $emp->Find("department = ? and status = 'Active'", array($department));
        }

        $reportData = array();
        $reportData[] = array(
            "Employee ID",
            "Employee","Leave Type","Pending","Approved","Rejected",
            "Canceled","Available","To be Accrued","Carried Forward from Previous Years");

        foreach ($employees as $employee) {
            $leaveEntitlements = $leaveActionManager->getEntitlementByEmployee($employee)->getObject();
            foreach ($leaveEntitlements as $leaveEntitlement) {
                $reportData[] = array(
                    $employee->employee_id,
                    $employee->first_name." ".$employee->last_name,
                    $leaveEntitlement['name'],
                    $leaveEntitlement['pendingLeaves'],
                    $leaveEntitlement['approvedLeaves'],
                    $leaveEntitlement['rejectedLeaves'],
                    $leaveEntitlement['cancelRequestedLeaves'],
                    $leaveEntitlement['availableLeaves'],
                    $leaveEntitlement['tobeAccrued'],
                    $leaveEntitlement['carriedForward']
                );
            }
        }

        return $reportData;
    }
}
