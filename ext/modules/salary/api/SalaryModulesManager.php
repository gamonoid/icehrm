<?php
if (!class_exists('SalaryModulesManager')) {
	
	class SalaryModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeSalary");
			}
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeSalary');
			
		}

	}
}


if (!class_exists('EmployeeSalary')) {

	class EmployeeSalary extends ICEHRM_Record {
		var $_table = 'EmployeeSalary';

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