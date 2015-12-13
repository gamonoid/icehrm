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
	
	public function getPunch($req){
		$date = $req->date;
		$arr = explode(" ",$date);
		$date = $arr[0];
		
		$employee = $this->baseService->getElement('Employee',$this->getCurrentProfileId(),null,true);
		
		//Find any open punch
		$attendance = new Attendance();
		$attendance->Load("employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ? and (out_time is NULL or out_time = '0000-00-00 00:00:00')",array($employee->id,$date));

		if($attendance->employee == $employee->id){
			//found an open punch
			return new IceResponse(IceResponse::SUCCESS,$attendance);
		}else{
			return new IceResponse(IceResponse::SUCCESS,null);
		}
		
		
	}
	
	
	public function savePunch($req){

        $useServerTime = SettingsManager::getInstance()->getSetting('Attendance: Use Department Time Zone');
        $currentEmployeeTimeZone = BaseService::getInstance()->getCurrentEmployeeTimeZone();

        if($useServerTime == '1' && !empty($currentEmployeeTimeZone)){
            date_default_timezone_set('Asia/Colombo');

            $date = new DateTime("now", new DateTimeZone('Asia/Colombo'));

            $date->setTimezone(new DateTimeZone($currentEmployeeTimeZone));
            $req->time = $date->format('Y-m-d H:i:s');
        }

		$req->date = $req->time;

        //check if there is an open punch
		$openPunch = $this->getPunch($req)->getData();
		
		if(empty($openPunch)){
			$openPunch = new Attendance();
		}

		$dateTime = $req->date;
		$arr = explode(" ",$dateTime);
		$date = $arr[0];
		

	
		$employee = $this->baseService->getElement('Employee',$this->getCurrentProfileId(),null,true);
		
		//check if dates are differnet
		$arr = explode(" ",$openPunch->in_time);
		$inDate = $arr[0];
		if(!empty($openPunch->in_time) && $inDate != $date){
			return new IceResponse(IceResponse::ERROR,"Attendance entry should be within a single day");
		}
		
		//compare dates
		if(!empty($openPunch->in_time) && strtotime($dateTime) <= strtotime($openPunch->in_time)){
			return new IceResponse(IceResponse::ERROR,"Punch-in time should be lesser than Punch-out time");
		}
	
		//Find all punches for the day
		$attendance = new Attendance();
		$attendanceList = $attendance->Find("employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ?",array($employee->id,$date));
		
		foreach($attendanceList as $attendance){
			if(!empty($openPunch->in_time)){
				if($openPunch->id == $attendance->id){
					continue;
				}
				if(strtotime($attendance->out_time) >= strtotime($dateTime) && strtotime($attendance->in_time) <= strtotime($dateTime)){
					//-1---0---1---0 || ---0--1---1---0
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}else if(strtotime($attendance->out_time) >= strtotime($openPunch->in_time) && strtotime($attendance->in_time) <= strtotime($openPunch->in_time)){
					//---0---1---0---1 || ---0--1---1---0
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}else if(strtotime($attendance->out_time) <= strtotime($dateTime) && strtotime($attendance->in_time) >= strtotime($openPunch->in_time)){
					//--1--0---0--1--
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one ".$attendance->id);
				}	
			}else{
				if(strtotime($attendance->out_time) >= strtotime($dateTime) && strtotime($attendance->in_time) <= strtotime($dateTime)){
					//---0---1---0 
					return new IceResponse(IceResponse::ERROR,"Time entry is overlapping with an existing one");
				}	
			}
		}
		if(!empty($openPunch->in_time)){
			$openPunch->out_time = $dateTime;
			$openPunch->note = $req->note;
			$this->baseService->audit(IceConstants::AUDIT_ACTION, "Punch Out \ time:".$openPunch->out_time);
		}else{
			$openPunch->in_time = $dateTime;
			$openPunch->out_time = '0000-00-00 00:00:00';
			$openPunch->note = $req->note;
			$openPunch->employee = $employee->id;
			$this->baseService->audit(IceConstants::AUDIT_ACTION, "Punch In \ time:".$openPunch->in_time);
		}
		$ok = $openPunch->Save();
		
		if(!$ok){
			LogManager::getInstance()->info($openPunch->ErrorMsg());
			return new IceResponse(IceResponse::ERROR,"Error occured while saving attendance");
		}
		return new IceResponse(IceResponse::SUCCESS,$openPunch);
	
	}
	
}