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

class Time_sheetsInitialize extends AbstractInitialize{
	
	public function init(){
		//Add Employee time sheets if it is not already created for current week
		$empId = $this->getCurrentProfileId();
		if(date('w', strtotime("now")) == 0) {
			$start = date("Y-m-d", strtotime("now"));
		}else{
			$start = date("Y-m-d", strtotime("last Sunday"));
		}
		
		if(date('w', strtotime("now")) == 6) {
			$end = date("Y-m-d", strtotime("now"));
		}else{
			$end = date("Y-m-d", strtotime("next Saturday"));
		}
		
		
		
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("employee = ? and date_start = ? and date_end = ?",array($empId,$start,$end));
		if($timeSheet->date_start == $start && $timeSheet->employee == $empId){
				
		}else{
			if(!empty($empId)){
				$timeSheet->employee = $empId;
				$timeSheet->date_start = $start;
				$timeSheet->date_end = $end;
				$timeSheet->status = "Pending";
				$ok = $timeSheet->Save();
				if(!$ok){
					LogManager::getInstance()->info("Error creating time sheet : ".$timeSheet->ErrorMsg());
				}	
			}
				
		}
		
		
		//Generate missing timesheets
		
		
	}
	
}