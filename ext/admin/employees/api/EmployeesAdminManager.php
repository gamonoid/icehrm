<?php
if (!class_exists('EmployeesAdminManager')) {
	class EmployeesAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
				
		}

		public function initializeFieldMappings(){
			
		}

		public function initializeDatabaseErrorMappings(){
			$this->addDatabaseErrorMapping('CONSTRAINT `Fk_User_Employee` FOREIGN KEY',"Can not delete Employee, please delete the User for this employee first.");
			$this->addDatabaseErrorMapping("Duplicate entry|for key 'employee'","A duplicate entry found");
		}

		public function setupModuleClassDefinitions(){
			$this->addModelClass('Employee');
			$this->addModelClass('EmploymentStatus');
		}

	}
}

if (!class_exists('Employee')) {
	class Employee extends ICEHRM_Record {
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getManagerAccess(){
			return array("get","element","save");
		}
	
		public function getUserAccess(){
			return array("get");
		}
	
		public function getUserOnlyMeAccess(){
			return array("element","save");
		}
	
		public function getUserOnlyMeAccessField(){
			return "id";
		}
	
		var $_table = 'Employees';
	}
}

if (!class_exists('EmploymentStatus')) {
	class EmploymentStatus extends ICEHRM_Record {
		
		var $_table = 'EmploymentStatus';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
		
		public function getManagerAccess(){
			return array("get","element","save");
		}
	
		public function getUserAccess(){
			return array();
		}
	}
}

