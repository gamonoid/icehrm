<?php
namespace Test\Unit;

use Classes\LanguageManager;

class LanguageManagerUnit extends \TestTemplate
{

    protected function setUp()
    {
        parent::setUp();
    }
    
    public function testTran()
    {
        $this->assertEquals('cat', LanguageManager::tran('cat'));
        $this->assertEquals('solid', LanguageManager::tran('solid'));
        $this->assertEquals('one file', LanguageManager::tran('one file'));
        $this->assertEquals('2 files', LanguageManager::tran('2 files'));
        $this->assertEquals('User Logged In now', LanguageManager::translateTnrText('User <t>Logged In</t> now'));
        fwrite(STDOUT, __METHOD__ . " End\n");
    }
}
