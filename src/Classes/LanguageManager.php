<?php
namespace Classes;

use Gettext\Generators\Json;
use Gettext\Translations;
use Gettext\Translator;
use Metadata\Common\Model\SupportedLanguage;
use Utils\LogManager;

class LanguageManager
{
    private static $me = null;

    /* @var \Gettext\Translator $translator */
    private $translator = null;
    private $translations = null;

    private function __construct()
    {
    }

    private static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new LanguageManager();
            self::$me->loadLanguage();
        }

        return self::$me;
    }

    private function loadLanguage()
    {
        $lang = $this->getCurrentLang();
        $this->translations = Translations::fromPoFile(APP_BASE_PATH.'lang/'.$lang.'.po');
        if (file_exists(APP_BASE_PATH.'lang/'.$lang.'-ext.po')) {
            $this->translations->addFromPoFile(APP_BASE_PATH.'lang/'.$lang.'-ext.po');
        }
        $t = new Translator();
        $t->loadTranslations($this->translations);
        $t->register();
        $this->translator = $t;
    }

    private function getCurrentLang()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user) || empty($user->lang) || $user->lang == "NULL") {
            $lang = SettingsManager::getInstance()->getSetting('System: Language');
            LogManager::getInstance()->info("System Lang:".$lang);
        } else {
            $supportedLang = new SupportedLanguage();
            $supportedLang->Load("id = ?", array($user->lang));
            $lang = $supportedLang->name;
        }
        if (empty($lang) || !file_exists(APP_BASE_PATH.'lang/'.$lang.'.po')) {
            $lang = 'en';
        }
        LogManager::getInstance()->info("Current Language:".$lang);
        return $lang;
    }

    public static function getTranslations()
    {
        $me = self::getInstance();
        return Json::toString($me->translations);
    }

    public static function tran($text)
    {
        $me = self::getInstance();
        return $me->translator->gettext($text);
    }

    public static function translateTnrText($string)
    {
        $me = self::getInstance();
        $pattern = "#<t>(.*?)</t>#";
        preg_match_all($pattern, $string, $matches);

        for ($i = 0; $i<count($matches[0]); $i++) {
            $tagVal = $matches[1][$i];
            $fullVal = $matches[0][$i];
            $string = str_replace($fullVal, $me::tran($tagVal), $string);
        }

        return $string;
    }
}
