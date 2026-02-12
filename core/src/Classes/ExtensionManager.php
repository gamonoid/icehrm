<?php

namespace Classes;

use Utils\LogManager;

class ExtensionManager
{
    const GROUP = 'extension';

    protected function processExtensionInDB()
    {
        $dbModule = new \Modules\Common\Model\Module();
        $extensions = $dbModule->Find("1 = 1");

        $extensionsInDB = [];
        foreach ($extensions as $dbm) {
            if (substr($dbm->update_path, 0, 10) !== 'extension>') {
                continue;
            }
            $extensionsInDB[$dbm->name][$dbm->mod_group] = $dbm;
            ModuleAccessService::getInstance()->setModule($dbm->name, self::GROUP, $dbm);
        }

        return $extensionsInDB;
    }

    public function getExtensionsPath()
    {
        return APP_BASE_PATH.'../extensions/';
    }

    /**
     * Get the actual path to an extension, considering grouped extensions
     */
    public function getExtensionActualPath(string $extensionName): string
    {
        return ExtensionGroupManager::getInstance()->getExtensionPath($extensionName);
    }

    public function getExtensionMetaData($extensionName)
    {
        // Handle both "extension/type" format and just "extension" format
        $parts = explode('/', $extensionName);
        $extName = $parts[0];

        // Get the actual path considering groups
        $actualPath = $this->getExtensionActualPath($extName);

        // If there's a type (admin/user), append it
        if (count($parts) > 1) {
            $metaPath = $actualPath . $parts[1] . '/meta.json';
        } else {
            $metaPath = $actualPath . 'meta.json';
        }

        if (!file_exists($metaPath)) {
            return null;
        }

        return json_decode(file_get_contents($metaPath));
    }

    public function setupExtensions($adminModulesTemp, $userModulesTemp)
    {
        $menu = [];
        $extensions = [];
        $currentLocation = 0;

        $needToInstall = false;
        $extensionsInDB = $this->processExtensionInDB();

        // Use ExtensionGroupManager to get all extension names (both grouped and ungrouped)
        $extensionNames = ExtensionGroupManager::getInstance()->getAllExtensionNames();

        foreach ($extensionNames as $extensionDir) {
            $extensionPath = $this->getExtensionActualPath($extensionDir);
            if (is_dir($extensionPath)) {
                $extensionAdminDir = $extensionDir.'/admin';
                if (is_dir($extensionPath . 'admin')) {
                    $meta = $this->getExtensionMetaData($extensionAdminDir);
                    list($arr, $menu, $dbModule, $needToInstall) = $this->getExtensionData($extensionAdminDir, $meta, $menu, $extensionsInDB, 'admin', $extensionDir);
                    /* @var \Classes\AbstractModuleManager */
                    $manager = $this->includeModuleManager($extensionDir, $arr, 'admin');
                    if (!$manager || $dbModule->status == 'Disabled') {
                        continue;
                    }
                    if ($needToInstall) {
                        $manager->install();
                    }
                    $extensions = $this->initManager($arr, $extensions, $meta, $currentLocation, $manager);

					if (!$meta->show_in_menu) {
						continue;
					}

                    if (!isset($adminModulesTemp[$arr['menu']])) {
                        $adminModulesTemp[$arr['menu']] = array();
                    }

                    if ($arr['order'] == '0' || $arr['order'] == '') {
                        $adminModulesTemp[$arr['menu']]["Z".$currentLocation] = $arr;
                        $currentLocation++;
                    } else {
                        $adminModulesTemp[$arr['menu']]["A".$arr['order']] = $arr;
                    }
                }

                $extensionUserDir = $extensionDir.'/user';
                if (is_dir($extensionPath . 'user')) {
                    $meta = $this->getExtensionMetaData($extensionUserDir);
                    list($arr, $menu, $dbModule, $needToInstall) = $this->getExtensionData($extensionUserDir, $meta, $menu, $extensionsInDB, 'user', $extensionDir);
                    /* @var \Classes\AbstractModuleManager */
                    $manager = $this->includeModuleManager($extensionDir, $arr, 'user');
                    if (!$manager || $dbModule->status == 'Disabled') {
                        continue;
                    }
                    if ($needToInstall) {
                        $manager->install();
                    }
                    $extensions = $this->initManager($arr, $extensions, $meta, $currentLocation, $manager);

					if (!$meta->show_in_menu || $meta->headless) {
						continue;
					}

                    if (!isset($userModulesTemp[$arr['menu']])) {
                        $userModulesTemp[$arr['menu']] = array();
                    }

                    if ($arr['order'] == '0' || $arr['order'] == '') {
                        $userModulesTemp[$arr['menu']]["Z" . $currentLocation] = $arr;
                        $currentLocation++;
                    } else {
                        $userModulesTemp[$arr['menu']]["A" . $arr['order']] = $arr;
                    }
                }
            }
        }

        return [$adminModulesTemp, $userModulesTemp];
    }

