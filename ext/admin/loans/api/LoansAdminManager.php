<?php
if (!class_exists('LoansAdminManager')) {
	class LoansAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('CompanyLoan');
		}
		
	}
}

if (!class_exists('CompanyLoan')) {
	class CompanyLoan extends ICEHRM_Record {
		var $_table = 'CompanyLoans';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}
}