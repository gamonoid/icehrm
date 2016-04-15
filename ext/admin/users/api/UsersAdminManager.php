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

        public function getDashboardItemData(){
            $data = array();
            $user = new User();
            $data['numberOfUsers'] = $user->Count("1 = 1");
            return $data;

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

                //Check if you are trying to change user level
                $oldUser = new User();
                $oldUser->Load("id = ?",array($obj->id));
                if($oldUser->user_level != $obj->user_level && $oldUser->user_level == 'Admin'){
                    $adminUsers = $userTemp->Find("user_level = ?",array("Admin"));
                    if(count($adminUsers) == 1 && $adminUsers[0]->id == $obj->id){
                        return new IceResponse(IceResponse::ERROR,"You are the only admin user for the application. You are not allowed to revoke your admin rights");
                    }
                }


			}

            //Check if the user have rights to the default module
            if(!empty($obj->default_module)){
                $module = new Module();
                $module->Load("id = ?",array($obj->default_module));
                if($module->mod_group == "user"){
                    $module->mod_group = "modules";
                }
                $moduleManager = BaseService::getInstance()->getModuleManager($module->mod_group, $module->name);
                if(!BaseService::getInstance()->isModuleAllowedForGivenUser($moduleManager, $obj)){
                    return new IceResponse(IceResponse::ERROR,"This module can not be set as the default module for the user since the user do not have access to this module");
                }

            }


	
			return new IceResponse(IceResponse::SUCCESS,"");
		}

	
		var $_table = 'Users';
	}	
}

if (!class_exists('UserRole')) {
    class UserRole extends ICEHRM_Record {
        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array();
        }

        var $_table = 'UserRoles';
    }
}