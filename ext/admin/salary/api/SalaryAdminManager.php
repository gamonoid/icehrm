<?php
if (!class_exists('SalaryAdminManager')) {
	class SalaryAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
            $this->addModelClass('SalaryComponentType');
            $this->addModelClass('SalaryComponent');
            $this->addModelClass('Deduction');
		}
		
	}
}

if (!class_exists('SalaryComponentType')) {
    class SalaryComponentType extends ICEHRM_Record {
        var $_table = 'SalaryComponentType';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array("get","element");
        }
    }
}

if (!class_exists('SalaryComponent')) {
    class SalaryComponent extends ICEHRM_Record {
        var $_table = 'SalaryComponent';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array("get","element");
        }
    }
}


if (!class_exists('PayrollEmployee')) {
    class PayrollEmployee extends ICEHRM_Record {
        var $_table = 'PayrollEmployees';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array("get","element");
        }
    }
}

