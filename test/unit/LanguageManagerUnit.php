<?php
namespace Test\Unit;

use Classes\LanguageManager;
use Gettext\Translations;

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

    public function testGetTranslations() 
    {

        $enCount = $this->getTranslationCount('en');

        $this->assertEquals($enCount, $this->getTranslationCount('de'));
        $this->assertEquals($enCount, $this->getTranslationCount('es'));
        $this->assertEquals($enCount, $this->getTranslationCount('fr'));
        $this->assertEquals($enCount, $this->getTranslationCount('it'));
        $this->assertEquals($enCount, $this->getTranslationCount('ja'));
        $this->assertEquals($enCount, $this->getTranslationCount('pl'));
        $this->assertEquals($enCount, $this->getTranslationCount('zh'));
    }

    private function getTranslationCount($lang) 
    {
        $trans = json_decode(LanguageManager::getTranslations($lang), true);
        $count = count(array_keys($trans['messages']['']));
    }
}
