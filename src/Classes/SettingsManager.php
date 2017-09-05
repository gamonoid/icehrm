<?php
namespace Classes;

use Model\Setting;

class SettingsManager
{

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new SettingsManager();
        }

        return self::$me;
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
        if ($setting->name == $name) {
            return $setting->value;
        }
        return null;
    }

    public function setSetting($name, $value)
    {
        $setting = new Setting();
        $setting->Load("name = ?", array($name));
        if ($setting->name == $name) {
            $setting->value = $value;
            $setting->Save();
        }
    }

    public function addSetting($name, $value)
    {
        $setting = new Setting();
        $setting->Load("name = ?", array($name));
        if ($setting->name == $name) {
            $setting->value = $value;
            $setting->Save();
        } else {
            $setting->name = $name;
            $setting->value = $value;
            $setting->description = $value;
            $setting->meta = '';
            $setting->Save();
        }
    }
}
