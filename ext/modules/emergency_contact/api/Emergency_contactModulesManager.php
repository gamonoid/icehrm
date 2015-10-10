<?php
if (!class_exists('Emergency_contactModulesManager')) {
	
	class Emergency_contactModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmergencyContact");
			}
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmergencyContact');

		}

	}
}

if (!class_exists('EmergencyContact')) {
	class EmergencyContact extends ICEHRM_Record {
		var $_table = 'EmergencyContacts';

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