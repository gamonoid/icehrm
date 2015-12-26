<?php
UIManager::getInstance()->setCurrentUser($user);
UIManager::getInstance()->setProfiles($profileCurrent, $profileSwitched);
UIManager::getInstance()->setHomeLink($homeLink);

UIManager::getInstance()->addQuickAccessMenuItem("View Employees","fa-users",CLIENT_BASE_URL."?g=admin&n=employees&m=admin_Employees",array("Admin","Manager"));
UIManager::getInstance()->addQuickAccessMenuItem("Add a New Employee","fa-edit",CLIENT_BASE_URL."?g=admin&n=employees&m=admin_Employees&action=new",array("Admin"));
UIManager::getInstance()->addQuickAccessMenuItem("Manage Client/Projects","fa-list-alt",CLIENT_BASE_URL."?g=admin&n=projects&m=admin_Admin",array("Admin","Manager"));
UIManager::getInstance()->addQuickAccessMenuItem("Clocked In Employees","fa-clock-o",CLIENT_BASE_URL."?g=admin&n=attendance&m=admin_Employees#tabAttendanceStatus",array("Admin","Manager"));
UIManager::getInstance()->addQuickAccessMenuItem("Additional Modules","fa-shopping-cart","http://icehrm.com/modules.php",array("Admin"));