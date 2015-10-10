<?php
if (!class_exists('QualificationsAdminManager')) {
	class QualificationsAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){

		}
		
		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('Skill');
			$this->addModelClass('Education');
			$this->addModelClass('Certification');
			$this->addModelClass('Language');
			
			
		}
	}
}


if (!class_exists('Skill')) {
	class Skill extends ICEHRM_Record {
		var $_table = 'Skills';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getManagerAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}

	class Education extends ICEHRM_Record {
		var $_table = 'Educations';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getManagerAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}

	class Certification extends ICEHRM_Record {
		var $_table = 'Certifications';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getManagerAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}

	class Language extends ICEHRM_Record {
		var $_table = 'Languages';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getManagerAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array();
		}
	}

}
