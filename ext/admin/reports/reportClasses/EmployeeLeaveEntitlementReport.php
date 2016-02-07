<?php
if(!interface_exists('ReportBuilderInterface')){
    include_once APP_BASE_PATH.'admin/reports/reportClasses/ReportBuilderInterface.php';
}

if(!class_exists('LeavesActionManager')){
    include_once APP_BASE_PATH.'modules/leaves/api/LeavesActionManager.php';
}
class EmployeeLeaveEntitlementReport implements ReportBuilderInterface{
    public function getData($report,$req){

        $leaveActionManager = new LeavesActionManager();

        $department = $req['department'];
        $employeeId = $req['employee'];

        if(($employeeId == "NULL" || empty($employeeId)) &&  ($department == "NULL" || empty($department))){
            $emp = new Employee();
            $employees = $emp->Find("status = 'Active'",array());
        }else if($employeeId != "NULL" && !empty($employeeId)){
            $emp = new Employee();
            $employees = $emp->Find("id = ?",array($employeeId));
        }else{
            $emp = new Employee();
            $employees = $emp->Find("department = ? and status = 'Active'",array($department));
        }


        $reportData = array();
        $reportData[] = array("Employee ID","Employee","Leave Type","Pending","Approved","Rejected","Canceled","Available","To be Accrued","Carried Forward from Previous Years");

        foreach($employees as $employee){
            $leaveEntitlements = $leaveActionManager->getEntitlementByEmployee($employee)->getObject();
            foreach($leaveEntitlements as $leaveEntitlement){
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