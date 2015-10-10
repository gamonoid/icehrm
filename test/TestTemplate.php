<?php
include dirname(__FILE__).'/test.includes.php';
include APP_BASE_PATH.'admin/users/api/UsersAdminManager.php';

class TestTemplate extends PHPUnit_Framework_TestCase{
	
	protected $usersArray = array();
	
	protected function setUp()
	{
		parent::setUp();
	
		$this->deleteAllUsers();
		$this->createNewUsers();
		
		SessionUtils::saveSessionObject('user', $this->usersArray['admin']);
		
	}
	
	protected function deleteAllUsers(){
		$user = new User();
		$users = $user->Find("username <> ?",array('admin'));
		foreach($users as $user){
			$user->Delete();
		}
	}
	
	protected function createNewUsers(){
		
		$profileVar = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
		$profileClass = ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME);
		
		$user = new User();
		$user->username = 'manager';
		$user->email = 'manager@icehrm-test.com';
		$user->password = '21232f297a57a5a743894a0e4a801fc3';
		$user->user_level = 'Manager';
		$user->Save();
		
		$this->usersArray[$user->username] = $user;
		
		$user = new User();
		$user->username = 'profile';
		$user->email = 'profile@icehrm-test.com';
		$user->password = '21232f297a57a5a743894a0e4a801fc3';
		$user->user_level = 'Profile';
		$user->Save();
		
		$this->usersArray[$user->username] = $user;
		
		
		$user = new User();
		$user->Load("username = ?",array('admin'));
		$this->usersArray[$user->username] = $user;
	}
	
}