<?php
use Classes\BaseService;
use Classes\SettingsManager;

$moduleName = 'overtime';
$moduleGroup = 'modules';
$moduleMainName = "EmployeeOvertime"; // for creating module js lib
$subModuleMainName = "SubordinateEmployeeOvertime";
$moduleItemName = "Overtime Request"; // For permissions

$itemName = $moduleItemName; // for status change popup
$itemNameLower = strtolower($moduleMainName);  // for status change popup
$approveModName = $moduleMainName.'Approval';

define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
$additionalJs = array();
$approvalsNeeded = SettingsManager::getInstance()->getSetting('Overtime: Enable Multi Level Approvals') == '1';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tab<?=$moduleMainName?>" href="#tabPage<?=$moduleMainName?>"><?=t('Overtime Requests')?></a></li>
        <li><a id="tab<?=$subModuleMainName?>" href="#tabPage<?=$subModuleMainName?>"><?=t('Overtime Requests (Direct Reports)')?></a></li>
        <?php if ($approvalsNeeded) {?>
            <li><a id="tab<?=$approveModName?>" href="#tabPage<?=$approveModName?>"><?=t('Overtime Request Approval')?></a></li>
        <?php } ?>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPage<?=$moduleMainName?>">
            <div class="tab-pane active" id="tabPage<?=$moduleMainName?>">
                <div id="<?=$moduleMainName?>Table" class="reviewBlock" data-content="List"></div>
                <div id="<?=$moduleMainName?>Form"></div>
                <div id="<?=$moduleMainName?>FilterForm"></div>
            </div>
        </div>
        <div class="tab-pane" id="tabPage<?=$subModuleMainName?>">
            <div id="<?=$subModuleMainName?>Table" class="reviewBlock" data-content="List"></div>
            <div id="<?=$subModuleMainName?>Form"></div>
            <div id="<?=$subModuleMainName?>FilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPage<?=$approveModName?>">
            <div id="<?=$approveModName?>Table" class="reviewBlock" data-content="List"></div>
            <div id="<?=$approveModName?>Form"></div>
            <div id="<?=$approveModName?>FilterForm"></div>
        </div>
    </div>

</div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'customFields' => BaseService::getInstance()->getCustomFields("EmployeeOvertime"),
    'permissions' => [ "get","element","save","delete" ],
    'moduleMainName' => $moduleMainName,
    'approveModName' => $approveModName,
    'subModuleMainName' => $subModuleMainName,
];
?>
<script>
  initUserOvertime(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>

