<?php
if (!class_exists('JobsAdminManager')) {
	class JobsAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('JobTitle');
			$this->addModelClass('PayGrade');
			
		}
		
	}
}


if (!class_exists('JobTitle')) {
	class JobTitle extends ICEHRM_Record {
		var $_table = 'JobTitles';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}
}
	
if (!class_exists('PayGrade')) {
	class PayGrade extends ICEHRM_Record {
		var $_table = 'PayGrades';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array();
		}
	}
}