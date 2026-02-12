<?php

use Classes\BaseService;
use Classes\StatsHelper;
use Connection\Common\ConnectionService;

$moduleName = 'connection';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';


$employeeCount = StatsHelper::getActiveEmployeeCount();
$userCount = StatsHelper::getUserCount();
$connectionService = new ConnectionService();
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabConnection" href="#tabConnection"><?=t('System Status')?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabConnection">
            <div class="reviewBlock" data-content="List">
                <div id="connectionData"></div>
            </div>
        </div>
    </div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'components' => [
            'employeeCount' => [
                    'count' => $employeeCount,
            ],
            'systemData' => [
                    'data' => $connectionService->getSystemReport(),
                    'issues' => $connectionService->getSystemErrors(),
                    'logFileRows' => $connectionService->getLastLogFileRows(1000),
            ],
    ]
];
?>
<script>
  initAdminConnection(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>

