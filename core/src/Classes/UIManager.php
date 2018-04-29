<?php
namespace Classes;

use Classes\ModuleBuilder\ModuleBuilder;

class UIManager
{

    private static $me = null;

    protected $user;
    protected $currentProfile;
    protected $switchedProfile;

    protected $currentProfileBlock = null;
    protected $switchedProfileBlock = null;

    protected $templates = array();

    protected $quickAccessMenuItems = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new UIManager();
        }

        return self::$me;
    }

    private function getTemplate($name, $type)
    {

        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        $this->templates[$name] = file_get_contents(APP_BASE_PATH."templates/".$type."/".$name.".html");

        return $this->templates[$name];
    }

    public function populateTemplate($name, $type, $params)
    {
        $template = $this->getTemplate($name, $type);
        foreach ($params as $key => $value) {
            $template = str_replace("#_".$key."_#", $value, $template);
        }

        return LanguageManager::translateTnrText($template);
    }

    public function setCurrentUser($user)
    {
        $this->user = $user;
    }

    public function setHomeLink($homeLink)
    {
        $this->homeLink = $homeLink;
    }

    public function setProfiles($profileCurrent, $profileSwitched)
    {
        $this->currentProfile = $profileCurrent;
        $this->switchedProfile = $profileSwitched;

        if (!empty($profileCurrent) && !empty($profileSwitched)) {
            $this->currentProfileBlock = array(
                "profileImage"=>$profileCurrent->image,
                "firstName"=>$profileCurrent->first_name,
                "lastName"=>$profileCurrent->last_name
            );

            $this->switchedProfileBlock = array(
                "profileImage"=>$profileSwitched->image,
                "firstName"=>$profileSwitched->first_name,
                "lastName"=>$profileSwitched->last_name
            );
        } elseif (!empty($profileCurrent)) {
            $this->currentProfileBlock = array(
                "profileImage"=>$profileCurrent->image,
                "firstName"=>$profileCurrent->first_name,
                "lastName"=>$profileCurrent->last_name
            );
        } elseif (!empty($profileSwitched)) {
            $this->currentProfileBlock = array(
                "profileImage"=>BASE_URL."images/user_male.png",
                "firstName"=>$this->user->username,
                "lastName"=>""
            );

            $this->switchedProfileBlock = array(
                "profileImage"=>$profileSwitched->image,
                "firstName"=>$profileSwitched->first_name,
                "lastName"=>$profileSwitched->last_name
            );
        } else {
            $this->currentProfileBlock = array(
                "profileImage"=>BASE_URL."images/user_male.png",
                "firstName"=>$this->user->username,
                "lastName"=>""
            );
        }
    }

    public function getProfileBlocks()
    {
        $tempateProfileBlock = $this->populateTemplate('profile_info', 'app', $this->currentProfileBlock);
        if (!empty($this->switchedProfileBlock)) {
            $tempateProfileBlock
                .= $this->populateTemplate('switched_profile_info', 'app', $this->switchedProfileBlock);
        }
        return $tempateProfileBlock;
    }

    public function getMenuBlocks()
    {
        $manuItems = array();

        if (!empty($this->quickAccessMenuItems)) {
            $itemsHtml = $this->getQuickAccessMenuItemsHTML();
            if (!empty($itemsHtml)) {
                $manuItems[] = new MenuItemTemplate('menuButtonQuick', array("ITEMS"=>$itemsHtml));
            }
        }

        $manuItems[] = new MenuItemTemplate('menuButtonNotification', array());
        if ($this->user->user_level == "Admin") {
            $manuItems[] = new MenuItemTemplate('menuButtonSwitchProfile', array());
        }

        if (!empty($this->currentProfile)) {
            $manuItems[] = new MenuItemTemplate('menuButtonProfile', array(
                "profileImage"=>$this->currentProfile->image,
                "firstName"=>$this->currentProfile->first_name,
                "lastName"=>$this->currentProfile->last_name,
                "homeLink"=>$this->homeLink,
                "CLIENT_BASE_URL"=>CLIENT_BASE_URL

            ));
        } else {
            $manuItems[] = new MenuItemTemplate('menuButtonProfile', array(
                "profileImage"=>BASE_URL."images/user_male.png",
                "firstName"=>$this->user->username,
                "lastName"=>"",
                "homeLink"=>$this->homeLink,
                "CLIENT_BASE_URL"=>CLIENT_BASE_URL

            ));
        }

        if ($this->user->user_level == "Admin") {
            $other = '';
            if (class_exists('\\Classes\\ProVersion')) {
                $pro = new ProVersion();
                if (method_exists($pro, 'getDetails')) {
                    $other = $pro->getDetails();
                }
            }

            $manuItems[] = new MenuItemTemplate('menuButtonHelp', array(
                "APP_NAME"=>APP_NAME,
                "VERSION"=>VERSION,
                "VERSION_DATE"=>VERSION_DATE,
                "OTHER"=>$other
            ));
        }

        return $manuItems;
    }

    public function getMenuItemsHTML()
    {
        $menuItems = $this->getMenuBlocks();
        $menuHtml = "";
        foreach ($menuItems as $item) {
            $menuHtml.=$item->getHtml();
        }

        return $menuHtml;
    }

    public function addQuickAccessMenuItem($name, $icon, $link, $userLevels = array())
    {
        $newName = LanguageManager::tran($name);
        $this->quickAccessMenuItems[] = array($newName, $icon, $link, $userLevels);
    }

    public function getQuickAccessMenuItemsHTML()
    {
        $html = "";
        $user = BaseService::getInstance()->getCurrentUser();
        foreach ($this->quickAccessMenuItems as $item) {
            if (empty($item[3]) || in_array($user->user_level, $item[3])) {
                $html .= '<a href="'.$item[2].'"><i class="fa '.$item[1].'"></i> '.$item[0].'</a>';
            }
        }

        return $html;
    }

    /**
     * @param ModuleBuilder $moduleBuilder
     * @return mixed|string
     */
    public function renderModule($moduleBuilder)
    {
        $str = '<div class="span9">'
            .'<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">'
            .'__tabHeaders__</ul><div class="tab-content">__tabPages__</div></div><script>__tabJs__</script>';
        $str = str_replace("__tabHeaders__", $moduleBuilder->getTabHeadersHTML(), $str);
        $str = str_replace("__tabPages__", $moduleBuilder->getTabPagesHTML(), $str);
        $str = str_replace("__tabJs__", $moduleBuilder->getModJsHTML(), $str);
        return $str;
    }

    public function getCompanyLogoUrl()
    {
        $logoFileSet = false;
        $logoFileName = CLIENT_BASE_PATH."data/logo.png";
        $logoSettings = SettingsManager::getInstance()->getSetting("Company: Logo");
        if (!empty($logoSettings)) {
            $logoFileName = FileService::getInstance()->getFileUrl($logoSettings);
            $logoFileSet = true;
        }

        if (!$logoFileSet && !file_exists($logoFileName)) {
            return  BASE_URL."images/logo.png";
        }

        return $logoFileName;
    }
}
