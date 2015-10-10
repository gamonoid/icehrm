<?php
if (!class_exists('ProjectsModulesManager')) {
	
	class ProjectsModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeProject");
			}
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeProject');
			
		}

	}
}


if (!class_exists('EmployeeProject')) {

	class EmployeeProject extends ICEHRM_Record {
		var $_table = 'EmployeeProjects';

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