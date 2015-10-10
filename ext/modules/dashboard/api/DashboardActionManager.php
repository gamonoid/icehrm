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

include (APP_BASE_PATH."modules/leaves/api/LeavesActionManager.php");

class DashboardActionManager extends SubActionManager{
	
	public function getPendingLeaves($req){
		
		$lam = new LeavesActionManager();
		$leavePeriod = $lam->getCurrentLeavePeriod(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
		
		$leave = new EmployeeLeave();
		$pendingLeaves = $leave->Find("status = ? and employee = ?",array("Pending", $this->getCurrentProfileId()));
		
		return new IceResponse(IceResponse::SUCCESS,count($pendingLeaves));
		
	}
	
	public function getLastTimeSheetHours($req){
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("employee = ? order by date_end desc limit 1",array($this->getCurrentProfileId()));
		
		if(empty($timeSheet->employee)){
			return new IceResponse(IceResponse::SUCCESS,"0:00");
		}
		
		$timeSheetEntry = new EmployeeTimeEntry();
		$list = $timeSheetEntry->Find("timesheet = ?",array($timeSheet->id));
		
		$seconds = 0;
		foreach($list as $entry){
			$seconds += (strtotime($entry->date_end) - strtotime($entry->date_start));	
		}
		
		$minutes = (int)($seconds/60);
		$rem = $minutes % 60;
		$hours = ($minutes - $rem)/60;
		if($rem < 10){
			$rem ="0".$rem;
		}
		return new IceResponse(IceResponse::SUCCESS,$hours.":".$rem);
		
	}
	
	public function getEmployeeActiveProjects($req){
		$project = new EmployeeProject();
		$projects = $project->Find("employee = ? and status =?",array($this->getCurrentProfileId(),'Current'));
	
		
		return new IceResponse(IceResponse::SUCCESS,count($projects));
	
	}
	
	
}