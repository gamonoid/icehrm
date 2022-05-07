<?php

namespace Classes;

use Utils\LogManager;

class ExtensionManager
{
    const GROUP = 'extension';
    protected function processExtensionInDB()
    {
        $dbModule = new \Modules\Common\Model\Module();
        $extensions = $dbModule->Find("mod_group = ?", array(self::GROUP));

        $extensionsInDB = [];
        foreach ($extensions as $dbm) {
            $extensionsInDB[$dbm->name] = $dbm;
            ModuleAccessService::getInstance()->setModule($dbm->name, self::GROUP, $dbm);
        }

        return $extensionsInDB;
    }

    public function getExtensionsPath()
    {
        return APP_BASE_PATH.'../extensions/';
    }

    public function getExtensionMetaData($extensionName)
    {
        return json_decode(file_get_contents($this->getExtensionsPath().$extensionName.'/meta.json'));
    }

    public function setupExtensions()
    {
        $menu = [];
        $extensions = [];
        $extensionDirs = scandir($this->getExtensionsPath());
        $currentLocation = 0;

        $extensionsInDB = $this->processExtensionInDB();

        $needToInstall = false;

        foreach ($extensionDirs as $extensionDir) {
            if (is_dir($this->getExtensionsPath().$extensionDir) && $extensionDir != '.' && $extensionDir != '..') {
                $meta = $this->getExtensionMetaData($extensionDir);

                $arr = [];
                $arr['name'] = $extensionDir;
                $arr['label'] = $meta->label;
                $arr['icon'] = $meta->icon;
                $arr['menu'] = $meta->menu[0];
                $arr['order'] = 0;
                $arr['status'] = 'Enabled';
                $arr['user_levels'] = $meta->user_levels;
                $arr['user_roles'] = isset($meta->user_roles)?$meta->user_roles:"";
                $arr['model_namespace'] = $meta->model_namespace;
                $arr['manager'] = $meta->manager;
                $arr['controller'] = $meta->controller;

                // Add menu
                $menu[$meta->menu[0]] = $meta->menu[1];

                //Check in admin dbmodules
                if (isset($extensionsInDB[$arr['name']])) {
                    $dbModule = $extensionsInDB[$arr['name']];

                    $arr['name'] = $dbModule->name;
                    $arr['label'] = $dbModule->label;
                    $arr['icon'] = $dbModule->icon;
                    $arr['menu'] = $dbModule->menu;
                    $arr['status'] = $dbModule->status;
                    $arr['user_levels'] = json_decode($dbModule->user_levels);
                    $arr['user_roles'] = empty($dbModule->user_roles)
                        ? [] : json_decode($dbModule->user_roles);
                    $arr['user_roles_blacklist'] = empty($dbModule->user_roles_blacklist)
                        ? [] : json_decode($dbModule->user_roles_blacklist);
                } else {
                    $dbModule = new \Modules\Common\Model\Module();
                    $dbModule->menu = $arr['menu'];
                    $dbModule->name = $arr['name'];
                    $dbModule->label = $arr['label'];
                    $dbModule->icon = $arr['icon'];
                    $dbModule->mod_group = self::GROUP;
                    $dbModule->mod_order = $arr['order'];
                    $dbModule->status = "Enabled";
                    $dbModule->version = isset($meta->version)?$meta->version:"";
                    $dbModule->update_path = self::GROUP.">".$extensionDir;
                    $dbModule->user_levels = isset($meta->user_levels)?json_encode($meta->user_levels):"";
                    $dbModule->user_roles = isset($meta->user_roles)?json_encode($meta->user_roles):"";

                    $ok = $dbModule->Save();
                    if (!$ok) {
                        LogManager::getInstance()->error('Error saving module: '.$dbModule->name);
                    }
                    $needToInstall = $ok;
                }

                /* @var \Classes\AbstractModuleManager */
                $manager = $this->includeModuleManager($extensionDir, $arr);

                if (!$manager || $dbModule->status == 'Disabled') {
                    continue;
                }

                if ($needToInstall) {
                    $manager->install();
                }

                $menuName = $arr['menu'];
                if (!isset($extensions[$menuName])) {
                    $extensions[$menuName] = array();
                }

                if (!$meta->headless) {
                    if ($arr['order'] == '0' || $arr['order'] == '') {
                        $extensions[$menuName]["Z".$currentLocation] = $arr;
                        $currentLocation++;
                    } else {
                        $extensions[$arr['menu']]["A".$arr['order']] = $arr;
                    }
                }

                $manager->initialize();
                $initializer = $manager->getInitializer();
                if ($initializer !== null) {
                    $initializer->setBaseService(BaseService::getInstance());
                    $initializer->init();
                }
            }
        }

        return [$menu, $extensions];
    }

    public function includeModuleManager($name, $data)
    {
        if (!file_exists($this->getExtensionsPath().$name.'/'.$name.'.php')) {
            return false;
        }
        include($this->getExtensionsPath().$name.'/'.$name.'.php');
        $moduleManagerClass = $data['manager'];
        /* @var \Classes\AbstractModuleManager $moduleManagerObj*/
        $moduleManagerObj = new $moduleManagerClass();
        $moduleManagerObj->setModuleObject($data);
        $moduleManagerObj->setModuleType(self::GROUP);
        $moduleManagerObj->setModulePath(CLIENT_PATH.'/'.self::GROUP.'/'.$name);

        $controllerClass = $data['controller'];
        if (class_exists($controllerClass)) {
            $moduleManagerObj->setActionManager(new $controllerClass());
        }

        \Classes\BaseService::getInstance()->addModuleManager($moduleManagerObj);
        return $moduleManagerObj;
    }
}
