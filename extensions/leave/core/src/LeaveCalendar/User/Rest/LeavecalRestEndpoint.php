<?php

namespace LeaveCalendar\User\Rest;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\LeaveRequestService;
use Metadata\Common\Model\Country;
use Users\Common\Model\User;

class LeavecalRestEndpoint extends RestEndPoint
{
    public function getMonthlyLeaves(User $user, $year, $month)
    {
        $service = new LeaveRequestService();

        $start = $year.'-'.$month.'-01';
        $end = date('Y-m-t', strtotime($start));
        $monthlyLeaves = $service->getLeaveDays($start, $end);


		$employeeId = BaseService::getInstance()->getCurrentProfileId();
		$countryId = null;
		if (!empty($employeeId)) {
			$employee = new Employee();
			$employee->Load('id = ?', [$employeeId]);
			$country = new Country();
			$country->Load('code = ?', [$employee->country]);
			$countryId = $country->id;
		}

		$leaveUtil = new LeaveUtil();
		$holidays = $leaveUtil->getHolidays($start, $end, $countryId, $employeeId);

        return new IceResponse(
            IceResponse::SUCCESS,
            ['leave' => $monthlyLeaves, 'holidays' => $holidays]
        );
    }

    public function getYearlyLeaves(User $user, $year)
    {
        $service = new LeaveRequestService();
        $yearData = $service->getYearlyLeaveDays($year);

        return new IceResponse(
            IceResponse::SUCCESS,
            [$year => $yearData]
        );
    }
}
