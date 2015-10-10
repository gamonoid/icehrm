<?php
if (!class_exists('UsersAdminManager')) {
	class UsersAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
				
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
				
			$this->addModelClass('User');
		}

	}
}

if (!class_exists('User')) {
	class User extends ICEHRM_Record {
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
	
		public function validateSave($obj){
			$userTemp = new User();
	
			if(empty($obj->id)){
				$users = $userTemp->Find("email = ?",array($obj->email));
				if(count($users) > 0){
					return new IceResponse(IceResponse::ERROR,"A user with same authentication email already exist");
				}
			}else{
				$users = $userTemp->Find("email = ? and id <> ?",array($obj->email, $obj->id));
				if(count($users) > 0){
					return new IceResponse(IceResponse::ERROR,"A user with same authentication email already exist");
				}
			}
	
			return new IceResponse(IceResponse::SUCCESS,"");
		}
	
		var $_table = 'Users';
	}	
}