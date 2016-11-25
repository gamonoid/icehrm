<?php

if(!class_exists("TestTemplate")) {
    include dirname(__FILE__).'/../TestTemplate.php';
}

class FileServiceTest extends TestTemplate{
    var $obj = null;

    protected function setUp()
    {
        parent::setUp();

    }


    public function testSample(){
        $this->assertEquals(1, 1);

    }
}