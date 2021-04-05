<?php
namespace Classes;

use Classes\Crypt\AesCtr;
use Model\Setting;

class SettingsManager
{

    const ENCRYPTED_PREFIX = 'iceenc_';

    private static $me = null;

    private $encryptedSettings = [];

    private function __construct()
    {
        $this->addEncryptedSetting('SAML: X.509 Certificate');
        $this->addEncryptedSetting('LDAP: Manager Password');
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new SettingsManager();
        }

        return self::$me;
    }

    public function addEncryptedSetting($name)
    {
        if (!$this->isEncryptedSetting($name)) {
            $this->encryptedSettings[] = $name;
        }
    }

    public function isEncryptedSetting($name)
    {
        return in_array($name, $this->encryptedSettings);
    }

    public function getInstanceKey()
    {
        $settings = new Setting();
        $settings->Load("name = ?", array("Instance: Key"));
        if ($settings->name != "Instance: Key") {
            return null;
        }
        return $settings->value;
    }

    private function encrypt($value)
    {
        $id = BaseService::getInstance()->getInstanceId();
        $key = $this->getInstanceKey();
        return AesCtr::encrypt($value, $id.$key, 256);
    }

    public function encryptSetting($name, $value)
    {
        // check the existence of prefix and encrypt only if need to avoid double encryption
        if ($this->isEncryptedSetting($name)
            && substr($value, 0, strlen(self::ENCRYPTED_PREFIX)) !== self::ENCRYPTED_PREFIX
        ) {
            $value = self::ENCRYPTED_PREFIX.$this->encrypt($value);
        }

        return $value;
    }

    private function decrypt($value)
    {
        $id = BaseService::getInstance()->getInstanceId();
        $key = $this->getInstanceKey();
        return AesCtr::decrypt($value, $id.$key, 256);
    }

    public function decryptSetting($name, $value)
    {
        if ($this->isEncryptedSetting($name)
            && substr($value, 0, strlen(self::ENCRYPTED_PREFIX)) === self::ENCRYPTED_PREFIX
        ) {
            $value = $this->decrypt(substr($value, strlen(self::ENCRYPTED_PREFIX)));
        }

        return $value;
    }

    public function getSetting($name)
    {
        if (class_exists("\\Classes\\ProVersion")) {
            $pro = new ProVersion();
            $val =  $pro->getSetting($name);
            if (!empty($val)) {
                return $val;
            }
        }

        $setting = new Setting();
        $setting->Load("name = ?", array($name));
        $value = null;
        if ($setting->name == $name) {
            $value = $setting->value;
        }

        if (null === $value) {
            return null;
        }

        return $this->decryptSetting($name, $value);
    }

    public function setSetting($name, $value)
    {
        $setting = new Setting();
        $setting->Load("name = ?", array($name));
        if ($setting->name !== $name) {
            return;
        }

        $setting->value = $this->encryptSetting($name, $value);
        $setting->Save();
    }

    public function addSetting($name, $value)
    {
        $setting = new Setting();
        $setting->Load("name = ?", array($name));
        if ($setting->name == $name) {
            $setting->value = $this->encryptSetting($name, $value);
            $setting->Save();
        } else {
            $setting->name = $name;
            $setting->value = $this->encryptSetting($name, $value);
            $setting->description = '';
            $setting->meta = '';
            $setting->Save();
        }
    }

    public function getDeprecatedSettings()
    {
        return [
            'Attendance: Work Week Start Day',
            'Attendance: Overtime Calculation Class'
        ];
    }
}
