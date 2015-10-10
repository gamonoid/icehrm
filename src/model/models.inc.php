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
	var $_table = 'Reports';
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



