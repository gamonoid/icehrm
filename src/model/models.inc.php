<?php
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */


class File extends ICEHRM_Record {
	var $_table = 'Files';
	public function getAdminAccess(){
		return array("get","element","save","delete");
	}
	
	public function getUserAccess(){
		return array();
	}
	
	public function getAnonymousAccess(){
		return array("save");
	}
}



class Setting extends ICEHRM_Record {
	public function getAdminAccess(){
		return array("get","element","save","delete");
	}
	
	public function getUserAccess(){
		return array();
	}

	public function postProcessGetElement($obj){
		if($obj->name == 'Api: REST Api Token'){
			$user = BaseService::getInstance()->getCurrentUser();
			$dbUser = new User();
			$dbUser->Load("id = ?",array($user->id));
			$resp = RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
			$obj->value = $resp->getData();
		}
		return $obj;
	}

	var $_table = 'Settings';
}

class Report extends ICEHRM_Record {
	public function getAdminAccess(){
		return array("get","element","save","delete");
	}

	public function getManagerAccess(){
		return array("get","element","save","delete");
	}

	public function getUserAccess(){
		return array();
	}

	public function postProcessGetData($entry){
		$entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($entry->output).".png".'"/>';
		return $entry;
	}

	var $_table = 'Reports';
}


class ReportFile extends ICEHRM_Record {
	public function getAdminAccess(){
		return array("get","element","save","delete");
	}

	public function getManagerAccess(){
		return array("get","element","save","delete");
	}

	public function getUserOnlyMeAccess(){
		return array("get","element","delete");
	}

	public function getUserAccess(){
		return array("get");
	}

	public function postProcessGetData($entry){
		$data = explode(".",$entry->name);
		$entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($data[count($data)-1]).".png".'"/>';
		return $entry;
	}

	var $_table = 'ReportFiles';
}


class UserReport extends ICEHRM_Record {
	public function getAdminAccess(){
		return array("get","element","save","delete");
	}

	public function getManagerAccess(){
		return array("get","element","save","delete");
	}

	public function getUserAccess(){
		return array();
	}

	public function postProcessGetData($entry){
		$entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($entry->output).".png".'"/>';
		return $entry;
	}

	var $_table = 'UserReports';
}

class Audit extends ICEHRM_Record {
	var $_table = 'AuditLog';
}


class DataEntryBackup extends ICEHRM_Record {
	var $_table = 'DataEntryBackups';
}


class Notification extends ICEHRM_Record {
	var $_table = 'Notifications';
}

class RestAccessToken extends ICEHRM_Record {
	var $_table = 'RestAccessTokens';
}

class Cron extends ICEHRM_Record {
    var $_table = 'Crons';
}

class IceEmail extends ICEHRM_Record {
    var $_table = 'Emails';
}

class StatusChangeLog extends ICEHRM_Record {
	var $_table = 'StatusChangeLogs';
}



