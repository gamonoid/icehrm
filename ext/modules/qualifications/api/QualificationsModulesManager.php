<?php
if (!class_exists('QualificationsModulesManager')) {
	
	class QualificationsModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeSkill");
				$this->addUserClass("EmployeeEducation");
				$this->addUserClass("EmployeeCertification");
				$this->addUserClass("EmployeeLanguage");
			}
			
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeSkill');
			$this->addModelClass('EmployeeEducation');
			$this->addModelClass('EmployeeCertification');
			$this->addModelClass('EmployeeLanguage');
			
			
		}

	}
}


if (!class_exists('EmployeeSkill')) {

	class EmployeeSkill extends ICEHRM_Record {
		var $_table = 'EmployeeSkills';

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
	}

	class EmployeeEducation extends ICEHRM_Record {
		var $_table = 'EmployeeEducations';

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
	}

	class EmployeeCertification extends ICEHRM_Record {
		var $_table = 'EmployeeCertifications';

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
	}

	class EmployeeLanguage extends ICEHRM_Record {
		var $_table = 'EmployeeLanguages';

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
	}
}