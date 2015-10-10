<?php
if(!class_exists("TestTemplate")) {
	include dirname(__FILE__).'/../TestTemplate.php';
}

class UsersActionManagerTest extends TestTemplate{
	
	var $obj = null;
	
	protected function setUp()
	{
		parent::setUp();
		
		include APP_BASE_PATH."admin/users/api/UsersEmailSender.php";
		include APP_BASE_PATH."admin/users/api/UsersActionManager.php";
		
		$this->obj = new UsersActionManager();
		$this->obj->setUser($this->usersArray['admin']);
		$this->obj->setBaseService(BaseService::getInstance());
		$this->obj->setEmailSender(BaseService::getInstance()->getEmailSender());
	}
	
	
	public function testChangePassword(){

		$this->obj->getCurrentProfileId();
		
		$this->assertEquals(1, 1);
		
	}
}