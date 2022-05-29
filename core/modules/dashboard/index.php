<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\LanguageManager;

$moduleName = 'dashboard';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

        $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();
        $dashBoardList = array();
        foreach($moduleManagers as $moduleManagerObj){

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

        $dashboardList1  =[];
        $dashboardList2  =[];
        foreach($dashBoardList as $k=>$v){
            if (count($dashboardList1) === 4 ) {
                $dashboardList2[] = $v;
            } else {
                $dashboardList1[] = $v;
            }
        }
?><div class="span9">
    <div class="row">
        <?php
        foreach($dashboardList1 as $v){
            echo LanguageManager::translateTnrText($v);
        }
        ?>
    </div>
    <div class="row">
        <div class="col-lg-4 col-xs-12">
            <div id="EmployeeOnlineOfflineChartLoader" style="width:100%;"></div>
            <div id="EmployeeOnlineOfflineChart" style="display:none;box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);border: none;margin-bottom: 20px;"></div>
        </div>
        <div class="col-lg-4 col-xs-12">
            <div id="EmployeeStatusLoader" style="width:100%;"></div>
            <div id="EmployeeStatusWrap" style="display: none;box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);border: none;margin-bottom: 20px; padding:25px;">
                <h4>Your Day</h4>
                <div id="EmployeeStatus" style="margin-left: 10px; margin-top: 30px;"></div>
            </div>
        </div>
        <div class="col-lg-4 col-xs-12">
            <div id="TaskListLoader" style="width:100%;"></div>
            <div id="TaskListWrap" style="display: none;box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);border: none;margin-bottom: 20px; padding:25px;">
                <h4><?=t('My Todo List')?></h4>
                <div id="TaskList" style="margin-left: 10px; margin-top: 30px;"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php
        foreach($dashboardList2 as $v){
            echo LanguageManager::translateTnrText($v);
        }
        ?>
    </div>

</div>

<script>
var modJsList = [];

modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

var modJs = modJsList['tabDashboard'];


</script>
<?php include APP_BASE_PATH.'footer.php';?>
