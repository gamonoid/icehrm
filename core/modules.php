<?php
$initializers = [];
//Reset modules if required
if (\Classes\SettingsManager::getInstance()->getSetting("System: Reset Modules and Permissions") == "1") {
    $permissionTemp = new \Permissions\Common\Model\Permission();
    $permissions = $permissionTemp->Find("1=1");
    foreach ($permissions as $permTemp) {
        $permTemp->Delete();
    }

    $moduleTemp = new \Modules\Common\Model\Module();
    $modulesTemp = $moduleTemp->Find("1=1");
    foreach ($modulesTemp as $moduleTemp) {
        $moduleTemp->Delete();
    }

    \Classes\SettingsManager::getInstance()->setSetting("System: Reset Modules and Permissions", "0");
}

$addNewPermissions = false;
if (\Classes\SettingsManager::getInstance()->getSetting("System: Add New Permissions") == "1") {
    $addNewPermissions = true;
    \Classes\SettingsManager::getInstance()->setSetting("System: Add New Permissions", "0");
}

$resetModuleNames = false;
if (\Classes\SettingsManager::getInstance()->getSetting("System: Reset Module Names") == "1") {
    $resetModuleNames = true;
    \Classes\SettingsManager::getInstance()->setSetting("System: Reset Module Names", "0");
}

\Classes\BaseService::getInstance()->initializePro();

function includeModuleManager($type, $name, $data)
{
    $moduleManagerClass = $data['manager'];
    /* @var \Classes\AbstractModuleManager $moduleManagerObj*/
    $moduleManagerObj = new $moduleManagerClass();
    $moduleManagerObj->setModuleObject($data);
    $moduleManagerObj->setModuleType($type);
    $moduleManagerObj->setModulePath(CLIENT_PATH.'/'.$type.'/'.$name);


    if (isset($data['action']) && !empty($data['action'])) {
        $moduleActionManagerClass = $data['manager'];
    } else {
        $moduleActionManagerClass = str_replace('ModulesManager', '', $moduleManagerClass);
        $moduleActionManagerClass = str_replace('AdminManager', '', $moduleActionManagerClass);
        $moduleActionManagerClass = $moduleActionManagerClass.'ActionManager';
    }
    if(class_exists($moduleActionManagerClass)) {
        $moduleManagerObj->setActionManager(new $moduleActionManagerClass());
    }
    \Classes\BaseService::getInstance()->addModuleManager($moduleManagerObj);
    return $moduleManagerObj;
}

function createPermissions($meta, $moduleId)
{
    $permData = $meta->permissions;
    if (empty($permData)) {
        return;
    }

    foreach ($permData as $key => $val) {
        if (!empty($val)) {
            foreach ($val as $permissionString => $defaultValue) {
                $permissionObj = new \Permissions\Common\Model\Permission();
                $permissionObj->Load("user_level = ? and module_id = ? and permission = ?", array($key, $moduleId, $permissionString));

                if (empty($permissionObj->id) && $permissionObj->module_id == $moduleId) {
                } else {
                    $permissionObj = new \Permissions\Common\Model\Permission();
                    $permissionObj->user_level = $key;
                    $permissionObj->module_id = $moduleId;
                    $permissionObj->permission = $permissionString;
                    $permissionObj->value = $defaultValue;
                    $permissionObj->meta = '["value", {"label":"Value","type":"select","source":[["Yes","Yes"],["No","No"]]}]';
                    $permissionObj->Save();
                }
            }
        }
    }
}

$dbModule = new \Modules\Common\Model\Module();
$adminDbModules = $dbModule->Find("mod_group = ?", array("admin"));
$userDbModules = $dbModule->Find("mod_group = ?", array("user"));

$adminDBModuleList = array();
foreach ($adminDbModules as $dbm) {
    $adminDBModuleList[$dbm->name] = $dbm;
    \Classes\ModuleAccessService::getInstance()->setModule($dbm->name, 'admin', $dbm);
}

