<?php
if (!class_exists('LoansModulesManager')) {
	
	class LoansModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeCompanyLoan");
			}
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeCompanyLoan');
			
		}

	}
}


if (!class_exists('EmployeeCompanyLoan')) {

	class EmployeeCompanyLoan extends ICEHRM_Record {
		var $_table = 'EmployeeCompanyLoans';

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
			return array("get","element");
		}
	}
}