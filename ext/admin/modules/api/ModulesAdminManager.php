<?php
if (!class_exists('ModulesAdminManager')) {
	class ModulesAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
			$this->addModelClass('Module');
		}
		
	}
}

if (!class_exists('Module')) {
	class Module extends ICEHRM_Record {
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
		var $_table = 'Modules';
	}	
}