<?php
use Classes\StatsHelper;
use Connection\Common\ConnectionService;

$moduleName = 'connection';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$isIceHrmPro = false;
if (class_exists('\\Classes\\ProVersion')) {
    $data = \Classes\ProVersion::$data;
    $isIceHrmPro = true;
    $data = json_decode($data, true);
}

$employeeCount = StatsHelper::getActiveEmployeeCount();
$userCount = StatsHelper::getUserCount();
$connectionService = new ConnectionService();
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabConnection" href="#tabConnection"><?=t('Connection')?></a></li>
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
                    'isIceHrmPro' => $isIceHrmPro,
                    'count' => $employeeCount,
                    'allowed' => $isIceHrmPro ? intval($data['employees']) : 'N/A',
                    'validUntil' => $data['licenseActivated'],
                    'licenseId' => $data['key'],
            ],
            'systemData' => [
                    'data' => $connectionService->getSystemReport(),
                    'issues' => $connectionService->getSystemErrors(),
            ],
    ]
];
?>
<script>
  initAdminConnection(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>

