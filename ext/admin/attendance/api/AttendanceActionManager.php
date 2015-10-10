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

class AttendanceActionManager extends SubActionManager{
	
	public function savePunch($req){
		
		
		$employee = $this->baseService->getElement('Employee',$req->employee,null,true);
		$inDateTime = $req->in_time;
		$inDateArr = explode(" ",$inDateTime);
		$inDate = $inDateArr[0];
		$outDateTime = $req->out_time;
		$outDate = "";
		if(!empty($outDateTime)){
			$outDateArr = explode(" ",$outDateTime);
			$outDate = $outDateArr[0];
		}
		
		$note = $req->note;
		
		//check if dates are differnet
		if(!empty($outDate) && $inDate != $outDate){
			return new IceResponse(IceResponse::ERROR,"Attendance entry should be within a single day");
		}
		
		//compare dates
		if(!empty($outDateTime) && strtotime($outDateTime) <= strtotime($inDateTime)){
			return new IceResponse(IceResponse::ERROR,"Punch-in time should be lesser than Punch-out time");
		}
	
		
		//Find all punches for the day
		$attendance = new Attendance();
		$attendanceList = $attendance->Find("employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ?",array($employee->id,$inDate));
		
		foreach($attendanceList as $attendance){
			if(!empty($req->id) && $req->id == $attendance->id){
				continue;
			}
			if(empty($attendance->out_time) || $attendance->out_time == "0000-00-00 00:00:00"){
				return new IceResponse(IceResponse::ERROR,"There is a non closed attendance entry for today. Please mark punch-out time of the open entry before adding a new one");
			}else if(!empty($outDateTime)){
				if(strtotime($attendance->out_time) >= strtotime($outDateTime) && strtotime($attendance->in_time) <= strtotime($outDateTime)){
					//-1---0---1---0 || ---0--1---1---0
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}else if(strtotime($attendance->out_time) >= strtotime($inDateTime) && strtotime($attendance->in_time) <= strtotime($inDateTime)){
					//---0---1---0---1 || ---0--1---1---0
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}else if(strtotime($attendance->out_time) <= strtotime($outDateTime) && strtotime($attendance->in_time) >= strtotime($inDateTime)){
					//--1--0---0--1--
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}	
			}else{
				if(strtotime($attendance->out_time) >= strtotime($inDateTime) && strtotime($attendance->in_time) <= strtotime($inDateTime)){
					//---0---1---0 
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}	
			}
		}
		
		$attendance = new Attendance();
		if(!empty($req->id)){
			$attendance->Load("id = ?",array($req->id));
		}
		$attendance->in_time = $inDateTime;
		if(empty($outDateTime)){
			$attendance->out_time = "0000-00-00 00:00:00";
		}else{
			$attendance->out_time = $outDateTime;
		}
		
		$attendance->employee = $req->employee;
		$attendance->note = $note;
		$ok = $attendance->Save();
		if(!$ok){
			LogManager::getInstance()->info($attendance->ErrorMsg());
			return new IceResponse(IceResponse::ERROR,"Error occured while saving attendance");
		}
		return new IceResponse(IceResponse::SUCCESS,$attendance);
		
	}
	
	
}