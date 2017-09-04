<?php
if(!class_exists("TestTemplate")) {
    include dirname(__FILE__).'/../TestTemplate.php';
}

class LanguageManagerTest extends TestTemplate{

    protected function setUp()
    {
        parent::setUp();

    }


    public function testTran(){
        $this->assertEquals('cat', \Classes\LanguageManager::tran('cat'));
        $this->assertEquals('solid', \Classes\LanguageManager::tran('solid'));
        $this->assertEquals('one file', \Classes\LanguageManager::tran('one file'));
        $this->assertEquals('2 files', \Classes\LanguageManager::tran('2 files'));
        $this->assertEquals('User Logged In now', \Classes\LanguageManager::translateTnrText('User <t>Logged In</t> now'));
    }
}
