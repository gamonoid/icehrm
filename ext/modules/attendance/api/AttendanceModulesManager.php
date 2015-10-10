<?php
if (!class_exists('AttendanceModulesManager')) {
	class AttendanceModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("Attendance");
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