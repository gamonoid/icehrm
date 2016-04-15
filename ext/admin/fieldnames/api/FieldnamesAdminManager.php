<?php
if (!class_exists('FieldnamesAdminManager')) {
    class FieldnamesAdminManager extends AbstractModuleManager{
        public function initializeUserClasses(){

        }

        public function initializeFieldMappings(){

        }

        public function initializeDatabaseErrorMappings(){

        }

        public function setupModuleClassDefinitions(){
            $this->addModelClass('FieldNameMapping');
            $this->addModelClass('CustomField');
        }
    }
}

if (!class_exists('FieldNameMapping')) {
    class FieldNameMapping extends ICEHRM_Record {
        var $_table = 'FieldNameMappings';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array();
        }

        public function getAnonymousAccess(){
            return array("get","element");
        }
    }
}

if (!class_exists('CustomField')) {
	class CustomField extends ICEHRM_Record {
		var $_table = 'CustomFields';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array();
		}

		public function getAnonymousAccess(){
			return array("get","element");
		}
	}
}