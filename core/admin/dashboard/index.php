<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'dashboard';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
    <div class="row">

        <?php
        $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();
        $dashBoardList = array();
        foreach($moduleManagers as $moduleManagerObj){

            //Check if this is not an admin module
            if($moduleManagerObj->getModuleType() != 'admin'){
                continue;
            }

            $allowed = \Classes\BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

            if(!$allowed){
                continue;
            }

            $item = $moduleManagerObj->getDashboardItem();
            if(!empty($item)) {
                $index = $moduleManagerObj->getDashboardItemIndex();
                $dashBoardList[$index] = $item;
            }
        }

        ksort($dashBoardList);

        foreach($dashBoardList as $k=>$v){
            echo \Classes\LanguageManager::translateTnrText($v);
        }
        ?>
    </div>


</div>
<script>
    var modJsList = new Array();

    modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

    var modJs = modJsList['tabDashboard'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
