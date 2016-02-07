<?php
UIManager::getInstance()->setCurrentUser($user);
UIManager::getInstance()->setProfiles($profileCurrent, $profileSwitched);
UIManager::getInstance()->setHomeLink($homeLink);

$moduleManagers = BaseService::getInstance()->getModuleManagers();
foreach($moduleManagers as $moduleManagerObj){
    $allowed = BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

    if($allowed){
        $moduleManagerObj->initQuickAccessMenu();
    }
}
