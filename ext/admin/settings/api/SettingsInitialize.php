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

class SettingsInitialize extends AbstractInitialize{
	
	public function init(){
		if(SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1"){
			$user = BaseService::getInstance()->getCurrentUser();
			$dbUser = new User();
			$dbUser->Load("id = ?",array($user->id));
			$resp = RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
			if($resp->getStatus() != IceResponse::SUCCESS){
				LogManager::getInstance()->error("Error occured while creating REST Api acces token for ".$user->username);
			}
		}
		
	}
	
}