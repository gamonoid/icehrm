<?php
if (!class_exists('PermissionsAdminManager')) {
	class PermissionsAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
			$this->addModelClass('Permission');
		}
		
	}
}

if (!class_exists('Permission')) {
	class Permission extends ICEHRM_Record {
		var $_table = 'Permissions';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
	}
}