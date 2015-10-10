<?php
if (!class_exists('ProjectsAdminManager')) {
	class ProjectsAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
				
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){
            $this->addDatabaseErrorMapping("key 'EmployeeProjectsKey'", "Employee already added to this project");
		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('Client');
			$this->addModelClass('Project');
				
		}

	}
}


if (!class_exists('Client')) {
	class Client extends ICEHRM_Record {
		var $_table = 'Clients';
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
	
if (!class_exists('Project')) {
	class Project extends ICEHRM_Record {
		var $_table = 'Projects';
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get","element");
		}
	}
}