$userDBModuleList = array();
foreach ($userDbModules as $dbm) {
    $userDBModuleList[$dbm->name] = $dbm;
    \Classes\ModuleAccessService::getInstance()->setModule($dbm->name, 'user', $dbm);
}

$adminModulesTemp = array();
$ams = scandir(CLIENT_PATH.'/admin/');
$currentLocation = 0;
foreach ($ams as $am) {
    if (is_dir(CLIENT_PATH.'/admin/'.$am) && $am != '.' && $am != '..') {
        if (!\Classes\BaseService::getInstance()->isModuleEnabled('admin', $am)) {
            continue;
        }
        $meta = json_decode(file_get_contents(CLIENT_PATH.'/admin/'.$am.'/meta.json'));

        $arr = array();
        $arr['name'] = $am;
        $arr['label'] = $meta->label;
        $arr['icon'] = $meta->icon;
        $arr['menu'] = $meta->menu;
        $arr['order'] = $meta->order;
        $arr['status'] = 'Enabled';
        $arr['user_levels'] = $meta->user_levels;
        $arr['user_roles'] = isset($meta->user_roles)?$meta->user_roles:"";
        $arr['model_namespace'] = $meta->model_namespace;
        $arr['manager'] = $meta->manager;

        //Check in admin dbmodules
        if (isset($adminDBModuleList[$arr['name']])) {
            $dbModule = $adminDBModuleList[$arr['name']];

            if ($addNewPermissions && isset($meta->permissions)) {
                createPermissions($meta, $dbModule->id);
            }

            if ($resetModuleNames) {
                $dbModule->label = $arr['label'];
                $dbModule->menu = $arr['menu'];
                $dbModule->icon = $arr['icon'];
                $dbModule->mod_order = $arr['order'];
                $dbModule->Save();
            }

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
            $dbModule->mod_group = "admin";
            $dbModule->mod_order = $arr['order'];
            $dbModule->status = "Enabled";
            $dbModule->version = isset($meta->version)?$meta->version:"";
            $dbModule->update_path = "admin>".$am;
            $dbModule->user_levels = isset($meta->user_levels)?json_encode($meta->user_levels):"";
            $dbModule->user_roles = isset($meta->user_roles)?json_encode($meta->user_roles):"";
            $dbModule->Save();

            if (isset($meta->permissions)) {
                createPermissions($meta, $dbModule->id);
            }
        }

        /* @var \Classes\AbstractModuleManager */
        $manager = includeModuleManager('admin', $am, $arr);
        if ($dbModule->status == 'Disabled') {
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

        /* @var \Classes\AbstractInitialize $initializer */
        $initializer = $manager->getInitializer();
        if ($initializer !== null) {
            $initializer->setBaseService($baseService);
            $initializers[] = $initializer;
        }
    }
}

$userModulesTemp = array();
$ams = scandir(CLIENT_PATH.'/modules/');
foreach ($ams as $am) {
    try {
        if (is_dir(CLIENT_PATH . '/modules/' . $am) && $am != '.' && $am != '..') {
            if (!\Classes\BaseService::getInstance()->isModuleEnabled('modules', $am)) {
                continue;
            }
            $meta = json_decode(file_get_contents(CLIENT_PATH . '/modules/' . $am . '/meta.json'));

            $arr = array();
            $arr['name'] = $am;
            $arr['label'] = $meta->label;
            $arr['icon'] = $meta->icon;
            $arr['menu'] = $meta->menu;
            $arr['order'] = $meta->order;
            $arr['status'] = 'Enabled';
            $arr['user_levels'] = $meta->user_levels;
            $arr['user_roles'] = isset($meta->user_roles) ? $meta->user_roles : "";
            $arr['model_namespace'] = $meta->model_namespace;
            $arr['manager'] = $meta->manager;

            //Check in admin dbmodules
            if (isset($userDBModuleList[$arr['name']])) {
                $dbModule = $userDBModuleList[$arr['name']];

                if ($addNewPermissions && isset($meta->permissions)) {
                    createPermissions($meta, $dbModule->id);
                }

                if ($resetModuleNames) {
                    $dbModule->label = $arr['label'];
                    $dbModule->menu = $arr['menu'];
                    $dbModule->icon = $arr['icon'];
                    $dbModule->mod_order = $arr['order'];
                    $dbModule->Save();
                }

                $arr['name'] = $dbModule->name;
                $arr['label'] = $dbModule->label;
                $arr['icon'] = $dbModule->icon;
                $arr['menu'] = $dbModule->menu;
                $arr['order'] = $dbModule->mod_order;
                $arr['status'] = $dbModule->status;
                $arr['user_levels'] = json_decode($dbModule->user_levels);
                $arr['user_roles'] = empty($dbModule->user_roles)
                    ? [] :json_decode($dbModule->user_roles);
                $arr['user_roles_blacklist'] = empty($dbModule->user_roles_blacklist)
                    ? [] : json_decode($dbModule->user_roles_blacklist);
            } else {
                $dbModule = new \Modules\Common\Model\Module();
                $dbModule->menu = $arr['menu'];
                $dbModule->name = $arr['name'];
                $dbModule->label = $arr['label'];
                $dbModule->icon = $arr['icon'];
                $dbModule->mod_group = "user";
                $dbModule->mod_order = $arr['order'];
                $dbModule->status = "Enabled";
                $dbModule->version = isset($meta->version) ? $meta->version : "";
                $dbModule->update_path = "modules>" . $am;
                $dbModule->user_levels = isset($meta->user_levels) ? json_encode($meta->user_levels) : "";
                $dbModule->user_roles = isset($meta->user_roles) ? json_encode($meta->user_roles) : "";
                $dbModule->Save();

                if (isset($meta->permissions)) {
                    createPermissions($meta, $dbModule->id);
                }
            }

            /* @var \Classes\AbstractModuleManager */
            $manager = includeModuleManager('modules', $am, $arr);

            if ($dbModule->status == 'Disabled') {
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

            $initializer = $manager->getInitializer();
            if ($initializer !== null) {
                $initializer->setBaseService($baseService);
                $initializers[] = $initializer;
            }
        }
    } catch (\Exception $e) {
        $k = $e;
    }
}

$extensionManager = new \Classes\ExtensionManager();
$extensionData = $extensionManager->setupExtensions();
$extensionIcons = $extensionData[0];
$extensionTemp = $extensionData[1];
$extensionMenus = array_keys($extensionIcons);

foreach ($adminModulesTemp as $k => $v) {
    ksort($adminModulesTemp[$k]);
}

foreach ($userModulesTemp as $k => $v) {
    ksort($userModulesTemp[$k]);
}

foreach ($extensionTemp as $k => $v) {
    ksort($extensionTemp[$k]);
}

$adminIcons = json_decode(file_get_contents(CLIENT_PATH.'/admin/meta.json'), true);
$adminMenus = array_keys($adminIcons);

$adminModules = array();
$added = array();
foreach ($adminMenus as $menu) {
    if (isset($adminModulesTemp[$menu])) {
        $arr = array("name"=>$menu,"menu"=>$adminModulesTemp[$menu]);
        $adminModules[] = $arr;
        $added[] = $menu;
    }
}

foreach ($adminModulesTemp as $k => $v) {
    if (!in_array($k, $added)) {
        $arr = array("name"=>$k,"menu"=>$adminModulesTemp[$k]);
        $adminModules[] = $arr;
    }
}

$userIcons = json_decode(file_get_contents(CLIENT_PATH.'/modules/meta.json'), true);
$userMenus = array_keys($userIcons);

$userModules = array();
$added = array();
foreach ($userMenus as $menu) {
    if (isset($userModulesTemp[$menu])) {
        $arr = array("name"=>$menu,"menu"=>$userModulesTemp[$menu]);
        $userModules[] = $arr;
        $added[] = $menu;
    }
}

foreach ($userModulesTemp as $k => $v) {
    if (!in_array($k, $added)) {
        $arr = array("name"=>$k,"menu"=>$userModulesTemp[$k]);
        $userModules[] = $arr;
    }
}

$extensions = array();
foreach ($extensionMenus as $menu) {
    if (isset($extensionTemp[$menu])) {
        $arr = array("name"=>$menu,"menu"=>$extensionTemp[$menu]);
        $extensions[] = $arr;
        $added[] = $menu;
    }
}

foreach ($extensionTemp as $k => $v) {
    if (!in_array($k, $added)) {
        $arr = array("name"=>$k,"menu"=>$extensionTemp[$k]);
        $extensions[] = $arr;
    }
}

// Merge icons
$mainIcons = array_merge($adminIcons, $userIcons, $extensionIcons);

//Remove modules having no permissions
if (!empty($user)) {
    if (!empty($user->user_roles)) {
        try {
            $userRoles = json_decode($user->user_roles, true);
        } catch (Exception $e) {
            $userRoles = [];
        }
    } else {
        $userRoles = [];
    }

    foreach ($adminModules as $fk => $menu) {
        foreach ($menu['menu'] as $key => $item) {
            // If the user's once of the user roles are blacklisted for the module
            if (empty($item['user_roles_blacklist'])) {
                $item['user_roles_blacklist'] = [];
            }
            $commonRoles = array_intersect($item['user_roles_blacklist'], $userRoles);
            if (!empty($commonRoles)) {
                unset($adminModules[$fk]['menu'][$key]);
            }

            if (!in_array($user->user_level, $item['user_levels'])) {
                if (!empty($userRoles)) {
                    if (empty($item['user_roles'])) {
                        $item['user_roles'] = [];
                    }
                    $commonRoles = array_intersect($item['user_roles'], $userRoles);
                    if (empty($commonRoles)) {
                        unset($adminModules[$fk]['menu'][$key]);
                    }
                } else {
                    unset($adminModules[$fk]['menu'][$key]);
                }
            }
        }
    }

    foreach ($userModules as $fk => $menu) {
        foreach ($menu['menu'] as $key => $item) {
            // If the user's once of the user roles are blacklisted for the module
            if (empty($item['user_roles_blacklist'])) {
                $item['user_roles_blacklist'] = [];
            }
            $commonRoles = array_intersect($item['user_roles_blacklist'], $userRoles);
            if (!empty($commonRoles)) {
                unset($userModules[$fk]['menu'][$key]);
            }
            if (!in_array($user->user_level, $item['user_levels'])) {
                if (!empty($userRoles)) {
                    if (empty($item['user_roles'])) {
                        $item['user_roles'] = [];
                    }
                    $commonRoles = array_intersect($item['user_roles'], $userRoles);
                    if (empty($commonRoles)) {
                        unset($userModules[$fk]['menu'][$key]);
                    }
                } else {
                    unset($userModules[$fk]['menu'][$key]);
                }
            }
        }
    }

    foreach ($extensions as $fk => $menu) {
        foreach ($menu['menu'] as $key => $item) {
            // If the user's once of the user roles are blacklisted for the module
            if (empty($item['user_roles_blacklist'])) {
                $item['user_roles_blacklist'] = [];
            }
            $commonRoles = array_intersect($item['user_roles_blacklist'], $userRoles);
            if (!empty($commonRoles)) {
                unset($extensions[$fk]['menu'][$key]);
            }
            if (!in_array($user->user_level, $item['user_levels'])) {
                if (!empty($userRoles)) {
                    if (empty($item['user_roles'])) {
                        $item['user_roles'] = [];
                    }
                    $commonRoles = array_intersect($item['user_roles'], $userRoles);
                    if (empty($commonRoles)) {
                        unset($extensions[$fk]['menu'][$key]);
                    }
                } else {
                    unset($extensions[$fk]['menu'][$key]);
                }
            }
        }
    }
}

// Run initializers
foreach ($initializers as $initializer) {
    $initializer->init();
}

