<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'dashboard';
$moduleGroup = 'admin';
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
    <?php if($user->user_level == "Admin") { ?>
    $(document).ready(function () {
      $('.span9 .row').prepend(window.atob('PGRpdiBjbGFzcz0iY2FsbG91dCBjYWxsb3V0LXdhcm5pbmcgbGVhZCIgc3R5bGU9ImZvbnQtc2l6ZTogMTRweDttYXJnaW4tdG9wOiA5MHB4OyI+CiAgICAgICAgICAgIDxoND5Zb3UgYXJlIGN1cnJlbnRseSB1c2luZyBJY2VIcm0gT3BlbnNvdXJjZSBWZXJzaW9uPC9oND4KICAgICAgICAgICAgPHAgc3R5bGU9ImZvbnQtd2VpZ2h0OiBib2xkOyI+CiAgICAgICAgICAgICAgICBXZSBoYXZlIGEgbG90IG1vcmUgZmVhdHVyZXMgdG8gb2ZmZXIuIEluY2x1ZGluZyBvdXIgaGlnaGx5IGN1c3RvbWl6YWJsZSBsZWF2ZSBtYW5hZ2VtZW50LCByZWNydWl0bWVudCBtb2R1bGVzIGFuZCBtYW55IG1vcmUgYWR2YW5jZWQgZmVhdHVyZXMgaW4gSWNlSHJtUHJvJiMxNzQ7CiAgICAgICAgICAgICAgICA8YnIvPgogICAgICAgICAgICAgICAgPGJyLz4KICAgICAgICAgICAgICAgIDxhIHRhcmdldD0iX2JsYW5rIiBocmVmPSJodHRwczovL2ljZWhybS5jb20vcHVyY2hhc2UtaWNlaHJtcHJvIiBjbGFzcz0iYnRuIGJ0bi1zdWNjZXNzIGJ0bS14cyI+PGkgY2xhc3M9ImZhIGZhLWNoZWNrb3V0Ij48L2k+IE1vcmUgYWJvdXQgSWNlSHJtUHJvJiMxNzQ7PC9hPgogICAgICAgICAgICA8L3A+CiAgICAgICAgPC9kaXY+'));
    });
    <?php } ?>
</script>
<?php include APP_BASE_PATH.'footer.php';?>
