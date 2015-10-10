<?php
if (!class_exists('DocumentsModulesManager')) {
	
	class DocumentsModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeDocument");
			}		
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
		}

	}
}