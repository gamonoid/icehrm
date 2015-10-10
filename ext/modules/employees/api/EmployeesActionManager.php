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

class EmployeesActionManager extends SubActionManager{
	public function get($req){
		
		$employee = $this->baseService->getElement('Employee',$this->getCurrentProfileId(),$req->map,true);	
		
		$subordinate = new Employee();
		$subordinates = $subordinate->Find("supervisor = ?",array($employee->id));
		$employee->subordinates = $subordinates;
		
		$fs = FileService::getInstance();
		$employee = $fs->updateProfileImage($employee);
		
		if(!empty($employee->birthday)){
			$employee->birthday = date("F jS, Y",strtotime($employee->birthday));
		}
		
		if(!empty($employee->driving_license_exp_date)){
			$employee->driving_license_exp_date = date("F jS, Y",strtotime($employee->driving_license_exp_date));
		}
		
		if(!empty($employee->joined_date)){
			$employee->joined_date = date("F jS, Y",strtotime($employee->joined_date));
		}
		
		
		if(empty($employee->id)){
			return new IceResponse(IceResponse::ERROR,$employee);		
		}
		return new IceResponse(IceResponse::SUCCESS,array($employee,$this->getCurrentProfileId(),$this->user->employee));
	}
	
	public function deleteProfileImage($req){
		if($this->user->user_level == 'Admin' || $this->user->employee == $req->id){
			$fs = FileService::getInstance();
			$res = $fs->deleteProfileImage($req->id);
			return new IceResponse(IceResponse::SUCCESS,$res);	
		}
	}
	
	public function changePassword($req){
		
		if($this->getCurrentProfileId() != $this->user->employee || empty($this->user->employee)){
			return new IceResponse(IceResponse::ERROR,"You are not allowed to change passwords of other employees");
		}
		
		$user = $this->baseService->getElement('User',$this->user->id);
		if(empty($user->id)){
			return new IceResponse(IceResponse::ERROR,"Error occured while changing password");
		}
		$user->password = md5($req->pwd);
		$ok = $user->Save();
		if(!$ok){
			return new IceResponse(IceResponse::ERROR,$user->ErrorMsg());
		}
		return new IceResponse(IceResponse::SUCCESS,$user);
	}
}