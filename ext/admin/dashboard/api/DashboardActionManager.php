<?php
/*
 This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
*/


class DashboardActionManager extends SubActionManager{
	
	public function getInitData($req){
		$data = array();
		$employees = new Employee();
		$data['numberOfEmployees'] = $employees->Count("1 = 1");
		
		$company = new CompanyStructure();
		$data['numberOfCompanyStuctures'] = $company->Count("1 = 1");
		
		$user = new User();
		$data['numberOfUsers'] = $user->Count("1 = 1");
		
		$project = new Project();
		$data['numberOfProjects'] = $project->Count("status = 'Active'");
		
		$attendance = new Attendance();
		$data['numberOfAttendanceLastWeek'] = $attendance->Count("in_time > '".date("Y-m-d H:i:s",strtotime("-1 week"))."'");
		
		$empLeave = new EmployeeLeave();
		$data['numberOfLeaves'] = $empLeave->Count("date_start > '".date("Y-m-d")."'");
		
		$timeEntry = new EmployeeTimeEntry();
		$data['numberOfAttendanceLastWeek'] = $attendance->Count("in_time > '".date("Y-m-d H:i:s",strtotime("-1 week"))."'");
		
		
		return new IceResponse(IceResponse::SUCCESS,$data);
		
	}
	
}