<?php
namespace Charts\Admin\Rest;

use Attendance\Common\Model\AttendanceStatus;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Leaves\User\Api\LeavesActionManager;
use Users\Common\Model\User;
use Utils\LogManager;

class ChartsRestEndpoint extends RestEndPoint
{
    public function getCompanyLeaveEntitlement(User $user)
    {

        $data = [
            'pending' => 0,
            'approved' => 0,
            'remaining' => 0,
        ];

        $leaveActionManager = new LeavesActionManager();
        $employee = new Employee();
        $employees = $employee->Find('status = ?', ['Active']);
        foreach ($employees as $employee) {
            $leaveEntitlements = $leaveActionManager->getEntitlementByEmployee($employee)->getObject();
            foreach ($leaveEntitlements as $leaveEntitlement) {
                $data['pending'] += $leaveEntitlement['pendingLeaves'];
                $data['approved'] += $leaveEntitlement['approvedLeaves'];
                $data['remaining'] += $leaveEntitlement['totalLeaves']
                    - ( $leaveEntitlement['pendingLeaves'] + $leaveEntitlement['approvedLeaves']);
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    public function getEmployeeDistribution(User $user)
    {

        $data = [];
        $query = <<<'SQL'
    select c.title as name, count(e.id) as value 
    from Employees e
    left join CompanyStructures c 
    on e.department = c.id
    group by department
SQL;
        // $user->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        $rs = $user->DB()->Execute($query);

        foreach ($rs as $rowId => $row) {
            $name = $row['name'];
            $data[empty($name) ? 'Not Assigned' : $name] = $row['value'];
        }

        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    public function getCompanyEmployeeCheckIns(User $user)
    {

        $attendanceStatus = new AttendanceStatus();
        $attendanceStatusList = $attendanceStatus->Find("1 = 1");
        $countClockedIn = 0;
        $countClockedOut = 0;
        $countNotClockedIn = 0;
        foreach ($attendanceStatusList as $att) {
            if ($att->statusId === 1) {
                $countClockedOut++;
            } elseif ($att->statusId === 0) {
                $countClockedIn++;
            } else {
                $countNotClockedIn++;
            }
        }


        $data = [];
        $data['Checked-In'] = $countClockedIn;
        $data['Checked Out'] = $countClockedOut;
        $data['Not Started'] = $countNotClockedIn;

        return new IceResponse(IceResponse::SUCCESS, $data);
    }
}
