<?php
if (!class_exists('Time_sheetsModulesManager')) {
	
	class Time_sheetsModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			$this->addUserClass("EmployeeTimeSheet");
			$this->addUserClass("EmployeeTimeEntry");
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeTimeSheet');
			$this->addModelClass('EmployeeTimeEntry');
			
		}

	}
}

if (!class_exists('EmployeeTimeSheet')) {

	class EmployeeTimeSheet extends ICEHRM_Record {
		var $_table = 'EmployeeTimeSheets';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get","element");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save","delete");
		}
	}

	class EmployeeTimeEntry extends ICEHRM_Record {
		var $_table = 'EmployeeTimeEntry';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save","delete");
		}
		
		public function validateSave($obj){
			if(SettingsManager::getInstance()->getSetting("Attendance: Time-sheet Cross Check") == "1"){
				$attendance = new Attendance();
				$list = $attendance->Find("employee = ? and in_time <= ? and out_time >= ?",array($obj->employee,$obj->date_start,$obj->date_end));
				if(empty($list) || count($list) == 0){
					return new IceResponse(IceResponse::ERROR,"The time entry can not be added since you have not marked attendance for selected period");
				}	
			}
			return new IceResponse(IceResponse::SUCCESS,"");
		}
	}

}