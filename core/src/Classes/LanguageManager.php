<?php
namespace Classes;

use Gettext\Generators\Json;
use Gettext\Translations;
use Gettext\Translator;
use Metadata\Common\Model\SupportedLanguage;
use Utils\LogManager;

class LanguageManager
{
    private static $me = [];

    /* @var \Gettext\Translator $translator */
    private $translator = null;
    /* @var \Gettext\Translations $translations */
    private $translations = null;

    private function __construct()
    {
    }

    private static function getInstance($lang = null)
    {
        if ($lang === null) {
            $lang = self::getCurrentLang();
        }
        if (empty(self::$me[$lang])) {
            self::$me[$lang] = new LanguageManager();
            self::$me[$lang]->initialize($lang);
        }

        return self::$me[$lang];
    }

    private function initialize($lang)
    {
        $this->loadLanguage($lang);
    }

    public function loadLanguage($lang)
    {
        $this->translations = Translations::fromPoFile(APP_BASE_PATH.'lang/'.$lang.'.po');
        $t = new Translator();
        $t->loadTranslations($this->translations);
        $t->register();
        $this->translator = $t;
    }

    private static function getCurrentLang()
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
        return $lang;
    }

    public static function getTranslations($lang = null)
    {
        $me = self::getInstance($lang);
        return Json::toString($me->translations);
    }

    public static function getTranslationsObject($lang = null)
    {
        $me = self::getInstance($lang);
        return $me->translations;
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

        if (count($matches[0]) === 0) {
            return self::tran($string);
        }

        for ($i = 0; $i<count($matches[0]); $i++) {
            $tagVal = $matches[1][$i];
            $fullVal = $matches[0][$i];
            $string = str_replace($fullVal, $me::tran($tagVal), $string);
        }

        return $string;
    }
}