    public function includeModuleManager($name, $data, $type)
    {
        // Get actual path considering grouped extensions
        $actualPath = $this->getExtensionActualPath($name);
        $managerFile = $actualPath . $type . '/' . $name . '.php';

        if (!file_exists($managerFile)) {
            return false;
        }
        include $managerFile;
        $moduleManagerClass = $data['manager'];
        /* @var \Classes\AbstractModuleManager $moduleManagerObj*/
        $moduleManagerObj = new $moduleManagerClass();
        $moduleManagerObj->setModuleObject($data);
        $moduleManagerObj->setModuleType($type);
        $moduleManagerObj->setModulePath($actualPath . $type);

        $controllerClass = $data['controller'];
        if (class_exists($controllerClass)) {
            $moduleManagerObj->setActionManager(new $controllerClass());
        }

        \Classes\BaseService::getInstance()->addModuleManager($moduleManagerObj);
        return $moduleManagerObj;
    }

    /**
     * @param $extensionDir
     * @param $meta
     * @param array        $menu
     * @param array        $extensionsInDB
     * @return array
     */
    protected function getExtensionData($extensionDir, $meta, array $menu, array $extensionsInDB, $modGroup, $origDir)
    {
        $arr = [];
        $arr['name'] = $origDir;
        $arr['label'] = $meta->label;
        $arr['icon'] = $meta->icon;
        $arr['menu'] = $meta->menu[0];
        $arr['order'] = $meta->order;
        $arr['status'] = 'Enabled';
        $arr['user_levels'] = $meta->user_levels;
        $arr['user_roles'] = isset($meta->user_roles) ? $meta->user_roles : "";
        $arr['model_namespace'] = $meta->model_namespace;
        $arr['manager'] = $meta->manager;
        $arr['controller'] = $meta->controller;

        // Add menu
        $menu[$meta->menu[0]] = $meta->menu[1];

        //Check in admin dbmodules
        if (isset($extensionsInDB[$arr['name']][$modGroup])) {
            $dbModule = $extensionsInDB[$arr['name']][$modGroup];

            $arr['name'] = $dbModule->name;
//            $arr['label'] = $dbModule->label;
//            $arr['icon'] = $dbModule->icon;
//            $arr['menu'] = $dbModule->menu;
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
            $dbModule->mod_group = $modGroup;
            $dbModule->mod_order = $arr['order'];
            $dbModule->status = "Enabled";
            $dbModule->version = isset($meta->version) ? $meta->version : "";
            $dbModule->update_path = self::GROUP . ">" . str_replace('/', '|', $extensionDir);
            $dbModule->user_levels = isset($meta->user_levels) ? json_encode($meta->user_levels) : "";
            $dbModule->user_roles = isset($meta->user_roles) ? json_encode($meta->user_roles) : "";

            $ok = $dbModule->Save();
            if (!$ok) {
                LogManager::getInstance()->error('Error saving module: ' . $dbModule->name);
            }
            $needToInstall = $ok;
        }

        $arr['link_name'] = $arr['name'].'|'.$modGroup;
        $arr['link_group'] = self::GROUP;

        return array($arr, $menu, $dbModule, false);
    }

    /**
     * @param $arr
     * @param array                 $extensions
     * @param $meta
     * @param int                   $currentLocation
     * @param AbstractModuleManager $manager
     * @return array
     */
    protected function initManager($arr, array $extensions, $meta, int $currentLocation, AbstractModuleManager $manager)
    {
        $menuName = $arr['menu'];
        if (!isset($extensions[$menuName])) {
            $extensions[$menuName] = array();
        }

        if (!$meta->headless) {
            if ($arr['order'] == '0' || $arr['order'] == '') {
                $extensions[$menuName]["Z" . $currentLocation] = $arr;
                $currentLocation++;
            } else {
                $extensions[$arr['menu']]["A" . $arr['order']] = $arr;
            }
        }

        $manager->initialize();
        $initializer = $manager->getInitializer();
        if ($initializer !== null) {
            $initializer->setBaseService(BaseService::getInstance());
            $initializer->init();
        }
        return $extensions;
    }
}
