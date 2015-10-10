<?php
if (!class_exists('DocumentsAdminManager')) {
	class DocumentsAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			$this->addFileFieldMapping('EmployeeDocument', 'attachment', 'name');
		}
		
		public function initializeDatabaseErrorMappings(){
			$this->addDatabaseErrorMapping('CONSTRAINT `Fk_EmployeeDocuments_Documents` FOREIGN KEY','Can not delete Document Type, users have already uploaded these types of documents');
		}
		
		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('Document');
			$this->addModelClass('EmployeeDocument');
		}
		
	}
}


if (!class_exists('Document')) {
	class Document extends ICEHRM_Record {
		var $_table = 'Documents';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
			
		public function getUserAccess(){
			return array();
		}
	}
}
	
if (!class_exists('EmployeeDocument')) {
	class EmployeeDocument extends ICEHRM_Record {
		var $_table = 'EmployeeDocuments';
			
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
			
		public function Insert(){
			if(empty($this->date_added)){
				$this->date_added = date("Y-m-d H:i:s");
			}
			return parent::Insert();
		}
	}
}