<?php

namespace Test\Unit;

use Classes\PasswordManager;

class PasswordManagerUnit extends \TestTemplate
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testVerifyCorrectPasswordBCrypt()
    {
        $password = 'tester-password327Red';
        $hash = PasswordManager::createPasswordHash($password);
        $this->assertTrue(PasswordManager::verifyPassword($password, $hash));
    }

    public function testVerifyWrongPasswordBCrypt()
    {
        $password = 'tester-password327Red';
        $hash = PasswordManager::createPasswordHash($password);
        $this->assertFalse(PasswordManager::verifyPassword($password.'W', $hash));
    }

    public function testVerifyCorrectPasswordMD5()
    {
        $hash = '21232f297a57a5a743894a0e4a801fc3';
        $this->assertTrue(PasswordManager::verifyPassword('admin', $hash));

        $hash = '4048bb914a704a0728549a26b92d8550';
        $this->assertTrue(PasswordManager::verifyPassword('demouserpwd', $hash));
    }

    public function testVerifyWrongPasswordMD5()
    {
        $hash = '21232f297a57a5a743894a0e4a801fc4';
        $this->assertFalse(PasswordManager::verifyPassword('admin', $hash));

        $hash = '4048bb914a704a0728549a26b92d8550';
        $this->assertFalse(PasswordManager::verifyPassword('demouserpwd1', $hash));
    }
}
