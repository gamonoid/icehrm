<?php
\Classes\UIManager::getInstance()->setCurrentUser($user);
\Classes\UIManager::getInstance()->setProfiles($profileCurrent, $profileSwitched);
\Classes\UIManager::getInstance()->setHomeLink($homeLink);

$moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();
foreach($moduleManagers as $moduleManagerObj){
    $allowed = \Classes\BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

    if($allowed){
        $moduleManagerObj->initQuickAccessMenu();
    }
}
