<?php
if(!class_exists("TestTemplate")) {
    include dirname(__FILE__).'/../TestTemplate.php';
}

class LanguageManagerTest extends TestTemplate{
    var $obj = null;

    protected function setUp()
    {
        parent::setUp();

    }


    public function testSample(){
        $this->p("cat|".LanguageManager::tran('cat'));
        $this->p("cat|".LanguageManager::tran('cat'));

        $this->p("solid|".LanguageManager::tran('solid'));
        $this->p("solid|".LanguageManager::tran('solid'));


        $this->p("one file|".LanguageManager::tran('one file'));
        $this->p("2 files|".LanguageManager::tran('2 files'));
        
        $string = "wert <t>Logged In</t> rrr";
        
        $txt = LanguageManager::translateTnrText($string);

        $this->p("Tx1:".$txt);
    }
}